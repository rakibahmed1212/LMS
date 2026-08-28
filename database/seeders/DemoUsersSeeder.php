<?php

namespace Database\Seeders;

use App\Models\ClassYear;
use App\Models\Lesson;
use App\Models\Role;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ProgressService;
use App\Services\SubscriptionService;
use Illuminate\Database\Seeder;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $subscriptionService = app(SubscriptionService::class);
        $progressService = app(ProgressService::class);

        // ---- Staff ----
        $admin = User::firstOrCreate(
            ['email' => 'admin@lms.test'],
            ['name' => 'Super Admin', 'password' => bcrypt('password')],
        );
        $admin->roles()->syncWithoutDetaching(Role::where('name', Role::SUPER_ADMIN)->pluck('id'));

        $tutor = User::firstOrCreate(
            ['email' => 'tutor@lms.test'],
            ['name' => 'Ms. Rahman', 'password' => bcrypt('password')],
        );
        $tutor->roles()->syncWithoutDetaching(Role::where('name', Role::TUTOR)->pluck('id'));

        // ---- Parent ----
        $parent = User::firstOrCreate(
            ['email' => 'parent@lms.test'],
            ['name' => 'Ayesha Khan', 'phone' => '+8801712345678', 'password' => bcrypt('password')],
        );
        $parent->roles()->syncWithoutDetaching(Role::where('name', Role::PARENT)->pluck('id'));

        $childA = Student::firstOrCreate(
            ['parent_id' => $parent->id, 'name' => 'Aarav Khan'],
            ['school' => 'Sunrise Academy', 'dob' => now()->subYears(9), 'gender' => 'male'],
        );
        $childB = Student::firstOrCreate(
            ['parent_id' => $parent->id, 'name' => 'Zara Khan'],
            ['school' => 'Sunrise Academy', 'dob' => now()->subYears(7), 'gender' => 'female'],
        );

        $year3 = ClassYear::where('slug', 'year-3')->first();
        $childA->classYears()->sync([$year3->id]);
        $childB->classYears()->sync([$year3->id]);

        // ---- Subscriptions: child A has active Maths + trial English; Science NOT subscribed ----
        $planMaths = SubscriptionPlan::where('name', 'Year 3 Maths Monthly')->first();
        $planEnglish = SubscriptionPlan::where('name', 'Year 3 English Monthly')->first();

        $subMaths = $subscriptionService->subscribe($childA, $planMaths);
        $subscriptionService->markPaid($subMaths, 'demo-tx-001');
        $subMaths->update(['status' => Subscription::STATUS_ACTIVE]);

        // English via trial (bundle-equivalent granularity per subject)
        $subEnglish = $subscriptionService->subscribe($childA, $planEnglish);
        $subEnglish->update(['status' => Subscription::STATUS_TRIAL]);
        $subscriptionService->markPaid($subEnglish, 'demo-tx-002');

        // ---- Progress for child A on the Maths lessons ----
        $lessons = Lesson::query()
            ->whereHas('module.course.subject', fn ($q) => $q->where('slug', 'year-3-mathematics'))
            ->orderBy('sort_order')
            ->get();

        foreach ($lessons as $i => $lesson) {
            $progressService->recordWatch(
                $childA,
                $lesson,
                watchedSeconds: (int) ($lesson->duration_seconds * ($i === 0 ? 0.95 : 0.55)),
                lastPosition: $i === 0 ? 450 : 120,
            );
        }

        // ---- Child B has no subscription: demonstrates Access denied ----
    }
}
