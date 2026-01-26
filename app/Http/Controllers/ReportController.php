<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Generate monthly attendance report for a specific student
     */
    public function generateMonthlyReport(Request $request)
    {
        $coordinator = Auth::guard('coordinator')->user();
        
        // Validate request
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'month' => 'required|date_format:Y-m',
        ]);

        $studentId = $request->input('student_id');
        $month = $request->input('month');
        
        // Parse month
        $date = Carbon::createFromFormat('Y-m', $month);
        $year = $date->year;
        $monthNum = $date->month;
        
        // Get student
        $student = Student::findOrFail($studentId);
        
        // Verify student belongs to coordinator's program
        if ($coordinator->major && $student->course !== $coordinator->major) {
            return back()->with('error', 'You do not have permission to view this student\'s report.');
        }
        
        // Get attendance records for the month
        $attendances = Attendance::where('student_id', $studentId)
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->orderBy('date', 'asc')
            ->get();
        
        // Calculate statistics
        $totalDays = $date->daysInMonth;
        $presentDays = $attendances->count();
        $absentDays = $totalDays - $presentDays;
        
        // Count late arrivals
        $lateArrivals = $attendances->filter(function($attendance) {
            return $attendance->is_late || $attendance->afternoon_is_late;
        })->count();
        
        // Calculate total hours rendered
        $totalMinutes = 0;
        foreach ($attendances as $attendance) {
            // Morning hours
            if ($attendance->time_in && $attendance->time_out) {
                $morningIn = Carbon::parse($attendance->time_in);
                $timeOut = Carbon::parse($attendance->time_out);
                // Only count if time_out is before 12 PM (lunch break scenario)
                if ($timeOut->format('H') < 12) {
                    $totalMinutes += abs($timeOut->diffInMinutes($morningIn));
                }
            }
            // Afternoon hours
            if ($attendance->afternoon_time_in && $attendance->time_out) {
                $afternoonIn = Carbon::parse($attendance->afternoon_time_in);
                $timeOut = Carbon::parse($attendance->time_out);
                $totalMinutes += abs($timeOut->diffInMinutes($afternoonIn));
            }
        }
        
        $totalHours = floor($totalMinutes / 60);
        $remainingMinutes = $totalMinutes % 60;
        
        // Prepare data for PDF
        $data = [
            'student' => $student,
            'month' => $date->format('F Y'),
            'monthNum' => $monthNum,
            'year' => $year,
            'attendances' => $attendances,
            'totalDays' => $totalDays,
            'presentDays' => $presentDays,
            'absentDays' => $absentDays,
            'lateArrivals' => $lateArrivals,
            'totalHours' => $totalHours,
            'totalMinutes' => $remainingMinutes,
            'coordinator' => $coordinator,
            'generatedAt' => Carbon::now('Asia/Manila')->format('F d, Y h:i A'),
        ];
        
        // Generate PDF
        $pdf = Pdf::loadView('reports.monthly-attendance', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'Attendance_Report_' . $student->student_no . '_' . $month . '.pdf';
        
        return $pdf->download($filename);
    }
    
    /**
     * Show report generation form
     */
    public function showReportForm()
    {
        $coordinator = Auth::guard('coordinator')->user();
        
        // Get students from coordinator's program
        $students = Student::where('course', $coordinator->major)->get();
        
        return view('coordinator.generate-report', compact('students'));
    }
}

