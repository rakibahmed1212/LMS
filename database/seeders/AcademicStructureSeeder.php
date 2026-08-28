<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\ClassYear;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        $year3 = ClassYear::firstOrCreate(['slug' => 'year-3'], ['name' => 'Year 3', 'sort_order' => 1]);
        $year4 = ClassYear::firstOrCreate(['slug' => 'year-4'], ['name' => 'Year 4', 'sort_order' => 2]);

        $maths = Subject::firstOrCreate(
            ['slug' => 'year-3-mathematics'],
            ['name' => 'Mathematics', 'class_year_id' => $year3->id, 'color' => '#4f46e5', 'sort_order' => 1],
        );
        $english = Subject::firstOrCreate(
            ['slug' => 'year-3-english'],
            ['name' => 'English', 'class_year_id' => $year3->id, 'color' => '#0891b2', 'sort_order' => 2],
        );
        Subject::firstOrCreate(
            ['slug' => 'year-3-science'],
            ['name' => 'Science', 'class_year_id' => $year3->id, 'color' => '#16a34a', 'sort_order' => 3],
        );
        Subject::firstOrCreate(
            ['slug' => 'year-4-mathematics'],
            ['name' => 'Mathematics', 'class_year_id' => $year4->id, 'color' => '#7c3aed', 'sort_order' => 1],
        );

        // PRD §5.15 pricing table
        $this->createPlan('Year 3 Maths Monthly', $maths, 'monthly', 15.00);
        $this->createPlan('Year 3 Maths Annual', $maths, 'annual', 150.00);
        $this->createPlan('Year 3 English Monthly', $english, 'monthly', 15.00);
        $this->createPlan('Year 3 English Annual', $english, 'annual', 150.00);

        $bundleMonthly = SubscriptionPlan::firstOrCreate(
            ['name' => 'Year 3 Maths + English Bundle Monthly'],
            [
                'subject_id' => $maths->id,
                'billing_cycle' => 'monthly',
                'price' => 25.00,
                'title_internal' => 'Year 3 Maths + English Bundle',
                'is_bundle' => true,
                'trial_days' => 7,
            ],
        );
        $bundleMonthly->subjects()->syncWithoutDetaching([$maths->id, $english->id]);

        $bundleAnnual = SubscriptionPlan::firstOrCreate(
            ['name' => 'Year 3 Maths + English Bundle Annual'],
            [
                'subject_id' => $maths->id,
                'billing_cycle' => 'annual',
                'price' => 250.00,
                'title_internal' => 'Year 3 Maths + English Bundle',
                'is_bundle' => true,
            ],
        );
        $bundleAnnual->subjects()->syncWithoutDetaching([$maths->id, $english->id]);

        // Sample curriculum for Year 3 Maths
        $mathsCourse = Course::firstOrCreate(
            ['slug' => 'year-3-mathematics-core'],
            [
                'subject_id' => $maths->id,
                'title' => 'Year 3 Mathematics — Core Syllabus',
                'description' => 'Numbers, fractions, and geometry for Year 3 pupils.',
                'is_published' => true,
            ],
        );

        $module = Module::firstOrCreate(
            ['course_id' => $mathsCourse->id, 'title' => 'Fractions'],
            ['description' => 'Fundamentals of fractions', 'sort_order' => 1],
        );

        $lesson1 = $this->createLesson($module, 'Introduction to Fractions',
            'What is a fraction? Numerator and denominator.', 1, 'meal-planning-intro');
        $lesson2 = $this->createLesson($module, 'Equivalent Fractions',
            'Why 1/2 equals 2/4. Multiplication vs division.', 2, 'equivalent-fractions');
        $lesson3 = $this->createLesson($module, 'Adding & Subtracting Fractions',
            'Same denominators first, then common denominators.', 3, 'adding-fractions');

        $lesson1->worksheets()->firstOrCreate(
            ['title' => 'Fraction Worksheet 1'],
            ['file_url' => '/storage/worksheets/fractions-1.pdf'],
        );

        $quiz = Quiz::firstOrCreate(
            ['lesson_id' => $lesson1->id, 'title' => 'Fractions — Quick Check'],
            [
                'type' => Quiz::TYPE_GRADED,
                'time_limit_minutes' => 10,
                'max_score' => 100,
                'shuffle_questions' => true,
                'is_published' => true,
            ],
        );

        Question::firstOrCreate(
            ['quiz_id' => $quiz->id, 'text' => 'Which is the numerator in 3/5?'],
            [
                'type' => Question::TYPE_MCQ,
                'options' => ['3', '5', '8', '2'],
                'correct_answer' => '0',
                'points' => 5,
                'bank_tag' => 'fractions',
                'sort_order' => 1,
            ],
        );
        Question::firstOrCreate(
            ['quiz_id' => $quiz->id, 'text' => '1/2 is equal to 2/4.'],
            [
                'type' => Question::TYPE_TRUE_FALSE,
                'options' => ['True', 'False'],
                'correct_answer' => '0',
                'points' => 5,
                'bank_tag' => 'fractions',
                'sort_order' => 2,
            ],
        );

        Assignment::firstOrCreate(
            ['lesson_id' => $lesson2->id, 'title' => 'Equivalent Fractions Homework'],
            ['description' => 'Complete the worksheet and upload a scan of your answers.', 'max_score' => 100, 'allow_resubmission' => true, 'deadline' => now()->addDays(7)],
        );
    }

    private function createPlan(string $name, Subject $subject, string $cycle, float $price): void
    {
        $plan = SubscriptionPlan::firstOrCreate(
            ['name' => $name],
            ['subject_id' => $subject->id, 'billing_cycle' => $cycle, 'price' => $price, 'is_active' => true],
        );
        $plan->subjects()->syncWithoutDetaching([$subject->id]);
    }

    private function createLesson(Module $module, string $title, string $notes, int $order, string $videoRef): Lesson
    {
        return Lesson::firstOrCreate(
            ['module_id' => $module->id, 'title' => $title],
            [
                'notes' => $notes,
                'sort_order' => $order,
                'video_provider' => 'mux',
                'video_id' => $videoRef,
                'duration_seconds' => 8 * 60 + $order * 37,
                'is_published' => true,
                'is_free' => $order === 1,
            ],
        );
    }
}
