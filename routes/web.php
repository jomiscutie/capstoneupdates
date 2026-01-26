<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\Auth\CoordinatorAuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ReportController;

// -------------------- Generic login route --------------------
Route::get('/login', function () {
    return redirect()->route('student.login');
})->name('login');

// -------------------- Coordinator Routes --------------------
Route::prefix('coordinator')->group(function () {
    Route::middleware('guest:coordinator')->group(function () {
        Route::get('login', [CoordinatorAuthController::class, 'showLoginForm'])->name('coordinator.login');
        Route::post('login', [CoordinatorAuthController::class, 'login'])->name('coordinator.login.submit');
        Route::get('register', [CoordinatorAuthController::class, 'showRegisterForm'])->name('coordinator.register');
        Route::post('register', [CoordinatorAuthController::class, 'register'])->name('coordinator.register.submit');
    });

    Route::middleware('auth:coordinator')->group(function () {
        Route::get('dashboard', [CoordinatorAuthController::class, 'dashboard'])->name('coordinator.dashboard');
        Route::get('attendance-logs', [AttendanceController::class, 'coordinatorLogs'])->name('coordinator.attendance.logs');
        Route::get('generate-report', [ReportController::class, 'showReportForm'])->name('coordinator.generate.report');
        Route::post('generate-report', [ReportController::class, 'generateMonthlyReport'])->name('coordinator.generate.report.submit');
        Route::post('logout', [CoordinatorAuthController::class, 'logout'])->name('coordinator.logout');
    });
});

// -------------------- Student Routes --------------------
Route::prefix('student')->group(function () {
    Route::middleware('guest:student')->group(function () {
        Route::get('login', [StudentAuthController::class, 'showLoginForm'])->name('student.login');
        Route::post('login', [StudentAuthController::class, 'login'])->name('student.login.submit');
        Route::get('register', [StudentAuthController::class, 'showRegisterForm'])->name('student.register');
        Route::post('register', [StudentAuthController::class, 'register'])->name('student.register.submit');
    });

    Route::middleware('auth:student')->group(function () {
        Route::get('dashboard', function () {
            return view('student.dashboard');
        })->name('student.dashboard');

        Route::post('time-in', [AttendanceController::class, 'timeIn'])->name('student.timein');
        Route::post('time-out', [AttendanceController::class, 'timeOut'])->name('student.timeout');
        Route::post('verify-face', [AttendanceController::class, 'verifyFaceEncoding'])->name('student.verify.face');
        Route::get('recent-logs', [AttendanceController::class, 'recentLogs'])->name('student.recentlogs');
        Route::post('logout', [StudentAuthController::class, 'logout'])->name('student.logout');
    });
});

// -------------------- Home Route --------------------
Route::get('/', function () {
    Auth::guard('student')->logout();
    Auth::guard('coordinator')->logout();
    session()->invalidate();
    session()->regenerateToken();
    return view('auth.select-login');
})->name('login.selector');

// -------------------- Logout Functionality -------------------
