<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discussion_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // tutor/admin replies
            $table->unsignedBigInteger('parent_thread_id')->nullable();
            $table->text('message');
            $table->timestamps();
            $table->foreign('parent_thread_id')->references('id')->on('discussion_threads')->nullOnDelete();
            $table->index(['lesson_id']);
        });

        Schema::create('live_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tutor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('provider', 30)->default('jitsi'); // zoom|google_meet|jitsi
            $table->string('meeting_url');
            $table->timestamp('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_classes');
        Schema::dropIfExists('discussion_threads');
    }
};
