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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoursePortalTest extends TestCase
{
    use RefreshDatabase;

    private function courseSetup(): array
    {
        Role::firstOrCreate(['name' => Role::PARENT]);

        $classYear = ClassYear::create(['name' => 'Year 3', 'slug' => 'year-3']);
        $subject = Subject::create(['class_year_id' => $classYear->id, 'name' => 'Maths', 'slug' => 'maths']);
        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => 'Year 3 Maths',
            'slug' => 'year-3-maths',
            'description' => 'Demo maths course.',
            'is_published' => true,
        ]);
        $module = Module::create(['course_id' => $course->id, 'title' => 'Fractions']);
        $module->lessons()->create([
            'title' => 'Intro',
            'duration_seconds' => 300,
            'is_published' => true,
            'is_free' => true,
        ]);

        $plan = SubscriptionPlan::create([
            'subject_id' => $subject->id,
            'name' => 'Year 3 Maths Monthly',
            'billing_cycle' => 'monthly',
            'price' => 15,
        ]);
        $plan->subjects()->sync([$subject->id]);

        $parent = User::factory()->create();
        $parent->roles()->attach(Role::where('name', Role::PARENT)->first());
        $student = Student::create(['parent_id' => $parent->id, 'name' => 'Kid']);

        return compact('parent', 'student', 'course', 'plan');
    }

    public function test_course_portal_lists_published_courses(): void
    {
        $data = $this->courseSetup();

        $this->actingAs($data['parent'])
            ->get(route('courses.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Courses/Index')
                ->where('years.0.subjects.0.courses.0.slug', 'year-3-maths'));
    }

    public function test_course_detail_requires_subscription_for_child_without_access(): void
    {
        $data = $this->courseSetup();

        $this->actingAs($data['parent'])
            ->get(route('courses.show', ['course' => $data['course']->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Courses/Show')
                ->where('children.0.has_access', false)
                ->has('plans.0'));
    }

    public function test_course_detail_unlocks_for_subscribed_child(): void
    {
        $data = $this->courseSetup();

        $subscription = app(SubscriptionService::class)->subscribe($data['student'], $data['plan']);
        app(SubscriptionService::class)->markPaid($subscription, 'portal-test');

        $this->actingAs($data['parent'])
            ->get(route('courses.show', ['course' => $data['course']->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Courses/Show')
                ->where('children.0.has_access', true));
    }
}
