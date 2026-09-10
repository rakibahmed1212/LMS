<?php

use App\Http\Controllers\AdminAuditLogController;
use App\Http\Controllers\AdminContentController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminPlatformController;
use App\Http\Controllers\AdminStudentController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CertificateVerificationController;
use App\Http\Controllers\CoursePortalController;
use App\Http\Controllers\LessonPortalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ParentDashboardController;
use App\Http\Controllers\ParentStudentController;
use App\Http\Controllers\PaymentReceiptController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicPageController;
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
Route::get('/about', [PublicPageController::class, 'about'])->name('about');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('contact');
Route::get('/faq', [PublicPageController::class, 'faq'])->name('faq');
Route::get('/privacy', [PublicPageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PublicPageController::class, 'terms'])->name('terms');
Route::get('/courses', [CoursePortalController::class, 'index'])
    ->name('courses.index');
Route::get('/courses/{course:slug}', [CoursePortalController::class, 'show'])
    ->name('courses.show');
Route::get('/lessons/{lesson}', [LessonPortalController::class, 'show'])
    ->name('lessons.show');

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
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/payments/{payment}/receipt', [PaymentReceiptController::class, 'show'])->name('payments.receipt');

    Route::get('/students/{student}/subscribe', [SubscribeController::class, 'show'])
        ->name('subscriptions.show');
    Route::post('/students/{student}/subscribe', [SubscribeController::class, 'store'])
        ->name('subscriptions.store');

    Route::post('/lessons/{lesson}/progress', [LessonPortalController::class, 'progress'])
        ->name('lessons.progress');
    Route::post('/lessons/{lesson}/complete', [LessonPortalController::class, 'complete'])
        ->name('lessons.complete');
    Route::post('/lessons/{lesson}/questions', [LessonPortalController::class, 'question'])
        ->name('lessons.questions.store');

    Route::get('/admin', [AdminDashboardController::class, 'index'])
        ->middleware('can:manage subscriptions')
        ->name('admin.dashboard');
    Route::get('/admin/users', [AdminUserController::class, 'index'])
        ->middleware('can:manage staff')
        ->name('admin.users.index');
    Route::post('/admin/users', [AdminUserController::class, 'store'])
        ->middleware('can:manage staff')
        ->name('admin.users.store');
    Route::patch('/admin/users/{user}', [AdminUserController::class, 'update'])
        ->middleware('can:manage staff')
        ->name('admin.users.update');
    Route::get('/admin/students', [AdminStudentController::class, 'index'])
        ->middleware('can:manage subscriptions')
        ->name('admin.students.index');
    Route::get('/admin/content', [AdminContentController::class, 'index'])
        ->middleware('can:manage content')
        ->name('admin.content.index');
    Route::post('/admin/content/lessons', [AdminContentController::class, 'storeLesson'])
        ->middleware('can:create lesson')
        ->name('admin.content.lessons.store');
    Route::get('/admin/audit-logs', [AdminAuditLogController::class, 'index'])
        ->middleware('can:view audit logs')
        ->name('admin.audit-logs.index');
    Route::get('/admin/platform', [AdminPlatformController::class, 'index'])
        ->middleware('can:view audit logs')
        ->name('admin.platform.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
