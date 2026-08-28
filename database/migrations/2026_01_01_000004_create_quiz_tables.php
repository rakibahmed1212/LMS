<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['practice', 'graded', 'end_of_topic', 'end_of_year'])
                ->default('practice');
            $table->unsignedSmallInteger('time_limit_minutes')->nullable();
            $table->unsignedInteger('max_score')->default(100);
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('is_published')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['mcq', 'true_false', 'short_answer'])->default('mcq');
            $table->text('text');
            $table->json('options')->nullable();          // ["A", "B", "C", "D"] or ["True", "False"]
            $table->text('correct_answer')->nullable();   // index/string/expected keyword
            $table->unsignedInteger('points')->default(1);
            $table->string('bank_tag')->nullable();       // e.g. "fractions" for question bank search
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
        Schema::dropIfExists('quizzes');
    }
};
