<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ManualAttendanceRequest;
use App\Models\StudentTermAssignment;
use App\Support\WeekRangeFilter;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StudentDashboardController extends Controller
{
    /**
     * Show the student dashboard with today's attendance and logs (by month or week).
     */
    public function index(Request $request)
    {
        $student = Auth::guard('student')->user();
        if (! $student) {
            return redirect()->route('login');
        }
        $student->loadMissing([
            'activeTermAssignment',
            'termAssignments' => fn ($query) => $query->latest('id'),
        ]);
        $studentId = $student->id;

        $filter = $request->input('filter', 'month');
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $weekInput = $request->input('week');
        $weekStartInput = $request->input('week_start');
        $weekEndInput = $request->input('week_end');

        if ($filter === 'week') {
            [$weekStartInput, $weekEndInput] = WeekRangeFilter::defaultInputs($weekStartInput, $weekEndInput, $weekInput);
        }

        $attendance = Attendance::valid()->where('student_id', $studentId)
            ->where('date', now()->format('Y-m-d'))
            ->first();
        $timeOutCooldownMinutes = (int) config('dtr.time_out_cooldown_minutes', 30);
        $timeOutUnlockAtIso = null;
        $timeOutUnlockAtDisplay = null;
        $timeOutMinutesRemaining = 0;
        if ($attendance && ! $attendance->time_out && $timeOutCooldownMinutes > 0) {
            $timeInReference = $attendance->afternoon_time_in ?: $attendance->time_in;
            if ($timeInReference) {
                try {
                    $unlockAt = Carbon::parse(now('Asia/Manila')->toDateString().' '.$timeInReference, 'Asia/Manila')->addMinutes($timeOutCooldownMinutes);
                    $nowManila = now('Asia/Manila');
                    if ($nowManila->lt($unlockAt)) {
                        $timeOutUnlockAtIso = $unlockAt->toIso8601String();
                        $timeOutUnlockAtDisplay = $unlockAt->format(Attendance::TIME_12_FORMAT);
                        $timeOutMinutesRemaining = max(1, (int) ceil($nowManila->diffInMinutes($unlockAt, false) * -1));
                    }
                } catch (\Throwable $e) {
                    // Ignore invalid stored time format and let existing checks handle it.
                }
            }
        }

        $logs = collect();
        $weekLabel = '';
        $weekRange = $filter === 'week' ? WeekRangeFilter::parse($weekStartInput, $weekEndInput) : null;

        if ($filter === 'week' && $weekRange) {
            $logs = Attendance::valid()->where('student_id', $studentId)
                ->whereBetween('date', [$weekRange['start_date'], $weekRange['end_date']])
                ->orderBy('date', 'desc')
                ->get();

            $weekLabel = $weekRange['label'];
        } else {
            $filter = 'month';
            $parts = explode('-', $selectedMonth);
            $year = $parts[0] ?? now()->format('Y');
            $month = $parts[1] ?? now()->format('m');

            $logs = Attendance::valid()->where('student_id', $studentId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->orderBy('date', 'desc')
                ->get();
        }

        $termSummary = $this->buildTermSummary($student);

        return view('student.dashboard', [
            'student' => $student,
            'attendance' => $attendance,
            'logs' => $logs,
            'selectedMonth' => $selectedMonth,
            'filter' => $filter,
            'weekInput' => $weekStartInput ?? '',
            'weekStartInput' => $weekStartInput ?? '',
            'weekEndInput' => $weekEndInput ?? '',
            'weekLabel' => $weekLabel,
            'termSummary' => $termSummary,
            'timeOutUnlockAtIso' => $timeOutUnlockAtIso,
            'timeOutUnlockAtDisplay' => $timeOutUnlockAtDisplay,
            'timeOutMinutesRemaining' => $timeOutMinutesRemaining,
            'timeOutCooldownMinutes' => $timeOutCooldownMinutes,
        ]);
    }

    /**
     * JSON list of the signed-in student's manual attendance requests for a calendar month (modal / SPA-style UI).
     */
    public function manualRequestsForMonth(Request $request): JsonResponse
    {
        $student = Auth::guard('student')->user();
        if (! $student) {
            abort(401);
        }

        $month = trim((string) $request->query('month', now('Asia/Manila')->format('Y-m')));
        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            return response()->json(['message' => 'Invalid month format.'], 422);
        }

        try {
            $selected = Carbon::createFromFormat('Y-m', $month, 'Asia/Manila')->startOfMonth();
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Invalid month.'], 422);
        }

        $todayMonth = now('Asia/Manila')->startOfMonth();
        if ($selected->gt($todayMonth)) {
            return response()->json(['message' => 'Month cannot be in the future.'], 422);
        }

        [$y, $m] = explode('-', $month);
        $rows = ManualAttendanceRequest::query()
            ->where('student_id', $student->id)
            ->whereYear('attendance_date', (int) $y)
            ->whereMonth('attendance_date', (int) $m)
            ->with('reviewer:id,name')
            ->latest('attendance_date')
            ->latest('id')
            ->limit(50)
            ->get();

        return response()->json([
            'month' => $month,
            'month_label' => $selected->format('F Y'),
            'requests' => $rows->map(fn (ManualAttendanceRequest $r) => $this->serializeStudentManualRequest($r))->values(),
        ]);
    }

    private function serializeStudentManualRequest(ManualAttendanceRequest $req): array
    {
        $fmt = static function (?string $t): ?string {
            if ($t === null || $t === '') {
                return null;
            }
            try {
                return Carbon::parse($t)->format('g:i A');
            } catch (\Throwable $e) {
                return $t;
            }
        };

        $bits = [];
        if ($x = $fmt($req->time_in)) {
            $bits[] = 'AM in '.$x;
        }
        if ($x = $fmt($req->lunch_break_out)) {
            $bits[] = 'Lunch '.$x;
        }
        if ($x = $fmt($req->afternoon_time_in)) {
            $bits[] = 'PM in '.$x;
        }
        if ($x = $fmt($req->time_out)) {
            $bits[] = 'Out '.$x;
        }

        $reason = (string) ($req->reason ?? '');

        return [
            'id' => $req->id,
            'attendance_date_display' => $req->attendance_date ? $req->attendance_date->format('M j, Y') : null,
            'times_summary' => count($bits) ? implode(' · ', $bits) : '—',
            'status' => (string) ($req->status ?? 'pending'),
            'reviewer_name' => optional($req->reviewer)->name,
            'reason_preview' => $reason !== '' ? Str::limit($reason, 120) : null,
            'reason_full' => $reason !== '' ? $reason : null,
            'coordinator_note' => $req->coordinator_note ? (string) $req->coordinator_note : null,
        ];
    }

    private function buildTermSummary($student): array
    {
        $activeAssignment = $student->activeTermAssignment;
        $latestAssignment = $student->termAssignments->first();
        $completedAssignments = $student->termAssignments
            ->where('status', StudentTermAssignment::STATUS_COMPLETED)
            ->take(3)
            ->values();

        if ($activeAssignment) {
            return [
                'badge' => 'Active',
                'badge_class' => 'status-active',
                'headline' => $activeAssignment->term,
                'section' => $activeAssignment->section,
                'school_year' => $activeAssignment->school_year,
                'program' => $activeAssignment->course ?: $student->course,
                'note' => 'You are currently assigned to this OJT term.',
                'history' => collect(),
            ];
        }

        if ($latestAssignment && $latestAssignment->status === StudentTermAssignment::STATUS_COMPLETED) {
            return [
                'badge' => 'Completed',
                'badge_class' => 'status-completed',
                'headline' => $latestAssignment->term,
                'section' => $latestAssignment->section,
                'school_year' => $latestAssignment->school_year,
                'program' => $latestAssignment->course ?: $student->course,
                'note' => 'Your latest OJT term is completed. Wait for the next assignment from the admin.',
                'history' => $completedAssignments,
            ];
        }

        return [
            'badge' => 'Awaiting assignment',
            'badge_class' => 'status-pending',
            'headline' => 'No active term yet',
            'section' => null,
            'school_year' => null,
            'program' => $student->course,
            'note' => 'Your admin will assign your term and section before your coordinator can fully manage your records.',
            'history' => collect(),
        ];
    }
}
