<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\DisciplinaryActionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ViolationTypeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\StaffAdminController;

/*
|--------------------------------------------------------------------------
| Public Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect('/login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendTemporaryPassword'])->name('password.email');

/*
|--------------------------------------------------------------------------
| Student Portal Routes (Protected by student.auth)
|--------------------------------------------------------------------------
*/
Route::middleware(['student.auth'])->group(function () {
    Route::get('/student/dashboard', [StudentDashboardController::class, 'index']);
    Route::post('/student/pod-slips', [StudentDashboardController::class, 'storeSlip']);
    Route::post('/student/profile/update', [StudentDashboardController::class, 'updateProfile']);
    Route::post('/student/notifications/read', [StudentDashboardController::class, 'markNotificationsRead']);
});

/*
|--------------------------------------------------------------------------
| Admin / Prefect of Discipline Routes (Protected by admin.auth)
|--------------------------------------------------------------------------
*/
Route::middleware(['admin.auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/send', [NotificationController::class, 'sendDirectMessage'])->name('notifications.send');

    // Disciplinary Actions & POD Slips
    Route::get('/disciplinary-actions', [DisciplinaryActionController::class, 'index']);
    Route::post('/disciplinary-actions', [DisciplinaryActionController::class, 'store']);
    Route::patch('/disciplinary-actions/{id}/resolve', [DisciplinaryActionController::class, 'resolve']);
    Route::patch('/disciplinary-actions/{id}/toggle-status', [DisciplinaryActionController::class, 'toggleStatus']);
    Route::post('/pod-slips/{id}/approve', [DisciplinaryActionController::class, 'approveSlip']);
    Route::post('/pod-slips/{id}/reject', [DisciplinaryActionController::class, 'rejectSlip']);

    // Students & Courses Management
    Route::get('/students', [StudentController::class, 'index']);
    Route::post('/students', [StudentController::class, 'store']);
    Route::put('/students/{id}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{id}', [StudentController::class, 'destroy']);
    Route::post('/courses', [StudentController::class, 'storeCourse']);
    Route::delete('/courses/{id}', [StudentController::class, 'destroyCourse']);

    // Subjects Management
    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::post('/subjects', [SubjectController::class, 'store']);
    Route::put('/subjects/{id}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{id}', [SubjectController::class, 'destroy']);

    // Violation Types Management
    Route::get('/violation-types', [ViolationTypeController::class, 'index']);
    Route::post('/violation-types', [ViolationTypeController::class, 'store']);
    Route::put('/violation-types/{id}', [ViolationTypeController::class, 'update'])->name('violation-types.update');
    Route::delete('/violation-types/{id}', [ViolationTypeController::class, 'destroy']);

    // Activity Log
    Route::get('/activity-log', [ActivityLogController::class, 'index']);

    // Staff Admin Management
    Route::get('/staff-admins', [StaffAdminController::class, 'index']);
    Route::post('/staff-admins', [StaffAdminController::class, 'store']);
    Route::put('/staff-admins/{id}', [StaffAdminController::class, 'update']);
    Route::delete('/staff-admins/{id}', [StaffAdminController::class, 'destroy']);

    Route::fallback(fn () => redirect('/login'));
});