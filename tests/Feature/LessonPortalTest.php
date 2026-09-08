<?php

namespace Tests\Feature;

use App\Models\ClassYear;
use App\Models\Course;
use App\Models\DiscussionThread;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Role;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonPortalTest extends TestCase
{
    use RefreshDatabase;

    private function setupLessonData(): array
    {
        Role::firstOrCreate(['name' => Role::PARENT]);

        $year = ClassYear::create(['name' => 'Year 3', 'slug' => 'year-3']);
        $subject = Subject::create(['class_year_id' => $year->id, 'name' => 'Maths', 'slug' => 'maths']);
        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => 'Maths Core',
            'slug' => 'maths-core',
            'is_published' => true,
        ]);
        $module = Module::create(['course_id' => $course->id, 'title' => 'Fractions']);
        $freeLesson = Lesson::create([
            'module_id' => $module->id,
            'title' => 'Free intro',
            'duration_seconds' => 300,
            'is_published' => true,
            'is_free' => true,
        ]);
        $paidLesson = Lesson::create([
            'module_id' => $module->id,
            'title' => 'Paid lesson',
            'duration_seconds' => 360,
            'is_published' => true,
            'is_free' => false,
        ]);
        $plan = SubscriptionPlan::create([
            'subject_id' => $subject->id,
            'name' => 'Maths Monthly',
            'billing_cycle' => 'monthly',
            'price' => 15,
        ]);
        $plan->subjects()->sync([$subject->id]);

        $parent = User::factory()->create();
        $parent->roles()->attach(Role::where('name', Role::PARENT)->first());
        $student = Student::create(['parent_id' => $parent->id, 'name' => 'Kid']);

        return compact('freeLesson', 'paidLesson', 'plan', 'parent', 'student');
    }

    public function test_guest_can_open_free_preview_lesson(): void
    {
        $data = $this->setupLessonData();

        $this->get(route('lessons.show', $data['freeLesson']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Lessons/Show')
                ->where('hasAccess', true));
    }

    public function test_paid_lesson_is_locked_without_subscription(): void
    {
        $data = $this->setupLessonData();

        $this->actingAs($data['parent'])
            ->get(route('lessons.show', ['lesson' => $data['paidLesson']->id, 'student' => $data['student']->id]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Lessons/Show')
                ->where('hasAccess', false));
    }

    public function test_paid_lesson_unlocks_for_subscribed_student(): void
    {
        $data = $this->setupLessonData();

        $subscription = app(SubscriptionService::class)->subscribe($data['student'], $data['plan']);
        app(SubscriptionService::class)->markPaid($subscription, 'lesson-test');

        $this->actingAs($data['parent'])
            ->get(route('lessons.show', ['lesson' => $data['paidLesson']->id, 'student' => $data['student']->id]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Lessons/Show')
                ->where('hasAccess', true));
    }

    public function test_subscribed_student_can_save_lesson_progress(): void
    {
        $data = $this->setupLessonData();

        $subscription = app(SubscriptionService::class)->subscribe($data['student'], $data['plan']);
        app(SubscriptionService::class)->markPaid($subscription, 'lesson-progress-test');

        $this->actingAs($data['parent'])
            ->post(route('lessons.progress', $data['paidLesson']), [
                'student_id' => $data['student']->id,
                'watched_seconds' => 180,
                'last_position_seconds' => 180,
            ])
            ->assertRedirect();

        $this->assertTrue(Progress::where('student_id', $data['student']->id)
            ->where('lesson_id', $data['paidLesson']->id)
            ->where('watch_percent', 50)
            ->exists());
    }

    public function test_subscribed_student_can_post_lesson_question(): void
    {
        $data = $this->setupLessonData();

        $subscription = app(SubscriptionService::class)->subscribe($data['student'], $data['plan']);
        app(SubscriptionService::class)->markPaid($subscription, 'lesson-question-test');

        $this->actingAs($data['parent'])
            ->post(route('lessons.questions.store', $data['paidLesson']), [
                'student_id' => $data['student']->id,
                'message' => 'Can you explain this again?',
            ])
            ->assertRedirect();

        $this->assertTrue(DiscussionThread::where('student_id', $data['student']->id)
            ->where('lesson_id', $data['paidLesson']->id)
            ->where('message', 'Can you explain this again?')
            ->exists());
    }
}
