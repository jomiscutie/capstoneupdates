<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Coordinator;
use App\Models\ManualAttendanceRequest;
use App\Models\Student;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $totalCoordinators = Coordinator::count();
        $activeCoordinators = Coordinator::where('is_active', true)->count();
        $totalStudents = Student::count();
        $verifiedStudents = Student::verified()->count();
        $pendingStudents = Student::pendingVerification()->count();
        $presentToday = Attendance::valid()->where('date', $today)->distinct('student_id')->count('student_id');
        $pendingInvalidations = Attendance::where('invalidation_status', 'requested')->count();
        $pendingManualRequests = ManualAttendanceRequest::where('status', ManualAttendanceRequest::STATUS_PENDING)->count();
        $missingFaceEnrollment = Student::whereNull('face_encoding')->count();
        $auditEventsToday = AuditLog::whereDate('created_at', $today)->count();

        $studentsByCourse = Student::selectRaw('course, COUNT(*) as total')
            ->groupBy('course')
            ->orderByDesc('total')
            ->get();

        $recentCoordinators = Coordinator::latest()->take(6)->get();

        return view('admin.dashboard', compact(
            'totalCoordinators',
            'activeCoordinators',
            'totalStudents',
            'verifiedStudents',
            'pendingStudents',
            'presentToday',
            'pendingInvalidations',
            'pendingManualRequests',
            'missingFaceEnrollment',
            'auditEventsToday',
            'studentsByCourse',
            'recentCoordinators'
        ));
    }
}
