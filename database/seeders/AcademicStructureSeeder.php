<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
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
        AcademicSession::updateOrCreate(
            ['name' => '2026-27'],
            ['starts_at' => '2026-09-01', 'ends_at' => '2027-08-31', 'is_active' => true],
        );

        $year3 = $this->classYear('Year 3', 'year-3', 1);
        $year4 = $this->classYear('Year 4', 'year-4', 2);

        $maths = $this->subject($year3, 'Mathematics', 'year-3-mathematics', '#2563eb', 1);
        $english = $this->subject($year3, 'English', 'year-3-english', '#0891b2', 2);
        $science = $this->subject($year3, 'Science', 'year-3-science', '#16a34a', 3);
        $year4Maths = $this->subject($year4, 'Mathematics', 'year-4-mathematics', '#7c3aed', 1);

        foreach ([$maths, $english, $science, $year4Maths] as $subject) {
            $displaySubject = $subject->name === 'Mathematics' ? 'Maths' : $subject->name;
            $prefix = $subject->classYear->name.' '.$displaySubject;
            $this->createPlan($prefix.' Monthly', $subject, 'monthly', $subject->name === 'Science' ? 12.00 : 15.00, 7);
            $this->createPlan($prefix.' Annual', $subject, 'annual', $subject->name === 'Science' ? 120.00 : 150.00);
        }

        $bundleMonthly = SubscriptionPlan::updateOrCreate(
            ['name' => 'Year 3 Maths + English Bundle Monthly'],
            [
                'subject_id' => $maths->id,
                'billing_cycle' => 'monthly',
                'price' => 25.00,
                'title_internal' => 'Year 3 Maths + English Bundle',
                'is_bundle' => true,
                'trial_days' => 7,
                'is_active' => true,
            ],
        );
        $bundleMonthly->subjects()->syncWithoutDetaching([$maths->id, $english->id]);

        $bundleAnnual = SubscriptionPlan::updateOrCreate(
            ['name' => 'Year 3 Maths + English Bundle Annual'],
            [
                'subject_id' => $maths->id,
                'billing_cycle' => 'annual',
                'price' => 250.00,
                'title_internal' => 'Year 3 Maths + English Bundle',
                'is_bundle' => true,
                'is_active' => true,
            ],
        );
        $bundleAnnual->subjects()->syncWithoutDetaching([$maths->id, $english->id]);

        $this->course(
            $maths,
            'Year 3 Mathematics Core Syllabus',
            'year-3-mathematics-core',
            'Numbers, fractions, multiplication and geometry for Year 3 pupils.',
            [
                'Fractions' => [
                    ['Introduction to Fractions', 'What is a fraction? Numerator and denominator.', 'fractions-intro', true],
                    ['Equivalent Fractions', 'Why 1/2 equals 2/4. Multiplication vs division.', 'equivalent-fractions', false],
                    ['Adding and Subtracting Fractions', 'Same denominators first, then common denominators.', 'adding-fractions', false],
                ],
                'Multiplication' => [
                    ['Times Table Patterns', 'Recognise number patterns and build speed.', 'times-table-patterns', false],
                    ['Word Problems', 'Turn short stories into multiplication equations.', 'maths-word-problems', false],
                ],
            ],
        );

        $this->course(
            $english,
            'Year 3 English Reading and Writing',
            'year-3-english-reading-writing',
            'Reading comprehension, grammar, vocabulary and short creative writing.',
            [
                'Reading Skills' => [
                    ['Finding Main Ideas', 'Identify key ideas from a short passage.', 'main-ideas', true],
                    ['Inference Practice', 'Use clues in the text to answer questions.', 'inference-practice', false],
                ],
                'Writing Skills' => [
                    ['Sentence Types', 'Statements, questions, commands and exclamations.', 'sentence-types', false],
                    ['Story Planning', 'Plan a beginning, middle and ending.', 'story-planning', false],
                ],
            ],
        );

        $this->course(
            $science,
            'Year 3 Science Explorer',
            'year-3-science-explorer',
            'Plants, forces, materials and observation skills with worksheets.',
            [
                'Plants' => [
                    ['Parts of a Plant', 'Roots, stems, leaves and flowers.', 'parts-of-a-plant', true],
                    ['What Plants Need', 'Light, water, air and suitable temperature.', 'plants-need', false],
                ],
                'Forces' => [
                    ['Pushes and Pulls', 'Everyday forces and motion.', 'pushes-pulls', false],
                    ['Simple Experiments', 'Record observations from a fair test.', 'science-experiments', false],
                ],
            ],
        );

        $this->course(
            $year4Maths,
            'Year 4 Mathematics Booster',
            'year-4-mathematics-booster',
            'Place value, decimals and problem-solving for Year 4 pupils.',
            [
                'Place Value' => [
                    ['Thousands and Rounding', 'Understand four-digit numbers and rounding.', 'thousands-rounding', true],
                    ['Number Lines', 'Estimate and position numbers accurately.', 'number-lines', false],
                ],
                'Decimals' => [
                    ['Tenths and Hundredths', 'Represent decimals using place value.', 'tenths-hundredths', false],
                    ['Money Problems', 'Apply decimals to pounds and pence.', 'money-problems', false],
                ],
            ],
        );
    }

    private function classYear(string $name, string $slug, int $order): ClassYear
    {
        return ClassYear::updateOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'sort_order' => $order, 'is_active' => true],
        );
    }

    private function subject(ClassYear $classYear, string $name, string $slug, string $color, int $order): Subject
    {
        return Subject::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'class_year_id' => $classYear->id,
                'color' => $color,
                'sort_order' => $order,
                'is_active' => true,
            ],
        );
    }

    private function createPlan(string $name, Subject $subject, string $cycle, float $price, ?int $trialDays = null): void
    {
        $plan = SubscriptionPlan::updateOrCreate(
            ['name' => $name],
            [
                'subject_id' => $subject->id,
                'billing_cycle' => $cycle,
                'price' => $price,
                'trial_days' => $trialDays,
                'is_bundle' => false,
                'is_active' => true,
            ],
        );
        $plan->subjects()->syncWithoutDetaching([$subject->id]);
    }

    private function course(Subject $subject, string $title, string $slug, string $description, array $modules): Course
    {
        $course = Course::updateOrCreate(
            ['slug' => $slug],
            [
                'subject_id' => $subject->id,
                'title' => $title,
                'description' => $description,
                'thumbnail' => '/images/tuition-hero.png',
                'is_published' => true,
            ],
        );

        $moduleOrder = 1;
        foreach ($modules as $moduleTitle => $lessons) {
            $module = Module::updateOrCreate(
                ['course_id' => $course->id, 'title' => $moduleTitle],
                ['description' => $moduleTitle.' topic lessons and practice.', 'sort_order' => $moduleOrder],
            );

            foreach ($lessons as $index => [$lessonTitle, $notes, $videoRef, $isFree]) {
                $lesson = $this->lesson($module, $lessonTitle, $notes, $index + 1, $videoRef, $isFree);
                $lesson->worksheets()->updateOrCreate(
                    ['title' => $lessonTitle.' Worksheet'],
                    ['file_url' => '/storage/worksheets/'.str($videoRef)->slug().'.pdf', 'sort_order' => 1],
                );

                if ($index === 0) {
                    $this->quiz($lesson, $moduleTitle.' Quick Check');
                }

                if ($index === 1) {
                    Assignment::updateOrCreate(
                        ['lesson_id' => $lesson->id, 'title' => $moduleTitle.' Homework'],
                        [
                            'description' => 'Complete the worksheet and upload a clear scan or PDF.',
                            'max_score' => 100,
                            'allow_resubmission' => true,
                            'deadline' => now()->addDays(7 + $moduleOrder),
                            'is_published' => true,
                        ],
                    );
                }
            }

            $moduleOrder++;
        }

        return $course;
    }

    private function lesson(Module $module, string $title, string $notes, int $order, string $videoRef, bool $isFree): Lesson
    {
        return Lesson::updateOrCreate(
            ['module_id' => $module->id, 'title' => $title],
            [
                'notes' => $notes,
                'sort_order' => $order,
                'video_provider' => 'mux',
                'video_id' => $videoRef,
                'video_thumbnail' => '/images/tuition-hero.png',
                'duration_seconds' => 420 + ($order * 55),
                'is_published' => true,
                'is_free' => $isFree,
            ],
        );
    }

    private function quiz(Lesson $lesson, string $title): void
    {
        $quiz = Quiz::updateOrCreate(
            ['lesson_id' => $lesson->id, 'title' => $title],
            [
                'type' => Quiz::TYPE_GRADED,
                'time_limit_minutes' => 10,
                'max_score' => 100,
                'shuffle_questions' => true,
                'is_published' => true,
            ],
        );

        Question::updateOrCreate(
            ['quiz_id' => $quiz->id, 'text' => 'Choose the best answer for this lesson topic.'],
            [
                'type' => Question::TYPE_MCQ,
                'options' => ['Answer A', 'Answer B', 'Answer C', 'Answer D'],
                'correct_answer' => '0',
                'points' => 5,
                'bank_tag' => str($lesson->title)->slug(),
                'sort_order' => 1,
            ],
        );

        Question::updateOrCreate(
            ['quiz_id' => $quiz->id, 'text' => 'This lesson includes practice work.'],
            [
                'type' => Question::TYPE_TRUE_FALSE,
                'options' => ['True', 'False'],
                'correct_answer' => '0',
                'points' => 5,
                'bank_tag' => str($lesson->title)->slug(),
                'sort_order' => 2,
            ],
        );
    }
}
