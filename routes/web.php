<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\Auth\CoordinatorAuthController;
use App\Http\Controllers\Auth\UnifiedAuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\OjtCompletionController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentVerificationController;
use App\Http\Controllers\StudentSettingsController;
use App\Http\Controllers\CoordinatorSettingsController;

// -------------------- Unified login (one form for both roles) --------------------
Route::get('/login', [UnifiedAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UnifiedAuthController::class, 'login'])->middleware('throttle:5,1');

// -------------------- Coordinator Routes --------------------
Route::prefix('coordinator')->group(function () {
    Route::middleware('guest:coordinator')->group(function () {
        Route::get('login', [CoordinatorAuthController::class, 'showLoginForm'])->name('coordinator.login');
        Route::post('login', [CoordinatorAuthController::class, 'login'])->name('coordinator.login.submit')->middleware('throttle:5,1');
        Route::get('register', [CoordinatorAuthController::class, 'showRegisterForm'])->name('coordinator.register');
        Route::post('register', [CoordinatorAuthController::class, 'register'])->name('coordinator.register.submit')->middleware('throttle:5,1');
    });

    Route::middleware(['auth:coordinator', 'single.session'])->group(function () {
        Route::get('dashboard', [CoordinatorAuthController::class, 'dashboard'])->name('coordinator.dashboard');
        Route::get('absent-today', [CoordinatorAuthController::class, 'absentToday'])->name('coordinator.absent.today');
        Route::get('pending-verification', [StudentVerificationController::class, 'index'])->name('coordinator.pending.verification');
        Route::post('pending-verification/verify/{student}', [StudentVerificationController::class, 'verify'])->name('coordinator.pending.verification.verify');
        Route::post('pending-verification/reject/{student}', [StudentVerificationController::class, 'reject'])->name('coordinator.pending.verification.reject');
        Route::get('attendance-logs', [AttendanceController::class, 'coordinatorLogs'])->name('coordinator.attendance.logs');
        Route::get('attendance/{attendance}/verification-snapshot/{type}', [AttendanceController::class, 'viewVerificationSnapshot'])->where('type', 'morning|afternoon|timeout')->name('coordinator.attendance.verification_snapshot');
        Route::get('attendance-analytics', [AttendanceController::class, 'attendanceAnalytics'])->name('coordinator.attendance.analytics');
        Route::get('generate-report', [ReportController::class, 'showReportForm'])->name('coordinator.generate.report');
        Route::post('generate-report', [ReportController::class, 'generateMonthlyReport'])->name('coordinator.generate.report.submit');
        Route::get('ojt-completion', [OjtCompletionController::class, 'index'])->name('coordinator.ojt.completion');
        Route::post('ojt-completion/confirm/{student}', [OjtCompletionController::class, 'confirm'])->name('coordinator.ojt.completion.confirm');
        Route::post('ojt-completion/required-hours/{student}', [OjtCompletionController::class, 'updateRequiredHours'])->name('coordinator.ojt.completion.required-hours');
        Route::get('ojt-completion/certificate/{student}', [OjtCompletionController::class, 'downloadCertificate'])->name('coordinator.ojt.completion.certificate');
        Route::post('student/{student}/set-password', [OjtCompletionController::class, 'setStudentPassword'])->name('coordinator.student.set-password');
        Route::get('settings', [CoordinatorSettingsController::class, 'index'])->name('coordinator.settings');
        Route::post('settings/password', [CoordinatorSettingsController::class, 'updatePassword'])->name('coordinator.settings.password')->middleware('throttle:5,1');
        Route::post('logout', [CoordinatorAuthController::class, 'logout'])->name('coordinator.logout');
    });
});

// -------------------- Student Routes --------------------
Route::prefix('student')->group(function () {
    Route::middleware('guest:student')->group(function () {
        Route::get('login', [StudentAuthController::class, 'showLoginForm'])->name('student.login');
        Route::post('login', [StudentAuthController::class, 'login'])->name('student.login.submit')->middleware('throttle:5,1');
        Route::get('password/request', function () {
            return view('auth.student-forgot-password');
        })->name('student.password.request');
        Route::get('register', [StudentAuthController::class, 'showRegisterForm'])->name('student.register');
        Route::post('register', [StudentAuthController::class, 'register'])->name('student.register.submit')->middleware('throttle:5,1');
    });

    Route::middleware(['auth:student', 'single.session'])->group(function () {
        Route::get('dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');

        Route::post('time-in', [AttendanceController::class, 'timeIn'])->name('student.timein');
        Route::post('time-out', [AttendanceController::class, 'timeOut'])->name('student.timeout');
        Route::post('verify-face', [AttendanceController::class, 'verifyFaceEncoding'])->name('student.verify.face');
        Route::get('recent-logs', [AttendanceController::class, 'recentLogs'])->name('student.recentlogs');
        Route::get('attendance/{attendance}/verification-snapshot/{type}', [AttendanceController::class, 'viewVerificationSnapshot'])->where('type', 'morning|afternoon|timeout')->name('student.attendance.verification_snapshot');
        Route::get('settings', [StudentSettingsController::class, 'index'])->name('student.settings');
        Route::get('password/change', fn () => redirect()->route('student.settings'))->name('student.password.change');
        Route::post('password/change', [StudentAuthController::class, 'changePassword'])->name('student.password.change.submit')->middleware('throttle:5,1');
        Route::post('logout', [StudentAuthController::class, 'logout'])->name('student.logout');
    });
});

// -------------------- Home Route --------------------
Route::get('/', [UnifiedAuthController::class, 'showLoginForm'])->name('login.selector');

// -------------------- Logout Functionality -------------------
