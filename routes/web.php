<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminManagementController;
use App\Http\Controllers\AdminOversightController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\CoordinatorAuthController;
use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\Auth\UnifiedAuthController;
use App\Http\Controllers\CoordinatorSettingsController;
use App\Http\Controllers\OjtCompletionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentSettingsController;
use App\Http\Controllers\StudentVerificationController;
use Illuminate\Support\Facades\Route;

// -------------------- Unified login (one form for both roles) --------------------
Route::get('/login', [UnifiedAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UnifiedAuthController::class, 'login'])->middleware('throttle:5,1');

// -------------------- Coordinator Routes --------------------
Route::prefix('admin')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', fn () => redirect()->route('login'))->name('admin.login');
    });

    Route::middleware(['auth:admin', 'single.session'])->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('coordinators', [AdminManagementController::class, 'coordinators'])->name('admin.coordinators');
        Route::post('coordinators', [AdminManagementController::class, 'storeCoordinator'])->name('admin.coordinators.store');
        Route::post('coordinators/{coordinator}/toggle', [AdminManagementController::class, 'toggleCoordinator'])->name('admin.coordinators.toggle');
        Route::delete('coordinators/{coordinator}', [AdminManagementController::class, 'destroyCoordinator'])->name('admin.coordinators.destroy');
        Route::post('coordinators/{coordinator}/password', [AdminManagementController::class, 'updateCoordinatorPassword'])->name('admin.coordinators.password')->middleware('throttle:5,1');
        Route::post('coordinators/{coordinator}/assignments', [AdminManagementController::class, 'addCoordinatorAssignment'])->name('admin.coordinators.assignments.store');
        Route::post('coordinators/assignments/{assignment}', [AdminManagementController::class, 'updateCoordinatorAssignment'])->name('admin.coordinators.assignments.update');
        Route::post('coordinators/assignments/{assignment}/remove', [AdminManagementController::class, 'removeCoordinatorAssignment'])->name('admin.coordinators.assignments.remove');
        Route::get('options', [AdminManagementController::class, 'options'])->name('admin.options');
        Route::post('options/programs', [AdminManagementController::class, 'storeProgramOption'])->name('admin.options.programs.store');
        Route::post('options/sections', [AdminManagementController::class, 'storeSectionOption'])->name('admin.options.sections.store');
        Route::post('options/{option}/deactivate', [AdminManagementController::class, 'deactivateOption'])->name('admin.options.deactivate');
        Route::get('students', [AdminManagementController::class, 'students'])->name('admin.students');
        Route::get('office-requests', [AdminManagementController::class, 'officeRequests'])->name('admin.office-requests');
        Route::get('students/archived', [AdminManagementController::class, 'archivedStudents'])->name('admin.students.archived');
        Route::post('students/restore/{id}', [AdminManagementController::class, 'restoreStudent'])->name('admin.students.restore')->middleware('throttle:30,1');
        Route::post('students/archived/{id}/remove', [AdminManagementController::class, 'forceRemoveArchivedStudent'])->name('admin.students.archived.remove')->middleware('throttle:20,1');
        Route::delete('students/{student}', [AdminManagementController::class, 'destroyStudent'])->name('admin.students.destroy')->middleware('throttle:30,1');
        Route::post('students/delete-batch', [AdminManagementController::class, 'bulkDestroyStudents'])->name('admin.students.delete-batch')->middleware('throttle:10,1');
        Route::get('invalidations', [AdminOversightController::class, 'invalidations'])->name('admin.invalidations');
        Route::post('invalidations/{attendance}/review', [AdminOversightController::class, 'reviewInvalidation'])->name('admin.invalidations.review');
        Route::post('invalidations/{attendance}/restore', [AdminOversightController::class, 'restoreAttendance'])->name('admin.invalidations.restore');
        Route::get('manual-attendance-requests', [AdminOversightController::class, 'manualAttendanceRequests'])->name('admin.manual.requests');
        Route::post('manual-attendance-requests/{manualRequest}/review', [AdminOversightController::class, 'reviewManualAttendanceRequest'])->name('admin.manual.requests.review');
        Route::post('manual-attendance-requests/bulk-review', [AdminOversightController::class, 'bulkReviewManualAttendanceRequests'])->name('admin.manual.requests.bulk.review');
        Route::get('face-enrollment', [AdminOversightController::class, 'faceEnrollment'])->name('admin.face_enrollment');
        Route::get('audit-logs', [AdminOversightController::class, 'auditLogs'])->name('admin.audit_logs');
        Route::get('session-monitor', [AdminOversightController::class, 'sessions'])->name('admin.sessions');
        Route::post('session-monitor/force-logout', [AdminOversightController::class, 'forceLogout'])->name('admin.sessions.force_logout');
        Route::post('students/terms/batch', [AdminManagementController::class, 'batchStoreStudentTermAssignments'])->name('admin.students.terms.batch');
        Route::post('students/{student}/terms', [AdminManagementController::class, 'storeStudentTermAssignment'])->name('admin.students.terms.store');
        Route::post('students/terms/{assignment}/complete', [AdminManagementController::class, 'completeStudentTermAssignment'])->name('admin.students.terms.complete');
        Route::post('office-requests/{officeRequest}/review', [AdminManagementController::class, 'reviewOfficeAssignmentRequest'])->name('admin.office-requests.review');
        Route::get('settings', [AdminManagementController::class, 'settings'])->name('admin.settings');
        Route::post('settings/password', [AdminManagementController::class, 'updatePassword'])->name('admin.settings.password')->middleware('throttle:5,1');
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    });
});

// -------------------- Coordinator Routes --------------------
Route::prefix('coordinator')->group(function () {
    Route::middleware('guest:coordinator')->group(function () {
        Route::get('login', fn () => redirect()->route('login'))->name('coordinator.login');
        Route::post('login', [CoordinatorAuthController::class, 'login'])->name('coordinator.login.submit')->middleware('throttle:5,1');
        Route::get('register', [CoordinatorAuthController::class, 'showRegisterForm'])->name('coordinator.register');
        Route::post('register', [CoordinatorAuthController::class, 'register'])->name('coordinator.register.submit');
    });

    Route::middleware(['auth:coordinator', 'single.session'])->group(function () {
        Route::get('dashboard', [CoordinatorAuthController::class, 'dashboard'])->name('coordinator.dashboard');
        Route::get('absent-today', [CoordinatorAuthController::class, 'absentToday'])->name('coordinator.absent.today');
        Route::get('pending-verification', [StudentVerificationController::class, 'index'])->name('coordinator.pending.verification');
        Route::post('pending-verification/verify/{student}', [StudentVerificationController::class, 'verify'])->name('coordinator.pending.verification.verify');
        Route::post('pending-verification/reject/{student}', [StudentVerificationController::class, 'reject'])->name('coordinator.pending.verification.reject');
        Route::get('attendance-logs', [AttendanceController::class, 'coordinatorLogs'])->name('coordinator.attendance.logs');
        Route::get('manual-attendance-requests', [AttendanceController::class, 'coordinatorManualRequests'])->name('coordinator.manual.requests');
        Route::post('manual-attendance-requests/{manualRequest}/review', [AttendanceController::class, 'reviewManualRequest'])->name('coordinator.manual.requests.review');
        Route::post('manual-attendance-requests/bulk-review', [AttendanceController::class, 'coordinatorBulkReviewManualRequests'])->name('coordinator.manual.requests.bulk.review');
        Route::post('attendance/{attendance}/invalidate', [AttendanceController::class, 'invalidateAttendance'])->name('coordinator.attendance.invalidate');
        Route::get('attendance/{attendance}/verification-snapshot/{type}', [AttendanceController::class, 'viewVerificationSnapshot'])->where('type', 'morning|afternoon|timeout')->name('coordinator.attendance.verification_snapshot');
        Route::get('attendance-analytics', [AttendanceController::class, 'attendanceAnalytics'])->name('coordinator.attendance.analytics');
        Route::get('generate-report', [ReportController::class, 'showReportForm'])->name('coordinator.generate.report');
        Route::post('generate-report', [ReportController::class, 'generateMonthlyReport'])->name('coordinator.generate.report.submit');
        Route::post('generate-report/batch', [ReportController::class, 'generateBatchMonthlyReports'])->name('coordinator.generate.report.batch')->middleware('throttle:12,1');
        Route::get('students', [CoordinatorSettingsController::class, 'students'])->name('coordinator.students');
        Route::get('ojt-completion', [OjtCompletionController::class, 'index'])->name('coordinator.ojt.completion');
        Route::post('ojt-completion/confirm/{student}', [OjtCompletionController::class, 'confirm'])->name('coordinator.ojt.completion.confirm');
        Route::post('ojt-completion/required-hours/{student}', [OjtCompletionController::class, 'updateRequiredHours'])->name('coordinator.ojt.completion.required-hours');
        Route::get('ojt-completion/certificate/{student}', [OjtCompletionController::class, 'downloadCertificate'])->name('coordinator.ojt.completion.certificate');
        Route::post('student/{student}/set-password', [OjtCompletionController::class, 'setStudentPassword'])->name('coordinator.student.set-password');
        Route::get('settings', [CoordinatorSettingsController::class, 'index'])->name('coordinator.settings');
        Route::post('settings/password', [CoordinatorSettingsController::class, 'updatePassword'])->name('coordinator.settings.password')->middleware('throttle:5,1');
        Route::post('settings/students/required-hours/bulk', [CoordinatorSettingsController::class, 'bulkUpdateRequiredHours'])->name('coordinator.settings.required-hours.bulk');
        Route::delete('settings/students/{student}', [CoordinatorSettingsController::class, 'destroyStudent'])->name('coordinator.settings.students.destroy')->middleware('throttle:30,1');
        Route::post('settings/students/delete-batch', [CoordinatorSettingsController::class, 'bulkDestroyStudents'])->name('coordinator.settings.students.delete-batch')->middleware('throttle:10,1');
        Route::post('logout', [CoordinatorAuthController::class, 'logout'])->name('coordinator.logout');
    });
});

// -------------------- Kiosk Routes (attendance capture only) --------------------
Route::prefix('kiosk')
    ->middleware(['kiosk.access', 'throttle:90,1'])
    ->group(function () {
        Route::get('/', [AttendanceController::class, 'kioskIndex'])->name('kiosk.index');
        Route::post('identify', [AttendanceController::class, 'kioskIdentify'])->name('kiosk.identify');
        Route::post('time-in', [AttendanceController::class, 'kioskTimeIn'])->name('kiosk.timein');
        Route::post('lunch-break-out', [AttendanceController::class, 'kioskLunchBreakOut'])->name('kiosk.lunch.breakout');
        Route::post('time-out', [AttendanceController::class, 'kioskTimeOut'])->name('kiosk.timeout');
    });

// -------------------- Student Routes --------------------
Route::prefix('student')->group(function () {
    Route::middleware('guest:student')->group(function () {
        Route::get('login', fn () => redirect()->route('login'))->name('student.login');
        Route::post('login', [StudentAuthController::class, 'login'])->name('student.login.submit')->middleware('throttle:5,1');
        Route::get('password/request', function () {
            return view('auth.student-forgot-password');
        })->name('student.password.request');
        Route::get('register', [StudentAuthController::class, 'showRegisterForm'])->name('student.register');
        Route::post('register', [StudentAuthController::class, 'register'])->name('student.register.submit')->middleware('throttle:5,1');
    });

    Route::middleware(['auth:student', 'single.session'])->group(function () {
        Route::get('dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
        Route::post('manual-attendance-request', [AttendanceController::class, 'submitManualRequest'])->name('student.manual.request')->middleware('throttle:10,1');
        Route::get('recent-logs', [AttendanceController::class, 'recentLogs'])->name('student.recentlogs');
        Route::get('recent-logs/download', [AttendanceController::class, 'downloadRecentLogs'])->name('student.recentlogs.download');
        Route::get('attendance/{attendance}/verification-snapshot/{type}', [AttendanceController::class, 'viewVerificationSnapshot'])->where('type', 'morning|afternoon|timeout')->name('student.attendance.verification_snapshot');
        Route::get('settings', [StudentSettingsController::class, 'index'])->name('student.settings');
        Route::post('settings/face-enrollment', [StudentSettingsController::class, 'saveFaceEnrollment'])->name('student.settings.face-enrollment');
        Route::post('settings/office-request', [StudentSettingsController::class, 'submitOfficeAssignmentRequest'])->name('student.settings.office-request');
        Route::get('password/change', fn () => redirect()->route('student.settings'))->name('student.password.change');
        Route::post('password/change', [StudentAuthController::class, 'changePassword'])->name('student.password.change.submit')->middleware('throttle:5,1');
        Route::post('logout', [StudentAuthController::class, 'logout'])->name('student.logout');
    });
});

// -------------------- Home Route --------------------
Route::get('/', [UnifiedAuthController::class, 'showLoginForm'])->name('login.selector');
