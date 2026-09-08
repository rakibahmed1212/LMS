<?php

namespace Tests\Feature;

use App\Models\ClassYear;
use App\Models\Course;
use App\Models\Module;
use App\Models\Role;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStudentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_student_registry_by_student_id(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', Role::SUPER_ADMIN)->first());
        $parent = User::factory()->create(['name' => 'Sarah Parent', 'email' => 'sarah@example.test']);
        Student::create(['parent_id' => $parent->id, 'name' => 'James', 'student_code' => 'STU-2026-00125']);
        Student::create(['parent_id' => $parent->id, 'name' => 'Emily', 'student_code' => 'STU-2026-00126']);

        $this->actingAs($admin)
            ->get(route('admin.students.index', ['q' => '00125']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Students')
                ->where('students.data.0.student_code', 'STU-2026-00125')
                ->has('students.data', 1)
                ->has('stats'));
    }

    public function test_parent_cannot_access_admin_student_registry(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $parent = User::factory()->create();
        $parent->roles()->attach(Role::where('name', Role::PARENT)->first());

        $this->actingAs($parent)
            ->get(route('admin.students.index'))
            ->assertForbidden();
    }

    public function test_parent_role_can_create_own_students(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $parent = User::factory()->create();
        $parent->roles()->attach(Role::where('name', Role::PARENT)->first());
        $year = ClassYear::create(['name' => 'Year 3', 'slug' => 'year-3']);

        $this->actingAs($parent)
            ->post(route('parent.students.store'), [
                'name' => 'New Child',
                'dob' => '2018-01-01',
                'school' => 'Demo Primary',
                'gender' => 'female',
                'class_year_id' => $year->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('students', [
            'parent_id' => $parent->id,
            'name' => 'New Child',
            'school' => 'Demo Primary',
        ]);
    }

    public function test_admin_student_registry_shows_subscription_snapshot(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', Role::SUPER_ADMIN)->first());
        $parent = User::factory()->create();
        $student = Student::create(['parent_id' => $parent->id, 'name' => 'Kid']);
        $year = ClassYear::create(['name' => 'Year 4', 'slug' => 'year-4']);
        $subject = Subject::create(['class_year_id' => $year->id, 'name' => 'Maths', 'slug' => 'maths']);
        $plan = SubscriptionPlan::create([
            'subject_id' => $subject->id,
            'name' => 'Year 4 Maths Monthly',
            'billing_cycle' => 'monthly',
            'price' => 15,
        ]);
        $plan->subjects()->sync([$subject->id]);
        $subscription = app(SubscriptionService::class)->subscribe($student, $plan);
        app(SubscriptionService::class)->markPaid($subscription, 'admin-student-test');

        $this->actingAs($admin)
            ->get(route('admin.students.index', ['subscription_status' => 'active']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('students.data.0.subscriptions.0.status', 'active')
                ->where('students.data.0.subscriptions.0.latest_payment.status', 'paid'));
    }

    public function test_course_search_finds_lesson_topics(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $year = ClassYear::create(['name' => 'Year 3', 'slug' => 'year-3']);
        $subject = Subject::create(['class_year_id' => $year->id, 'name' => 'Maths', 'slug' => 'maths']);
        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => 'Year 3 Maths',
            'slug' => 'year-3-maths',
            'description' => 'Demo maths course.',
            'is_published' => true,
        ]);
        $module = Module::create(['course_id' => $course->id, 'title' => 'Fractions']);
        $module->lessons()->create([
            'title' => 'Adding Fractions',
            'duration_seconds' => 300,
            'is_published' => true,
        ]);

        $this->get(route('courses.index', ['q' => 'fractions']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Courses/Index')
                ->where('years.0.subjects.0.courses.0.slug', 'year-3-maths')
                ->where('filters.q', 'fractions'));
    }
}
