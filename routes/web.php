<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CertificateVerificationController;
use App\Http\Controllers\CoursePortalController;
use App\Http\Controllers\ParentDashboardController;
use App\Http\Controllers\ParentStudentController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscribeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/certificates/verify/{code}', [CertificateVerificationController::class, 'show'])
    ->name('certificates.verify');

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user?->hasRole('super_admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user?->hasRole(['tutor', 'content_manager'])) {
        return redirect()->route('plans.index');
    }

    return redirect()->route('parent.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/parent', [ParentDashboardController::class, 'index'])
        ->name('parent.dashboard');
    Route::post('/parent/students', [ParentStudentController::class, 'store'])
        ->middleware('can:manage students')
        ->name('parent.students.store');

    Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');

    Route::get('/courses', [CoursePortalController::class, 'index'])
        ->name('courses.index');
    Route::get('/courses/{course:slug}', [CoursePortalController::class, 'show'])
        ->name('courses.show');

    Route::get('/students/{student}/subscribe', [SubscribeController::class, 'show'])
        ->name('subscriptions.show');
    Route::post('/students/{student}/subscribe', [SubscribeController::class, 'store'])
        ->name('subscriptions.store');

    Route::get('/admin', [AdminDashboardController::class, 'index'])
        ->middleware('can:manage subscriptions')
        ->name('admin.dashboard');
    Route::get('/admin/users', [AdminUserController::class, 'index'])
        ->middleware('can:manage staff')
        ->name('admin.users.index');
    Route::patch('/admin/users/{user}', [AdminUserController::class, 'update'])
        ->middleware('can:manage staff')
        ->name('admin.users.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
