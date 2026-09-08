<?php

namespace Tests\Feature;

use App\Models\ClassYear;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AccessControlService;
use App\Services\ProgressService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AccessControlServiceTest extends TestCase
{
    use RefreshDatabase;

    private function seedMinimalData(): array
    {
        Role::firstOrCreate(['name' => Role::PARENT]);

        $classYear = ClassYear::create(['name' => 'Year 3', 'slug' => 'year-3']);
        $maths = Subject::create(['class_year_id' => $classYear->id, 'name' => 'Mathematics', 'slug' => 'year-3-maths']);
        $science = Subject::create(['class_year_id' => $classYear->id, 'name' => 'Science', 'slug' => 'year-3-science']);

        $courseMaths = Course::create(['subject_id' => $maths->id, 'title' => 'Maths Core', 'slug' => 'maths-core', 'is_published' => true]);
        $module = Module::create(['course_id' => $courseMaths->id, 'title' => 'Fractions', 'sort_order' => 1]);
        $lesson = Lesson::create(['module_id' => $module->id, 'title' => 'Intro', 'sort_order' => 1, 'video_provider' => 'mux', 'video_id' => 'v1', 'duration_seconds' => 300, 'is_published' => true, 'is_free' => false]);
        $freeLesson = Lesson::create(['module_id' => $module->id, 'title' => 'Preview', 'sort_order' => 2, 'video_provider' => 'mux', 'video_id' => 'v2', 'duration_seconds' => 300, 'is_published' => true, 'is_free' => true]);

        $planMaths = SubscriptionPlan::create(['subject_id' => $maths->id, 'name' => 'Year 3 Maths Monthly', 'billing_cycle' => 'monthly', 'price' => 15.00, 'is_active' => true]);
        $planMaths->subjects()->sync([$maths->id]);

        $parent = User::create(['name' => 'Parent', 'email' => 'p@test.dev', 'password' => bcrypt('secret')]);
        $parent->roles()->attach(Role::where('name', Role::PARENT)->first());

        $studentA = Student::create(['parent_id' => $parent->id, 'name' => 'A', 'student_code' => 'STU-2026-00001']);
        $studentB = Student::create(['parent_id' => $parent->id, 'name' => 'B', 'student_code' => 'STU-2026-00002']);

        app(SubscriptionService::class)->subscribe($studentA, $planMaths);

        return compact('parent', 'studentA', 'studentB', 'maths', 'science', 'lesson', 'freeLesson', 'courseMaths');
    }

    public function test_active_subscription_grants_subject_access(): void
    {
        $d = $this->seedMinimalData();
        $access = app(AccessControlService::class);

        $this->assertTrue($access->canAccessSubject($d['studentA'], $d['maths']));
        $this->assertFalse($access->canAccessSubject($d['studentA'], $d['science']));
        $this->assertFalse($access->canAccessSubject($d['studentB'], $d['maths']));
    }

    public function test_lesson_access_follows_subscription(): void
    {
        $d = $this->seedMinimalData();
        $access = app(AccessControlService::class);

        $this->assertTrue($access->canAccessLesson($d['studentA'], $d['lesson']));
        $this->assertFalse($access->canAccessLesson($d['studentB'], $d['lesson']));
        // free preview open to everyone
        $this->assertTrue($access->canAccessLesson($d['studentB'], $d['freeLesson']));
    }

    public function test_paid_lessons_are_limited_by_subscription_period(): void
    {
        Carbon::setTestNow('2026-09-08 10:00:00');

        $d = $this->seedMinimalData();
        $access = app(AccessControlService::class);
        $subscription = $d['studentA']->subscriptions()->first();
        $subscription->update([
            'started_at' => now(),
            'expires_at' => now()->addDays(90),
        ]);

        $paidLessons = collect([$d['lesson']]);
        for ($i = 2; $i <= 13; $i++) {
            $paidLessons->push(Lesson::create([
                'module_id' => $d['lesson']->module_id,
                'title' => 'Month paced lesson '.$i,
                'sort_order' => $i,
                'video_provider' => 'mux',
                'video_id' => 'v'.$i,
                'duration_seconds' => 300,
                'is_published' => true,
                'is_free' => false,
            ]));
        }

        $this->assertTrue($access->canAccessLesson($d['studentA']->refresh(), $paidLessons[11]));
        $this->assertFalse($access->canAccessLesson($d['studentA']->refresh(), $paidLessons[12]));

        Carbon::setTestNow();
    }

    public function test_expired_subscription_revokes_access_but_keeps_data(): void
    {
        $d = $this->seedMinimalData();
        $access = app(AccessControlService::class);

        // build progress while active
        app(ProgressService::class)->recordWatch($d['studentA'], $d['lesson'], 250, 250);
        $this->assertCount(1, $d['studentA']->progress);

        $sub = $d['studentA']->subscriptions()->first();
        $sub->update(['status' => Subscription::STATUS_EXPIRED, 'expires_at' => now()->subDay(), 'grace_ends_at' => now()->subDay()]);

        $sub->reconcile();
        $this->assertFalse($access->canAccessSubject($d['studentA']->refresh(), $d['maths']));
        // progress preserved after revocation
        $this->assertCount(1, $d['studentA']->refresh()->progress);
    }

    public function test_dunning_flow_blocks_then_restores_access(): void
    {
        $d = $this->seedMinimalData();
        $service = app(SubscriptionService::class);
        $access = app(AccessControlService::class);
        $sub = $d['studentA']->subscriptions()->first();

        $payment = $sub->payments()->create([
            'invoice_no' => Payment::generateInvoiceNo(),
            'amount' => 15,
            'discount_amount' => 0,
            'total' => 15,
            'status' => 'pending',
        ]);

        $service->markRenewalFailed($sub, $payment);

        $this->assertEquals(Subscription::STATUS_PAST_DUE, $sub->status);
        $this->assertFalse($access->canAccessSubject($d['studentA'], $d['maths']));

        $service->markPaid($sub, 'tx-retry-1');

        $this->assertEquals(Subscription::STATUS_ACTIVE, $sub->status);
        $this->assertTrue($access->canAccessSubject($d['studentA'], $d['maths']));
    }

    public function test_completing_course_issues_certificate(): void
    {
        $d = $this->seedMinimalData();
        $progress = app(ProgressService::class);

        $this->assertNull($progress->finaliseCourseIfComplete($d['studentA'], $d['courseMaths']));
        $progress->markCompleted($d['studentA'], $d['lesson']);
        $progress->markCompleted($d['studentA'], $d['freeLesson']);
        $this->assertNotNull($progress->finaliseCourseIfComplete($d['studentA'], $d['courseMaths']));
    }

    public function test_parent_dashboard_renders_children_with_subscriptions(): void
    {
        $d = $this->seedMinimalData();

        $response = $this->actingAs($d['parent'])->get('/parent');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Parent/Dashboard')
                ->has('children', 2));
    }
}
