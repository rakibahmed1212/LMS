<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('cert_code', 40)->unique(); // e.g. CERT-STU-2026-00125-Y3M
            $table->string('template', 60)->default('default');
            $table->string('pdf_url')->nullable();
            $table->string('qr_url')->nullable();
            $table->timestamp('issued_at');
            $table->timestamps();
            $table->unique(['student_id', 'course_id']);
        });

        // Scopes content ownership for Tutor/Content Manager roles.
        Schema::create('tutor_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['tutor_id', 'course_id']);
        });

        // Optional scope: a tutor only sees explicitly assigned students.
        Schema::create('tutor_student', function (Blueprint $table) {
            $table->foreignId('tutor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->primary(['tutor_id', 'student_id']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 80)->index();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('channel', 30)->default('in_app');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('tutor_student');
        Schema::dropIfExists('tutor_assignments');
        Schema::dropIfExists('certificates');
    }
};
