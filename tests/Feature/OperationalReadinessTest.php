<?php

namespace Tests\Feature;

use App\Models\ClassYear;
use App\Models\Course;
use App\Models\Module;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalReadinessTest extends TestCase
{
    use RefreshDatabase;

    private function setupSubscription(): array
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $parent = User::factory()->create();
        $parent->roles()->attach(Role::where('name', Role::PARENT)->first());
        $student = Student::create(['parent_id' => $parent->id, 'name' => 'Kid']);
        $year = ClassYear::create(['name' => 'Year 3', 'slug' => 'year-3']);
        $subject = Subject::create(['class_year_id' => $year->id, 'name' => 'Maths', 'slug' => 'maths']);
        $plan = SubscriptionPlan::create([
            'subject_id' => $subject->id,
            'name' => 'Maths Monthly',
            'billing_cycle' => 'monthly',
            'price' => 15,
        ]);
        $plan->subjects()->sync([$subject->id]);

        return compact('parent', 'student', 'plan');
    }

    public function test_subscription_purchase_creates_notification_and_receipt(): void
    {
        $data = $this->setupSubscription();

        $this->actingAs($data['parent'])
            ->post(route('subscriptions.store', $data['student']), [
                'plan_id' => $data['plan']->id,
            ])
            ->assertRedirect(route('parent.dashboard'));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $data['parent']->id,
            'type' => 'subscription_confirmation',
        ]);

        $payment = $data['student']->subscriptions()->first()->payments()->first();

        $this->actingAs($data['parent'])
            ->get(route('payments.receipt', $payment))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Payments/Receipt')
                ->where('receipt.invoice_no', $payment->invoice_no));
    }

    public function test_notification_center_lists_and_marks_read(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $parent = User::factory()->create();
        $notification = Notification::create([
            'user_id' => $parent->id,
            'type' => 'welcome',
            'title' => 'Welcome',
            'body' => 'Your account is ready.',
        ]);

        $this->actingAs($parent)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Notifications/Index')
                ->where('notifications.data.0.title', 'Welcome'));

        $this->actingAs($parent)
            ->patch(route('notifications.read', $notification))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_admin_can_view_audit_logs_and_platform_readiness(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', Role::SUPER_ADMIN)->first());

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/AuditLogs'));

        $this->actingAs($admin)
            ->get(route('admin.platform.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Platform')
                ->has('integrations', 5));
    }

    public function test_content_manager_can_create_lesson_from_backend(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $contentManager = User::factory()->create();
        $contentManager->roles()->attach(Role::where('name', Role::CONTENT_MANAGER)->first());
        $year = ClassYear::create(['name' => 'Year 3', 'slug' => 'year-3']);
        $subject = Subject::create(['class_year_id' => $year->id, 'name' => 'Maths', 'slug' => 'maths']);
        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => 'Maths',
            'slug' => 'maths',
            'is_published' => true,
        ]);
        $module = Module::create(['course_id' => $course->id, 'title' => 'Fractions']);

        $this->actingAs($contentManager)
            ->post(route('admin.content.lessons.store'), [
                'module_id' => $module->id,
                'title' => 'New backend lesson',
                'notes' => 'Created from admin content panel.',
                'duration_seconds' => 600,
                'video_provider' => 'mux',
                'video_id' => 'new-backend-lesson',
                'is_free' => false,
                'is_published' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lessons', [
            'module_id' => $module->id,
            'title' => 'New backend lesson',
        ]);
    }
}
