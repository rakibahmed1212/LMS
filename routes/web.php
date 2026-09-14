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
    Route::post('/admin/content/class-years', [AdminContentController::class, 'storeClassYear'])
        ->middleware('can:manage content')
        ->name('admin.content.class-years.store');
    Route::patch('/admin/content/class-years/{classYear}', [AdminContentController::class, 'updateClassYear'])
        ->middleware('can:manage content')
        ->name('admin.content.class-years.update');
    Route::delete('/admin/content/class-years/{classYear}', [AdminContentController::class, 'destroyClassYear'])
        ->middleware('can:manage content')
        ->name('admin.content.class-years.destroy');
    Route::post('/admin/content/subjects', [AdminContentController::class, 'storeSubject'])
        ->middleware('can:manage content')
        ->name('admin.content.subjects.store');
    Route::patch('/admin/content/subjects/{subject}', [AdminContentController::class, 'updateSubject'])
        ->middleware('can:manage content')
        ->name('admin.content.subjects.update');
    Route::delete('/admin/content/subjects/{subject}', [AdminContentController::class, 'destroySubject'])
        ->middleware('can:manage content')
        ->name('admin.content.subjects.destroy');
    Route::post('/admin/content/courses', [AdminContentController::class, 'storeCourse'])
        ->middleware('can:manage content')
        ->name('admin.content.courses.store');
    Route::patch('/admin/content/courses/{course}', [AdminContentController::class, 'updateCourse'])
        ->middleware('can:manage content')
        ->name('admin.content.courses.update');
    Route::delete('/admin/content/courses/{course}', [AdminContentController::class, 'destroyCourse'])
        ->middleware('can:manage content')
        ->name('admin.content.courses.destroy');
    Route::post('/admin/content/modules', [AdminContentController::class, 'storeModule'])
        ->middleware('can:manage content')
        ->name('admin.content.modules.store');
    Route::patch('/admin/content/modules/{module}', [AdminContentController::class, 'updateModule'])
        ->middleware('can:manage content')
        ->name('admin.content.modules.update');
    Route::delete('/admin/content/modules/{module}', [AdminContentController::class, 'destroyModule'])
        ->middleware('can:manage content')
        ->name('admin.content.modules.destroy');
    Route::post('/admin/content/lessons', [AdminContentController::class, 'storeLesson'])
        ->middleware('can:create lesson')
        ->name('admin.content.lessons.store');
    Route::patch('/admin/content/lessons/{lesson}', [AdminContentController::class, 'updateLesson'])
        ->middleware('can:manage content')
        ->name('admin.content.lessons.update');
    Route::delete('/admin/content/lessons/{lesson}', [AdminContentController::class, 'destroyLesson'])
        ->middleware('can:manage content')
        ->name('admin.content.lessons.destroy');
    Route::post('/admin/content/worksheets', [AdminContentController::class, 'storeWorksheet'])
        ->middleware('can:manage content')
        ->name('admin.content.worksheets.store');
    Route::patch('/admin/content/worksheets/{worksheet}', [AdminContentController::class, 'updateWorksheet'])
        ->middleware('can:manage content')
        ->name('admin.content.worksheets.update');
    Route::delete('/admin/content/worksheets/{worksheet}', [AdminContentController::class, 'destroyWorksheet'])
        ->middleware('can:manage content')
        ->name('admin.content.worksheets.destroy');
    Route::post('/admin/content/quizzes', [AdminContentController::class, 'storeQuiz'])
        ->middleware('can:manage content')
        ->name('admin.content.quizzes.store');
    Route::patch('/admin/content/quizzes/{quiz}', [AdminContentController::class, 'updateQuiz'])
        ->middleware('can:manage content')
        ->name('admin.content.quizzes.update');
    Route::delete('/admin/content/quizzes/{quiz}', [AdminContentController::class, 'destroyQuiz'])
        ->middleware('can:manage content')
        ->name('admin.content.quizzes.destroy');
    Route::post('/admin/content/questions', [AdminContentController::class, 'storeQuestion'])
        ->middleware('can:manage content')
        ->name('admin.content.questions.store');
    Route::patch('/admin/content/questions/{question}', [AdminContentController::class, 'updateQuestion'])
        ->middleware('can:manage content')
        ->name('admin.content.questions.update');
    Route::delete('/admin/content/questions/{question}', [AdminContentController::class, 'destroyQuestion'])
        ->middleware('can:manage content')
        ->name('admin.content.questions.destroy');
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
