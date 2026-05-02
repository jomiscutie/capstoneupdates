<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewManualAttendanceRequest;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceEvent;
use App\Models\AuditLog;
use App\Models\Coordinator;
use App\Models\ManualAttendanceRequest;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOversightController extends Controller
{
    public function invalidations(Request $request)
    {
        $status = (string) $request->input('status', 'requested');
        $q = trim((string) $request->input('q', ''));

        $query = Attendance::query()
            ->with(['student', 'invalidatedByCoordinator'])
            ->whereIn('invalidation_status', ['requested', 'approved', 'rejected']);

        if (in_array($status, ['requested', 'approved', 'rejected'], true)) {
            $query->where('invalidation_status', $status);
        }

        if ($q !== '') {
            $query->whereHas('student', function ($studentQuery) use ($q) {
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $studentQuery->where('name', 'like', $term)
                    ->orWhere('student_no', 'like', $term)
                    ->orWhere('course', 'like', $term);
            });
        }

        $records = $query->orderByDesc('invalidation_requested_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.invalidations', compact('records', 'status', 'q'));
    }

    public function reviewInvalidation(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'decision' => 'required|in:approve,reject',
            'review_note' => 'nullable|string|max:1000',
        ]);

        if ($attendance->invalidation_status !== 'requested') {
            return back()->with('info', 'This request was already reviewed.');
        }

        $admin = Auth::guard('admin')->user();
        $attendance->invalidation_status = $validated['decision'] === 'approve' ? 'approved' : 'rejected';
        $attendance->invalidation_review_note = trim((string) ($validated['review_note'] ?? '')) ?: null;
        $attendance->invalidation_reviewed_by = $admin?->id;
        $attendance->invalidation_reviewed_at = now('Asia/Manila');

        if ($validated['decision'] === 'approve') {
            $attendance->is_invalid = true;
            $attendance->invalidated_at = $attendance->invalidated_at ?: now('Asia/Manila');
        } else {
            $attendance->is_invalid = false;
            $attendance->invalidated_at = null;
        }
        $attendance->save();

        AuditLog::create([
            'actor_type' => 'admin',
            'actor_id' => $admin?->id,
            'action' => $validated['decision'] === 'approve'
                ? 'attendance_invalidation_approved'
                : 'attendance_invalidation_rejected',
            'target_type' => 'attendance',
            'target_id' => $attendance->id,
            'details' => $attendance->invalidation_review_note,
            'context' => [
                'student_id' => $attendance->student_id,
                'date' => $attendance->date,
            ],
        ]);

        return back()->with('success', 'Invalidation request '.($validated['decision'] === 'approve' ? 'approved' : 'rejected').'.');
    }

    public function restoreAttendance(Request $request, Attendance $attendance)
    {
        if (! $attendance->is_invalid) {
            return back()->with('info', 'Attendance is already active.');
        }
        $admin = Auth::guard('admin')->user();
        $attendance->is_invalid = false;
        $attendance->invalidation_status = 'rejected';
        $attendance->invalidation_review_note = trim((string) $request->input('reason', 'Restored by admin'));
        $attendance->invalidation_reviewed_by = $admin?->id;
        $attendance->invalidation_reviewed_at = now('Asia/Manila');
        $attendance->invalidated_at = null;
        $attendance->save();

        AuditLog::create([
            'actor_type' => 'admin',
            'actor_id' => $admin?->id,
            'action' => 'attendance_restored',
            'target_type' => 'attendance',
            'target_id' => $attendance->id,
            'details' => $attendance->invalidation_review_note,
            'context' => [
                'student_id' => $attendance->student_id,
                'date' => $attendance->date,
            ],
        ]);

        return back()->with('success', 'Attendance restored successfully.');
    }

    public function faceEnrollment(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $query = Student::query()->whereNull('face_encoding');
        if ($q !== '') {
            $term = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
            $query->where(function ($inner) use ($term) {
                $inner->where('name', 'like', $term)
                    ->orWhere('student_no', 'like', $term)
                    ->orWhere('course', 'like', $term);
            });
        }
        $students = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('admin.face-enrollment', compact('students', 'q'));
    }

    public function sessions()
    {
        $coordinators = Coordinator::query()->orderBy('name')->get(['id', 'name', 'email', 'is_active', 'current_session_id']);
        $students = Student::query()->orderBy('name')->limit(300)->get(['id', 'name', 'student_no', 'course', 'current_session_id']);

        return view('admin.sessions', compact('coordinators', 'students'));
    }

    public function forceLogout(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:coordinator,student',
            'id' => 'required|integer',
        ]);

        if ($validated['type'] === 'coordinator') {
            $user = Coordinator::findOrFail($validated['id']);
        } else {
            $user = Student::findOrFail($validated['id']);
        }

        $user->current_session_id = null;
        $user->save();

        AuditLog::create([
            'actor_type' => 'admin',
            'actor_id' => Auth::guard('admin')->id(),
            'action' => 'force_logout_'.$validated['type'],
            'target_type' => $validated['type'],
            'target_id' => $user->id,
            'details' => 'Session revoked from admin monitor.',
        ]);

        return back()->with('success', 'Session revoked successfully.');
    }

    public function auditLogs()
    {
        $logs = AuditLog::query()->latest()->paginate(50);
        $rows = $logs->getCollection();

        $adminIds = $rows->where('actor_type', 'admin')->pluck('actor_id')->filter()->unique()->values();
        $coordinatorIds = $rows->where('actor_type', 'coordinator')->pluck('actor_id')->filter()->unique()->values();
        $studentActorIds = $rows->where('actor_type', 'student')->pluck('actor_id')->filter()->unique()->values();
        $studentTargetIds = $rows->where('target_type', 'student')->pluck('target_id')->filter()->unique()->values();
        $studentIds = $studentActorIds->merge($studentTargetIds)->unique()->values();

        $adminMap = $adminIds->isNotEmpty()
            ? Admin::query()->whereIn('id', $adminIds)->pluck('name', 'id')
            : collect();
        $coordinatorMap = $coordinatorIds->isNotEmpty()
            ? Coordinator::query()->whereIn('id', $coordinatorIds)->pluck('name', 'id')
            : collect();
        $studentNoMap = $studentIds->isNotEmpty()
            ? Student::withTrashed()->whereIn('id', $studentIds)->pluck('student_no', 'id')
            : collect();

        $rows->transform(function ($log) use ($adminMap, $coordinatorMap, $studentNoMap) {
            $log->actor_label = match ($log->actor_type) {
                'admin' => 'ADMIN '.($adminMap[$log->actor_id] ?? '#'.$log->actor_id),
                'coordinator' => 'COORDINATOR '.($coordinatorMap[$log->actor_id] ?? '#'.$log->actor_id),
                'student' => 'STUDENT '.($studentNoMap[$log->actor_id] ?? '#'.$log->actor_id),
                default => strtoupper((string) $log->actor_type).' #'.($log->actor_id ?? '-'),
            };

            $log->target_label = match ($log->target_type) {
                'admin' => 'admin '.($adminMap[$log->target_id] ?? '#'.$log->target_id),
                'coordinator' => 'coordinator '.($coordinatorMap[$log->target_id] ?? '#'.$log->target_id),
                'student' => 'student '.($studentNoMap[$log->target_id] ?? '#'.$log->target_id),
                default => ($log->target_type ?? '-').' '.($log->target_id ? '#'.$log->target_id : ''),
            };

            return $log;
        });
        $logs->setCollection($rows);

        return view('admin.audit-logs', compact('logs'));
    }

    public function manualAttendanceRequests(Request $request)
    {
        $status = trim((string) $request->input('status', ManualAttendanceRequest::STATUS_PENDING));
        $search = trim((string) $request->input('q', ''));
        if (! in_array($status, [
            ManualAttendanceRequest::STATUS_PENDING,
            ManualAttendanceRequest::STATUS_APPROVED,
            ManualAttendanceRequest::STATUS_REJECTED,
            'all',
        ], true)) {
            $status = ManualAttendanceRequest::STATUS_PENDING;
        }

        $query = ManualAttendanceRequest::query()
            ->with(['student:id,name,student_no,course', 'reviewer:id,name']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($search !== '') {
            $pattern = $this->wildcardSearchPattern($search);
            $query->whereHas('student', function ($studentQuery) use ($pattern) {
                $studentQuery->where('name', 'like', $pattern)
                    ->orWhere('student_no', 'like', $pattern)
                    ->orWhere('course', 'like', $pattern);
            });
        }

        $requests = $query->orderByDesc('id')->paginate(25)->withQueryString();

        return view('admin.manual-attendance-requests', compact('requests', 'status', 'search'));
    }

    public function reviewManualAttendanceRequest(ReviewManualAttendanceRequest $request, ManualAttendanceRequest $manualRequest)
    {
        $redirect = redirect()->route('admin.manual.requests');
        if ($manualRequest->status !== ManualAttendanceRequest::STATUS_PENDING) {
            return $redirect->with('info', 'This request was already reviewed.');
        }

        $validated = $request->validated();
        $decision = $validated['decision'];
        $note = trim((string) ($validated['coordinator_note'] ?? ''));
        $result = $this->applyAdminManualDecision($manualRequest, $decision, $note);
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }

        if ($decision === 'approve') {
            return $redirect->with('success', 'Manual request approved by admin and attendance was posted.');
        }

        return $redirect->with('error', 'Manual request rejected by admin.');
    }

    public function bulkReviewManualAttendanceRequests(Request $request)
    {
        $redirect = redirect()->route('admin.manual.requests');
        $validated = $request->validate([
            'request_ids' => ['required', 'array', 'min:1'],
            'request_ids.*' => ['integer'],
            'decision' => ['required', 'in:approve,reject'],
            'coordinator_note' => ['nullable', 'string', 'max:1500'],
        ]);
        $decision = (string) $validated['decision'];
        $note = trim((string) ($validated['coordinator_note'] ?? ''));

        $targets = ManualAttendanceRequest::query()
            ->whereIn('id', $validated['request_ids'])
            ->where('status', ManualAttendanceRequest::STATUS_PENDING)
            ->get();

        if ($targets->isEmpty()) {
            return $redirect->with('info', 'No eligible pending manual requests were selected.');
        }

        $approved = 0;
        $rejected = 0;
        $skipped = 0;
        foreach ($targets as $manualRequest) {
            $result = $this->applyAdminManualDecision($manualRequest, $decision, $note, true);
            if ($result === true) {
                if ($decision === 'approve') {
                    $approved++;
                } else {
                    $rejected++;
                }
            } else {
                $skipped++;
            }
        }

        if ($decision === 'approve') {
            return $redirect->with('success', "Bulk approve complete. Approved: {$approved}, Skipped: {$skipped}.");
        }

        return $redirect->with('error', "Bulk reject complete. Rejected: {$rejected}, Skipped: {$skipped}.");
    }

    private function applyAdminManualDecision(
        $manualRequest,
        string $decision,
        string $note,
        bool $skipOnInvalid = false
    ): bool|\Illuminate\Http\RedirectResponse {
        if ($decision === 'approve') {
            $alreadyExists = Attendance::valid()
                ->where('student_id', $manualRequest->student_id)
                ->whereDate('date', Carbon::parse($manualRequest->attendance_date)->format('Y-m-d'))
                ->exists();
            if ($alreadyExists) {
                if ($skipOnInvalid) {
                    return false;
                }

                return redirect()->route('admin.manual.requests')->with('error', 'Attendance already exists for this date. Reject this request with a note instead.');
            }

            $hoursRendered = $this->calculateHoursRenderedFromTimes(
                $manualRequest->time_in,
                $manualRequest->lunch_break_out,
                $manualRequest->afternoon_time_in,
                $manualRequest->time_out
            );

            $postedAttendance = Attendance::create([
                'student_id' => $manualRequest->student_id,
                'date' => Carbon::parse($manualRequest->attendance_date)->format('Y-m-d'),
                'time_in' => $manualRequest->time_in,
                'lunch_break_out' => $manualRequest->lunch_break_out,
                'afternoon_time_in' => $manualRequest->afternoon_time_in,
                'time_out' => $manualRequest->time_out,
                'hours_rendered' => $hoursRendered,
                'is_late' => false,
                'late_minutes' => null,
                'afternoon_is_late' => false,
                'afternoon_late_minutes' => null,
            ]);
            $this->recordManualApprovalEvents($postedAttendance, $manualRequest, 'admin');

            $manualRequest->status = ManualAttendanceRequest::STATUS_APPROVED;
            $manualRequest->applied_at = now('Asia/Manila');
        } else {
            $manualRequest->status = ManualAttendanceRequest::STATUS_REJECTED;
        }

        $admin = Auth::guard('admin')->user();
        $manualRequest->reviewed_by = null;
        $manualRequest->reviewed_at = now('Asia/Manila');
        $manualRequest->coordinator_note = $note !== '' ? $note : null;
        $manualRequest->save();

        AuditLog::create([
            'actor_type' => 'admin',
            'actor_id' => $admin?->id,
            'action' => $decision === 'approve' ? 'manual_attendance_request_approved_by_admin' : 'manual_attendance_request_rejected_by_admin',
            'target_type' => 'manual_attendance_request',
            'target_id' => $manualRequest->id,
            'details' => $manualRequest->coordinator_note ?: 'No review note provided.',
            'context' => [
                'student_id' => $manualRequest->student_id,
                'attendance_date' => $manualRequest->attendance_date ? Carbon::parse($manualRequest->attendance_date)->format('Y-m-d') : null,
            ],
        ]);

        return true;
    }

    private function wildcardSearchPattern(string $query): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $query);
        $wild = str_replace(['*', '?'], ['%', '_'], $escaped);

        return '%'.$wild.'%';
    }

    private function calculateHoursRenderedFromTimes(?string $timeIn, ?string $lunchOut, ?string $afternoonIn, ?string $timeOut): string
    {
        if (! $timeOut) {
            return '0 hr 0 min';
        }

        $totalMinutes = 0;
        if ($timeIn) {
            $morningIn = Carbon::parse($timeIn, 'Asia/Manila');
            $timeOutAt = Carbon::parse($timeOut, 'Asia/Manila');

            if ($lunchOut) {
                $morningEnd = Carbon::parse($lunchOut, 'Asia/Manila');
                if ($morningEnd->gt($morningIn)) {
                    $totalMinutes += abs($morningEnd->diffInMinutes($morningIn));
                }
            } elseif ($afternoonIn) {
                $afternoonStart = Carbon::parse($afternoonIn, 'Asia/Manila');
                $morningEnd = $afternoonStart->lt($timeOutAt) ? $afternoonStart : $timeOutAt;
                if ($morningEnd->gt($morningIn)) {
                    $totalMinutes += abs($morningEnd->diffInMinutes($morningIn));
                }
            } elseif ($timeOutAt->gt($morningIn)) {
                $totalMinutes += abs($timeOutAt->diffInMinutes($morningIn));
            }
        }

        if ($afternoonIn && $timeOut) {
            $afternoonStart = Carbon::parse($afternoonIn, 'Asia/Manila');
            $afternoonEnd = Carbon::parse($timeOut, 'Asia/Manila');
            if ($afternoonEnd->gt($afternoonStart)) {
                $totalMinutes += abs($afternoonEnd->diffInMinutes($afternoonStart));
            }
        }

        $hours = (int) floor($totalMinutes / 60);
        $minutes = (int) ($totalMinutes % 60);

        return "{$hours} hr {$minutes} min";
    }

    private function recordAttendanceEvent(
        Attendance $attendance,
        int $studentId,
        string $eventType,
        ?string $direction,
        Carbon $occurredAt,
        string $source,
        ?string $verificationMethod = null,
        ?string $snapshotPath = null,
        array $meta = [],
        ?int $manualRequestId = null
    ): void {
        AttendanceEvent::create([
            'attendance_id' => $attendance->id,
            'student_id' => $studentId,
            'manual_attendance_request_id' => $manualRequestId,
            'event_type' => $eventType,
            'event_direction' => $direction,
            'occurred_at' => $occurredAt,
            'source' => $source,
            'verification_method' => $verificationMethod,
            'snapshot_path' => $snapshotPath,
            'meta' => $meta !== [] ? $meta : null,
        ]);
    }

    private function recordManualApprovalEvents(Attendance $attendance, ManualAttendanceRequest $manualRequest, string $approvedBy): void
    {
        $date = Carbon::parse($attendance->date)->format('Y-m-d');
        $eventMap = [
            ['field' => 'time_in', 'type' => 'morning_time_in', 'direction' => 'in'],
            ['field' => 'lunch_break_out', 'type' => 'lunch_break_out', 'direction' => 'out'],
            ['field' => 'afternoon_time_in', 'type' => 'afternoon_time_in', 'direction' => 'in'],
            ['field' => 'time_out', 'type' => 'time_out', 'direction' => 'out'],
        ];

        foreach ($eventMap as $event) {
            $field = $event['field'];
            $time = $attendance->{$field};
            if (! $time) {
                continue;
            }
            $this->recordAttendanceEvent(
                $attendance,
                (int) $attendance->student_id,
                $event['type'],
                $event['direction'],
                Carbon::parse($date.' '.$time, 'Asia/Manila'),
                'manual_request_approved',
                'manual',
                null,
                [
                    'approved_by' => $approvedBy,
                    'manual_request_id' => $manualRequest->id,
                ],
                (int) $manualRequest->id
            );
        }
    }
}
