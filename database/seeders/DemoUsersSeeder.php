<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ClassYear;
use App\Models\Course;
use App\Models\Gradebook;
use App\Models\LiveClass;
use App\Models\Payment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Role;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\TutorAssignment;
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

        $admin = $this->user('admin@lms.test', 'Super Admin', Role::SUPER_ADMIN);
        $tutor = $this->user('tutor@lms.test', 'Ms. Rahman', Role::TUTOR);
        $contentManager = $this->user('content@lms.test', 'Content Manager', Role::CONTENT_MANAGER);
        $inactiveStaff = $this->user('inactive@lms.test', 'Inactive Staff', Role::TUTOR, false);

        $parentA = $this->user('parent@lms.test', 'Ayesha Khan', Role::PARENT, true, '+8801712345678');
        $parentB = $this->user('rahim.parent@lms.test', 'Rahim Chowdhury', Role::PARENT, true, '+8801811122233');
        $parentC = $this->user('sara.parent@lms.test', 'Sara Ahmed', Role::PARENT, true, '+447700900123');

        $session = AcademicSession::where('is_active', true)->latest('starts_at')->first();

        $aarav = $this->student($parentA, 'Aarav Khan', 'Sunrise Academy', 'male', 9, 'year-3', $session);
        $zara = $this->student($parentA, 'Zara Khan', 'Sunrise Academy', 'female', 7, 'year-3', $session);
        $nabil = $this->student($parentB, 'Nabil Chowdhury', 'Maple Leaf School', 'male', 8, 'year-3', $session);
        $maya = $this->student($parentC, 'Maya Ahmed', 'Northbridge Primary', 'female', 10, 'year-4', $session);

        $mathsPlan = SubscriptionPlan::where('name', 'Year 3 Maths Monthly')->first();
        $englishPlan = SubscriptionPlan::where('name', 'Year 3 English Monthly')->first();
        $sciencePlan = SubscriptionPlan::where('name', 'Year 3 Science Monthly')->first();
        $year4MathsPlan = SubscriptionPlan::where('name', 'Year 4 Maths Annual')->first();

        $this->subscription($subscriptionService, $aarav, $mathsPlan, Subscription::STATUS_ACTIVE, now()->addDays(24), 'demo-tx-aarav-maths');
        $this->subscription($subscriptionService, $aarav, $englishPlan, Subscription::STATUS_TRIAL, now()->addDays(36), 'demo-tx-aarav-english');
        $this->subscription($subscriptionService, $nabil, $sciencePlan, Subscription::STATUS_PAST_DUE, now()->addDays(2), 'demo-tx-nabil-science', [
            'grace_ends_at' => now()->addDays(2),
            'dunning_stage' => 'dunning_1',
            'dunning_attempts' => 1,
        ]);
        $this->subscription($subscriptionService, $maya, $year4MathsPlan, Subscription::STATUS_ACTIVE, now()->addMonths(10), 'demo-tx-maya-y4-maths');

        $mathsCourse = Course::where('slug', 'year-3-mathematics-core')->first();
        $englishCourse = Course::where('slug', 'year-3-english-reading-writing')->first();
        $scienceCourse = Course::where('slug', 'year-3-science-explorer')->first();
        $year4MathsCourse = Course::where('slug', 'year-4-mathematics-booster')->first();

        foreach ([$mathsCourse, $englishCourse, $scienceCourse, $year4MathsCourse] as $course) {
            TutorAssignment::updateOrCreate(['tutor_id' => $tutor->id, 'course_id' => $course->id]);
        }
        $tutor->assignedStudents()->syncWithoutDetaching([$aarav->id, $zara->id, $nabil->id, $maya->id]);

        $this->progress($progressService, $aarav, $mathsCourse, [100, 100, 72, 35, 0]);
        $this->progress($progressService, $aarav, $englishCourse, [100, 55, 0, 0]);
        $this->progress($progressService, $nabil, $scienceCourse, [45, 0, 0, 0]);
        $this->progress($progressService, $maya, $year4MathsCourse, [100, 100, 100, 100]);

        $this->assessments($aarav, $mathsCourse, 84, 78);
        $this->assessments($aarav, $englishCourse, 68, 72);
        $this->assessments($nabil, $scienceCourse, 42, 0);
        $this->assessments($maya, $year4MathsCourse, 94, 91);

        foreach ([$mathsCourse, $englishCourse, $scienceCourse, $year4MathsCourse] as $index => $course) {
            LiveClass::updateOrCreate(
                ['course_id' => $course->id, 'title' => $course->subject->name.' Weekly Support'],
                [
                    'subject_id' => $course->subject_id,
                    'tutor_id' => $tutor->id,
                    'provider' => $index % 2 === 0 ? 'zoom' : 'google_meet',
                    'meeting_url' => 'https://meet.example.test/'.str($course->slug)->slug(),
                    'scheduled_at' => now()->addDays($index + 1)->setTime(18, 0),
                    'duration_minutes' => 60,
                    'is_active' => true,
                ],
            );
        }

        $admin->roles()->syncWithoutDetaching(Role::where('name', Role::SUPER_ADMIN)->pluck('id'));
        $contentManager->roles()->syncWithoutDetaching(Role::where('name', Role::CONTENT_MANAGER)->pluck('id'));
        $inactiveStaff->forceFill(['is_active' => false])->save();
    }

    private function user(string $email, string $name, string $role, bool $active = true, ?string $phone = null): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $phone,
                'password' => bcrypt('password'),
                'is_active' => $active,
                'email_verified_at' => now(),
            ],
        );

        $roleModel = Role::where('name', $role)->first();
        if ($roleModel) {
            $user->roles()->syncWithoutDetaching($roleModel->id);
        }

        return $user;
    }

    private function student(User $parent, string $name, string $school, string $gender, int $age, string $yearSlug, ?AcademicSession $session): Student
    {
        $student = Student::firstOrCreate(
            ['parent_id' => $parent->id, 'name' => $name],
            [
                'school' => $school,
                'dob' => now()->subYears($age),
                'gender' => $gender,
            ],
        );

        $student->update([
            'preferred_name' => str($name)->before(' ')->toString(),
            'school' => $school,
            'gender' => $gender,
            'address_line1' => '12 Demo Street',
            'city' => 'London',
            'postcode' => 'E1 6AN',
            'country' => 'United Kingdom',
            'emergency_contact_name' => $parent->name,
            'emergency_contact_phone' => $parent->phone,
            'learning_needs' => $age <= 8 ? 'Benefits from shorter lessons and visual examples.' : 'Preparing for stronger independent practice.',
            'medical_notes' => 'No medical notes recorded.',
            'is_active' => true,
        ]);

        $classYearId = ClassYear::where('slug', $yearSlug)->value('id');
        $student->enrollments()->updateOrCreate(
            ['class_year_id' => $classYearId, 'academic_session_id' => $session?->id],
            [],
        );

        return $student;
    }

    private function subscription(
        SubscriptionService $service,
        Student $student,
        ?SubscriptionPlan $plan,
        string $status,
        mixed $expiresAt,
        string $txId,
        array $extra = [],
    ): void {
        if (! $plan) {
            return;
        }

        $subscription = Subscription::where('student_id', $student->id)
            ->where('plan_id', $plan->id)
            ->first();

        if (! $subscription) {
            $subscription = $service->subscribe($student, $plan);
            $service->markPaid($subscription, $txId);
        }

        $subscription->forceFill([
            'status' => $status,
            'expires_at' => $expiresAt,
            'billing_cycle' => $plan->billing_cycle,
            'subscribed_price' => $plan->price,
            'currency' => $plan->currency,
            'gateway' => 'stripe',
            'gateway_subscription_id' => 'sub_demo_'.$subscription->id,
            ...$extra,
        ])->save();

        $payment = $subscription->payments()->where('gateway_transaction_id', $txId)->first();
        if (! $payment) {
            $payment = $subscription->payments()->latest('id')->first();
        }

        if ($payment) {
            $payment->forceFill([
                'status' => Payment::STATUS_PAID,
                'gateway' => 'stripe',
                'gateway_transaction_id' => $txId,
                'paid_at' => now()->subDays(rand(1, 20)),
            ])->save();
        }
    }

    private function progress(ProgressService $service, Student $student, ?Course $course, array $percentages): void
    {
        if (! $course) {
            return;
        }

        $lessons = $course->lessons()->where('lessons.is_published', true)->orderBy('lessons.id')->get();

        foreach ($lessons as $index => $lesson) {
            $percent = $percentages[$index] ?? 0;
            if ($percent <= 0) {
                continue;
            }

            $watchedSeconds = (int) round($lesson->duration_seconds * ($percent / 100));
            if ($percent >= ProgressService::COMPLETION_THRESHOLD) {
                $service->markCompleted($student, $lesson);
            } else {
                $service->recordWatch($student, $lesson, $watchedSeconds, $watchedSeconds);
            }
        }
    }

    private function assessments(Student $student, ?Course $course, float $quizScore, float $assignmentScore): void
    {
        if (! $course) {
            return;
        }

        $quiz = Quiz::whereHas('lesson.module', fn ($query) => $query->where('course_id', $course->id))->first();
        if ($quiz) {
            QuizAttempt::updateOrCreate(
                ['quiz_id' => $quiz->id, 'student_id' => $student->id, 'attempt_no' => 1],
                [
                    'status' => QuizAttempt::STATUS_SUBMITTED,
                    'score' => $quizScore,
                    'max_score' => 100,
                    'started_at' => now()->subDays(3),
                    'submitted_at' => now()->subDays(3)->addMinutes(9),
                    'answers' => ['demo' => true],
                ],
            );
        }

        $assignment = Assignment::whereHas('lesson.module', fn ($query) => $query->where('course_id', $course->id))->first();
        if ($assignment) {
            AssignmentSubmission::updateOrCreate(
                ['assignment_id' => $assignment->id, 'student_id' => $student->id],
                [
                    'file_path' => '/storage/submissions/demo-'.$student->id.'-'.$assignment->id.'.pdf',
                    'comment' => 'Demo homework upload.',
                    'score' => $assignmentScore > 0 ? $assignmentScore : null,
                    'feedback' => $assignmentScore > 0 ? 'Good work. Review the marked corrections.' : null,
                    'status' => $assignmentScore > 0 ? AssignmentSubmission::STATUS_GRADED : AssignmentSubmission::STATUS_SUBMITTED,
                    'is_late' => false,
                    'submitted_at' => now()->subDays(2),
                    'graded_at' => $assignmentScore > 0 ? now()->subDay() : null,
                ],
            );
        }

        $gradebook = Gradebook::firstOrCreate([
            'student_id' => $student->id,
            'subject_id' => $course->subject_id,
            'term' => 'Autumn',
        ]);
        $gradebook->recalc($quizScore, $assignmentScore);
    }
}
