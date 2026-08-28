<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "2025-26"
            $table->date('starts_at');
            $table->date('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Reuse the class-year structure template per academic session.
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->unique(['student_id', 'class_year_id', 'academic_session_id'], 'enrollment_unique');
        });

        Schema::create('progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('watch_percent')->default(0);
            $table->unsignedInteger('watched_seconds')->default(0);
            $table->unsignedInteger('last_position_seconds')->default(0);
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_watched_at')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'lesson_id']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 60)->index(); // lesson_viewed, video_watched, quiz_taken...
            $table->unsignedInteger('seconds_spent')->default(0);
            $table->timestamp('occurred_at')->index();
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('attempt_no')->default(1);
            $table->enum('status', ['in_progress', 'submitted'])->default('in_progress');
            $table->decimal('score', 6, 2)->nullable();
            $table->decimal('max_score', 6, 2)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->json('answers')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'quiz_id']);
        });

        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('deadline')->nullable();
            $table->decimal('max_score', 6, 2)->default(100);
            $table->boolean('allow_resubmission')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->text('comment')->nullable();
            $table->decimal('score', 6, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->enum('status', ['submitted', 'graded', 'returned'])->default('submitted');
            $table->boolean('is_late')->default(false);
            $table->timestamp('submitted_at');
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();
            $table->index(['assignment_id', 'student_id']);
        });

        // Weighted per-subject grade summary (quiz weight + assignment weight).
        Schema::create('gradebooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->decimal('quiz_weight', 5, 2)->default(60);
            $table->decimal('assignment_weight', 5, 2)->default(40);
            $table->decimal('quiz_avg', 6, 2)->nullable();
            $table->decimal('assignment_avg', 6, 2)->nullable();
            $table->decimal('final_score', 6, 2)->nullable();
            $table->string('term', 40)->default('all'); // pgsql: unique + nullable term is not allowed
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'subject_id', 'term']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gradebooks');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('progress');
        Schema::dropIfExists('student_enrollments');
        Schema::dropIfExists('academic_sessions');
    }
};
