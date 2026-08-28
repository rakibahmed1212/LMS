<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('locale', 10)->default('en')->after('phone');
            $table->boolean('is_active')->default(true)->after('locale');
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->string('student_code', 40)->unique();
            $table->string('name');
            $table->date('dob')->nullable();
            $table->string('school')->nullable();
            $table->string('gender', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'locale', 'is_active']);
        });
    }
};
