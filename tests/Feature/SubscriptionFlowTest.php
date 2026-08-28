<?php

namespace Tests\Feature;

use App\Models\ClassYear;
use App\Models\Coupon;
use App\Models\Role;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionFlowTest extends TestCase
{
    use RefreshDatabase;

    private function parentWithStudent(string $slug = 'year-3-maths'): array
    {
        Role::firstOrCreate(['name' => Role::PARENT]);

        $classYear = ClassYear::create(['name' => 'Year 3', 'slug' => 'year-3']);
        $subject = Subject::create(['class_year_id' => $classYear->id, 'name' => 'Maths', 'slug' => $slug]);
        $plan = SubscriptionPlan::create([
            'subject_id' => $subject->id,
            'name' => 'Year 3 Maths Monthly',
            'billing_cycle' => 'monthly',
            'price' => 15.00,
            'trial_days' => 7,
        ]);
        $plan->subjects()->sync([$subject->id]);

        $parent = User::create(['name' => 'P', 'email' => 'p@flow.test', 'password' => bcrypt('secret')]);
        $parent->roles()->attach(Role::where('name', Role::PARENT)->first());
        $student = Student::create(['parent_id' => $parent->id, 'name' => 'Kid', 'student_code' => 'STU-2026-00099']);

        return compact('parent', 'student', 'plan', 'subject');
    }

    public function test_plans_page_lists_available_plans(): void
    {
        $d = $this->parentWithStudent();

        $this->actingAs($d['parent'])
            ->get('/plans')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Plans/Index')
                ->has('years.0.subjects'));
    }

    public function test_parent_can_purchase_subscription_for_own_child(): void
    {
        $d = $this->parentWithStudent();
        Coupon::create(['code' => 'WELCOME10', 'type' => 'percent', 'value' => 10, 'is_active' => true]);

        $response = $this->actingAs($d['parent'])->post(
            '/students/'.$d['student']->id.'/subscribe',
            ['plan_id' => $d['plan']->id, 'coupon_code' => 'WELCOME10'],
        );

        $response->assertRedirect(route('parent.dashboard'));

        $sub = $d['student']->subscriptions()->first();
        $this->assertNotNull($sub);
        $this->assertEquals(Subscription::STATUS_TRIAL, $sub->status);
        $this->assertEquals('monthly', $sub->billing_cycle);
        $this->assertTrue($sub->payments()->where('status', 'paid')->exists());
    }

    public function test_discount_is_applied_for_valid_coupon(): void
    {
        $d = $this->parentWithStudent();
        Coupon::create(['code' => 'WELCOME10', 'type' => 'percent', 'value' => 10, 'is_active' => true]);

        $this->actingAs($d['parent'])->post(
            '/students/'.$d['student']->id.'/subscribe',
            ['plan_id' => $d['plan']->id, 'coupon_code' => 'WELCOME10'],
        );

        $payment = $d['student']->subscriptions()->first()->payments()->first();
        $this->assertEquals(13.50, (float) $payment->total);
        $this->assertEquals(1.50, (float) $payment->discount_amount);
    }

    public function test_parent_cannot_subscribe_for_someone_elses_child(): void
    {
        $d = $this->parentWithStudent();
        $intruder = User::create(['name' => 'Other', 'email' => 'other@flow.test', 'password' => bcrypt('secret')]);
        $intruder->roles()->attach(Role::where('name', Role::PARENT)->first());

        $this->actingAs($intruder)
            ->post('/students/'.$d['student']->id.'/subscribe', ['plan_id' => $d['plan']->id])
            ->assertForbidden();

        $this->assertNull($d['student']->subscriptions()->first());
    }

    public function test_invalid_coupon_rejects_purchase(): void
    {
        $d = $this->parentWithStudent();

        $this->actingAs($d['parent'])
            ->from('/students/'.$d['student']->id.'/subscribe')
            ->post('/students/'.$d['student']->id.'/subscribe', [
                'plan_id' => $d['plan']->id,
                'coupon_code' => 'NOPE',
            ])
            ->assertSessionHasErrors('coupon_code');

        $this->assertNull($d['student']->subscriptions()->first());
    }
}
