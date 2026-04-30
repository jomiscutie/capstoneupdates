<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvalidateAttendanceRequest;
use App\Http\Requests\ReviewManualAttendanceRequest;
use App\Http\Requests\StoreManualAttendanceRequest;
use App\Http\Requests\TimeInRequest;
use App\Http\Requests\TimeOutRequest;
use App\Models\Attendance;
use App\Models\AttendanceEvent;
use App\Models\AuditLog;
use App\Models\ManualAttendanceRequest;
use App\Models\StudentTermAssignment;
use App\Support\Services\FaceEncodingService;
use App\Support\StudentSearch;
use App\Support\WeekRangeFilter;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    public function kioskIndex(Request $request)
    {
        $stationLabel = trim((string) $request->query('station_name', 'Kiosk Station'));
        $stationId = trim((string) $request->query('station_id', 'station-1'));

        return view('kiosk.index', [
            'stationLabel' => $stationLabel,
            'stationId' => $stationId,
        ]);
    }

    public function kioskTimeIn(TimeInRequest $request)
    {
        return $this->runKioskAction($request, fn () => $this->timeIn($request));
    }

    public function kioskLunchBreakOut(TimeOutRequest $request)
    {
        return $this->runKioskAction($request, fn () => $this->lunchBreakOut($request));
    }

    public function kioskTimeOut(TimeOutRequest $request)
    {
        return $this->runKioskAction($request, fn () => $this->timeOut($request));
    }

    public function kioskIdentify(Request $request)
    {
        $request->validate([
            'face_encoding' => ['required', 'string'],
        ]);

        $match = $this->resolveKioskFaceMatch($request);
        if (! $match || ! isset($match['student'])) {
            return response()->json([
                'matched' => false,
                'message' => (string) ($match['message'] ?? 'Face not recognized.'),
            ], 200);
        }

        $student = $match['student'];

        return response()->json([
            'matched' => true,
            'student_id' => (int) $student->id,
            'student_no' => (string) $student->student_no,
            'student_name' => (string) $student->name,
            'confidence' => (int) round((float) ($match['confidence'] ?? 0)),
            'distance' => round((float) ($match['distance'] ?? 0), 4),
        ]);
    }

    public function timeIn(TimeInRequest $request)
    {
        date_default_timezone_set('Asia/Manila');
        try {
            $studentId = Auth::guard('student')->id();
            $student = \App\Models\Student::find($studentId);
            $today = Carbon::today('Asia/Manila')->toDateString();

            // Only verified students can time in
            if (! $student || ! $student->isVerified()) {
                return back()->with('error', 'Your account must be verified by your coordinator before you can record attendance. Please contact your OJT coordinator.');
            }

            // Password fallback is allowed only as an explicit exception; otherwise face must match enrolled encoding.
            if ($request->input('verification_method') === 'password') {
                if (! $request->filled('password')) {
                    return back()->with('error', 'Please enter your password to verify your identity.');
                }
                if (! $request->filled('verification_reason')) {
                    return back()->with('error', 'Please select why password verification is needed before recording attendance.');
                }
                if (! Hash::check($request->password, $student->password)) {
                    return back()->with('error', 'Incorrect password. Please try again.');
                }
            } else {
                $faceReject = $this->rejectUnlessFaceMatchesStored($student, $request);
                if ($faceReject !== null) {
                    return $faceReject;
                }
            }

            // Use client-recorded time when syncing from offline (recorded_at in Asia/Manila), else server time
            $currentTime = Carbon::now('Asia/Manila');
            if ($request->filled('recorded_at')) {
                try {
                    $recorded = Carbon::parse($request->recorded_at)->timezone('Asia/Manila');
                    $todayRecorded = $recorded->toDateString();
                    if ($todayRecorded === $today) {
                        $currentTime = $recorded;
                    }
                } catch (\Exception $e) {
                    // Ignore invalid recorded_at, use server time
                }
            }
            $timeInString = $currentTime->toTimeString();

            $noon = Carbon::createFromTime(12, 0, 0, 'Asia/Manila');
            $serverNow = Carbon::now('Asia/Manila');

            // Get or create attendance record (needed before morning/afternoon routing)
            $attendance = Attendance::valid()->firstOrNew([
                'student_id' => $studentId,
                'date' => $today,
            ]);

            // After A.M. departure (lunch break out), the next Time In is always "afternoon return", even
            // if the clock is still before 12:00 (e.g. lunch at 11:00, back at 11:30 or 11:45).
            $returnFromLunch = $attendance->time_in
                && $attendance->lunch_break_out
                && ! $attendance->afternoon_time_in;

            $isAfternoon = $currentTime->gte($noon);
            $timeHour = (int) $currentTime->format('H');

            // Hour bucketing: do not force "morning" when this tap is the post-lunch return before noon.
            if (! $returnFromLunch || $currentTime->gte($noon)) {
                if ($timeHour >= 12 && ! $isAfternoon) {
                    $isAfternoon = true;
                }
                if ($timeHour < 12 && $isAfternoon) {
                    $isAfternoon = false;
                }
            }

            $afternoonInAllowedBeforeNoon = false;
            if ($returnFromLunch && $currentTime->lt($noon)) {
                $isAfternoon = true;
                $afternoonInAllowedBeforeNoon = true;
            }

            // Second Time In when wall clock is already p.m. but client `recorded_at` is still a.m.
            // (offline sync, stale password hidden field) — use server time for this tap.
            if ($attendance->time_in && ! $attendance->afternoon_time_in && $serverNow->gte($noon) && $currentTime->lt($noon)) {
                $isAfternoon = true;
                $currentTime = $serverNow;
                $timeInString = $currentTime->toTimeString();
                $afternoonInAllowedBeforeNoon = false;
            }

            if ($isAfternoon) {
                // AFTERNOON TIME-IN: normally 12:00 PM onwards; also return-from-lunch before noon when lunch out is recorded

                if ($attendance->afternoon_time_in) {
                    $recorded = \Carbon\Carbon::parse($attendance->afternoon_time_in)->format('g:i A');

                    return back()->with('error', 'You have already recorded your afternoon time-in today. Duplicate time-in is not allowed for security. Recorded at: '.$recorded.'.')
                        ->with('error_type', 'already_time_in');
                }

                // CRITICAL SAFEGUARD: Check if any existing morning time-in is actually afternoon time
                // This fixes any incorrectly categorized records
                if ($attendance->time_in) {
                    $morningTime = Carbon::parse($attendance->time_in);
                    $morningHour = (int) $morningTime->format('H');
                    if ($morningHour >= 12) {
                        // Morning time-in was incorrectly recorded (it's actually afternoon time)
                        // Move it to afternoon and clear morning
                        $attendance->afternoon_time_in = $attendance->time_in;
                        $attendance->afternoon_is_late = $attendance->is_late;
                        $attendance->afternoon_late_minutes = $attendance->late_minutes;
                        $attendance->time_in = null;
                        $attendance->is_late = false;
                        $attendance->late_minutes = null;
                    }
                } else {
                    // For new records or records without morning time-in, explicitly set time_in to null
                    // This ensures the database constraint is satisfied
                    $attendance->time_in = null;
                    $attendance->is_late = false;
                    $attendance->late_minutes = null;
                }

                if ($attendance->lunch_break_out) {
                    $lunchEnd = Carbon::parse($today.' '.$attendance->lunch_break_out, 'Asia/Manila');
                    if ($currentTime->lte($lunchEnd)) {
                        return back()->with('error', 'Afternoon time-in must be after your lunch / break out ('.$lunchEnd->format('g:i A').').');
                    }
                }

                if ($currentTime->gt(Carbon::parse($today.' 21:00:00', 'Asia/Manila'))) {
                    return back()->with('error', 'Time-in is only allowed until 9:00 PM.');
                }

                // Reject impossible clock (afternoon slot) unless this is return-from-lunch before noon
                $savedTimeHour = (int) Carbon::parse($timeInString)->format('H');
                if ($savedTimeHour < 12 && ! $afternoonInAllowedBeforeNoon) {
                    return back()->with('error', 'System error: Time validation failed. Please try again.');
                }

                // Save to AFTERNOON time-in field (NOT morning)
                $attendance->afternoon_time_in = $timeInString;
                $attendance->afternoon_is_late = false;
                $attendance->afternoon_late_minutes = null;

                $verificationNote = $request->input('verification_method') === 'password'
                    ? ' (password verification)'
                    : (' with face verification.'.$this->confidenceSuffix($request));
                $message = 'Afternoon Time In recorded successfully'.$verificationNote;
            } else {
                // MORNING TIME-IN: Before 12:00 PM (00:00:00 to 11:59:59)
                // Examples: 6:00 AM, 7:00 AM, 8:00 AM, 9:00 AM, 10:00 AM, 11:00 AM, etc.

                if ($attendance->time_in) {
                    $recorded = \Carbon\Carbon::parse($attendance->time_in)->format('g:i A');

                    return back()->with('error', 'You have already recorded your morning time-in today. Duplicate time-in is not allowed for security. Recorded at: '.$recorded.'.')
                        ->with('error_type', 'already_time_in');
                }

                // CRITICAL SAFEGUARD: Prevent morning time-in if it's 12:00 PM or later
                // This ensures 2 PM CANNOT be saved to morning time_in
                if ($currentTime->gte($noon)) {
                    return back()->with('error', 'It is already past 12:00 PM. Please use afternoon time-in instead. Morning time-in is only allowed before 12:00 PM.');
                }

                // Additional validation: Check hour value
                $timeHour = (int) $currentTime->format('H');
                if ($timeHour >= 12) {
                    return back()->with('error', 'System error: Cannot record afternoon time in morning field. Please try again.');
                }
                if ($currentTime->gt(Carbon::parse($today.' 21:00:00', 'Asia/Manila'))) {
                    return back()->with('error', 'Time-in is only allowed until 9:00 PM.');
                }

                // FINAL VALIDATION: Ensure the time string hour is < 12 before saving
                $savedTimeHour = (int) Carbon::parse($timeInString)->format('H');
                if ($savedTimeHour >= 12) {
                    return back()->with('error', 'System error: Cannot save afternoon time to morning field. Please try again.');
                }

                // Save to MORNING time-in field (NOT afternoon)
                $attendance->time_in = $timeInString;
                $attendance->is_late = false;
                $attendance->late_minutes = null;

                $verificationNote = $request->input('verification_method') === 'password'
                    ? ' (password verification)'
                    : (' with face verification.'.$this->confidenceSuffix($request));
                $message = 'Morning Time In recorded successfully'.$verificationNote;
            }

            $verificationSnapshotPath = null;
            if ($request->hasFile('verification_snapshot')) {
                $path = $request->file('verification_snapshot')->store('verification_snapshots', 'public');
                $verificationSnapshotPath = $path;
                if ($isAfternoon) {
                    $attendance->afternoon_verification_snapshot = $path;
                } else {
                    $attendance->verification_snapshot = $path;
                }
            }

            $attendance->save();
            $this->recordAttendanceEvent(
                $attendance,
                $studentId,
                $isAfternoon ? 'afternoon_time_in' : 'morning_time_in',
                'in',
                Carbon::parse($today.' '.$timeInString, 'Asia/Manila'),
                $this->captureSource($request),
                (string) ($request->input('verification_method') ?: 'face'),
                $verificationSnapshotPath,
                $this->captureMeta($request, [
                    'recorded_at_client' => $request->input('recorded_at'),
                    'confidence' => $request->input('verification_confidence'),
                ])
            );
            $policyWarning = $this->passwordFallbackPolicyNotice($student, $request, 'time_in');

            $redirect = back()->with('success', $message);
            if ($policyWarning) {
                $redirect->with('warning', $policyWarning);
            }

            return $redirect;
        } catch (\Throwable $e) {
            Log::error('Time-in failed', ['student_id' => Auth::guard('student')->id(), 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Unable to record time-in. Please try again. If the problem persists, contact your coordinator.');
        }
    }

    public function timeOut(TimeOutRequest $request)
    {
        date_default_timezone_set('Asia/Manila');
        try {
            $studentId = Auth::guard('student')->id();
            $student = \App\Models\Student::find($studentId);
            $today = Carbon::today('Asia/Manila')->toDateString();

            // Only verified students can time out
            if (! $student || ! $student->isVerified()) {
                return back()->with('error', 'Your account must be verified by your coordinator before you can record attendance. Please contact your OJT coordinator.');
            }

            // Password fallback is allowed only as an explicit exception; otherwise face must match enrolled encoding.
            if ($request->input('verification_method') === 'password') {
                if (! $request->filled('password')) {
                    return back()->with('error', 'Please enter your password to verify your identity.');
                }
                if (! $request->filled('verification_reason')) {
                    return back()->with('error', 'Please select why password verification is needed before recording attendance.');
                }
                if (! Hash::check($request->password, $student->password)) {
                    return back()->with('error', 'Incorrect password. Please try again.');
                }
            } else {
                $faceReject = $this->rejectUnlessFaceMatchesStored($student, $request);
                if ($faceReject !== null) {
                    return $faceReject;
                }
            }

            $attendance = Attendance::valid()->where('student_id', $studentId)
                ->where('date', $today)
                ->first();

            // Strict old flow: a day must start with morning time-in before allowing time-out.
            if (! $attendance || ! $attendance->time_in) {
                return back()->with('error', 'You must record your morning time-in first before you can time out.')
                    ->with('error_type', 'no_time_in');
            }

            // If lunch / break out exists, require afternoon return before time-out.
            if ($attendance->lunch_break_out && ! $attendance->afternoon_time_in) {
                return back()->with('error', 'Please record your afternoon time-in first before time-out.')
                    ->with('error_type', 'no_afternoon_time_in');
            }

            if ($attendance->time_out) {
                $recorded = \Carbon\Carbon::parse($attendance->time_out)->format('g:i A');

                return back()->with('error', 'You have already recorded your time-out today. Duplicate time-out is not allowed for security. Recorded at: '.$recorded.'.')
                    ->with('error_type', 'already_time_out');
            }

            $currentTime = Carbon::now('Asia/Manila');
            if ($request->filled('recorded_at')) {
                try {
                    $recorded = Carbon::parse($request->recorded_at)->timezone('Asia/Manila');
                    if ($recorded->toDateString() === $today) {
                        $currentTime = $recorded;
                    }
                } catch (\Exception $e) {
                    // Ignore invalid recorded_at
                }
            }
            if ($currentTime->gt(Carbon::parse($today.' 21:00:00', 'Asia/Manila'))) {
                return back()->with('error', 'Time-out is only allowed until 9:00 PM.');
            }

            // Minimum gap after latest time-in before time-out (configurable).
            $cooldownMinutes = (int) config('dtr.time_out_cooldown_minutes', 30);
            $timeInReference = $attendance->afternoon_time_in ?: $attendance->time_in;
            if ($timeInReference && $cooldownMinutes > 0) {
                $minTimeoutAt = Carbon::parse($today.' '.$timeInReference, 'Asia/Manila')->addMinutes($cooldownMinutes);
                if ($currentTime->lt($minTimeoutAt)) {
                    $remainingMinutes = max(1, (int) ceil($currentTime->diffInMinutes($minTimeoutAt, false) * -1));

                    return back()->with(
                        'error',
                        "You can only time out after at least {$cooldownMinutes} minute(s) from your latest time-in. Please wait {$remainingMinutes} more minute(s)."
                    );
                }
            }

            $attendance->time_out = $currentTime->toTimeString();

            // Calculate total hours rendered (morning + afternoon)
            $totalMinutes = 0;

            // Morning hours: end at lunch break out when set, else afternoon time-in, else time-out
            if ($attendance->time_in) {
                $morningIn = Carbon::parse($attendance->time_in);
                $timeOut = Carbon::parse($attendance->time_out);

                if ($attendance->lunch_break_out) {
                    $morningEnd = Carbon::parse($attendance->lunch_break_out);
                    if ($morningEnd->gt($morningIn)) {
                        $totalMinutes += abs($morningEnd->diffInMinutes($morningIn));
                    }
                } elseif ($attendance->afternoon_time_in) {
                    $afternoonIn = Carbon::parse($attendance->afternoon_time_in);
                    $morningEnd = $afternoonIn->lt($timeOut) ? $afternoonIn : $timeOut;
                    if ($morningEnd->gt($morningIn)) {
                        $totalMinutes += abs($morningEnd->diffInMinutes($morningIn));
                    }
                } else {
                    $totalMinutes += abs($timeOut->diffInMinutes($morningIn));
                }
            }

            // Afternoon hours: from afternoon time-in to time-out (if afternoon time-in exists)
            if ($attendance->afternoon_time_in) {
                $afternoonIn = Carbon::parse($attendance->afternoon_time_in);
                $timeOut = Carbon::parse($attendance->time_out);

                // Only count if time-out is after afternoon time-in
                if ($timeOut->gt($afternoonIn)) {
                    $totalMinutes += abs($timeOut->diffInMinutes($afternoonIn));
                }
            }

            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            $attendance->hours_rendered = "{$hours} hr {$minutes} min";

            $timeoutSnapshotPath = null;
            if ($request->hasFile('verification_snapshot')) {
                $path = $request->file('verification_snapshot')->store('verification_snapshots', 'public');
                $timeoutSnapshotPath = $path;
                $attendance->timeout_verification_snapshot = $path;
            }

            $attendance->save();
            $this->recordAttendanceEvent(
                $attendance,
                $studentId,
                'time_out',
                'out',
                Carbon::parse($today.' '.$attendance->time_out, 'Asia/Manila'),
                $this->captureSource($request),
                (string) ($request->input('verification_method') ?: 'face'),
                $timeoutSnapshotPath,
                $this->captureMeta($request, [
                    'recorded_at_client' => $request->input('recorded_at'),
                    'confidence' => $request->input('verification_confidence'),
                ])
            );

            $verificationNote = $request->input('verification_method') === 'password' ? ' (password verification)' : (' with face verification.'.$this->confidenceSuffix($request));
            $policyWarning = $this->passwordFallbackPolicyNotice($student, $request, 'time_out');

            $redirect = back()->with('success', 'Time Out recorded successfully'.$verificationNote);
            if ($policyWarning) {
                $redirect->with('warning', $policyWarning);
            }

            return $redirect;
        } catch (\Throwable $e) {
            Log::error('Time-out failed', ['student_id' => Auth::guard('student')->id(), 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Unable to record time-out. Please try again. If the problem persists, contact your coordinator.');
        }
    }

    /**
     * Record morning / lunch departure (DTR A.M. Departure). Same verification model as time-in/out.
     */
    public function lunchBreakOut(TimeOutRequest $request)
    {
        date_default_timezone_set('Asia/Manila');
        try {
            $studentId = Auth::guard('student')->id();
            $student = \App\Models\Student::find($studentId);
            $today = Carbon::today('Asia/Manila')->toDateString();

            if (! $student || ! $student->isVerified()) {
                return back()->with('error', 'Your account must be verified by your coordinator before you can record attendance. Please contact your OJT coordinator.');
            }

            if ($request->input('verification_method') === 'password') {
                if (! $request->filled('password')) {
                    return back()->with('error', 'Please enter your password to verify your identity.');
                }
                if (! $request->filled('verification_reason')) {
                    return back()->with('error', 'Please select why password verification is needed before recording attendance.');
                }
                if (! Hash::check($request->password, $student->password)) {
                    return back()->with('error', 'Incorrect password. Please try again.');
                }
            } else {
                $faceReject = $this->rejectUnlessFaceMatchesStored($student, $request);
                if ($faceReject !== null) {
                    return $faceReject;
                }
            }

            $attendance = Attendance::valid()->where('student_id', $studentId)
                ->where('date', $today)
                ->first();

            if (! $attendance || ! $attendance->time_in) {
                return back()->with('error', 'Record your morning time-in first before leaving for lunch.');
            }

            if ($attendance->lunch_break_out) {
                $recorded = Carbon::parse($attendance->lunch_break_out)->format('g:i A');

                return back()->with('error', 'Lunch / break out was already recorded today at '.$recorded.'.');
            }

            if ($attendance->afternoon_time_in) {
                return back()->with('error', 'Afternoon time-in is already recorded. Lunch / break out cannot be added after that.');
            }

            if ($attendance->time_out) {
                return back()->with('error', 'Time-out is already recorded. Lunch / break out must be recorded before time-out.');
            }

            $currentTime = Carbon::now('Asia/Manila');
            if ($request->filled('recorded_at')) {
                try {
                    $recorded = Carbon::parse($request->recorded_at)->timezone('Asia/Manila');
                    if ($recorded->toDateString() === $today) {
                        $currentTime = $recorded;
                    }
                } catch (\Exception $e) {
                    // Ignore invalid recorded_at
                }
            }
            if ($currentTime->gt(Carbon::parse($today.' 21:00:00', 'Asia/Manila'))) {
                return back()->with('error', 'Lunch / break out is only allowed until 9:00 PM.');
            }

            $morningIn = Carbon::parse($attendance->time_in);
            if ($currentTime->lte($morningIn)) {
                return back()->with('error', 'Lunch / break out must be after your morning time-in.');
            }

            $attendance->lunch_break_out = $currentTime->toTimeString();

            $lunchSnapshotPath = null;
            if ($request->hasFile('verification_snapshot')) {
                $path = $request->file('verification_snapshot')->store('verification_snapshots', 'public');
                $lunchSnapshotPath = $path;
            }

            $attendance->save();
            $this->recordAttendanceEvent(
                $attendance,
                $studentId,
                'lunch_break_out',
                'out',
                Carbon::parse($today.' '.$attendance->lunch_break_out, 'Asia/Manila'),
                $this->captureSource($request),
                (string) ($request->input('verification_method') ?: 'face'),
                $lunchSnapshotPath,
                $this->captureMeta($request, [
                    'recorded_at_client' => $request->input('recorded_at'),
                    'confidence' => $request->input('verification_confidence'),
                ])
            );

            $verificationNote = $request->input('verification_method') === 'password' ? ' (password verification)' : (' with face verification.'.$this->confidenceSuffix($request));
            $policyWarning = $this->passwordFallbackPolicyNotice($student, $request, 'lunch_break_out');

            $redirect = back()->with('success', 'Lunch / break out recorded. This fills your DTR morning (A.M.) departure.'.$verificationNote);
            if ($policyWarning) {
                $redirect->with('warning', $policyWarning);
            }

            return $redirect;
        } catch (\Throwable $e) {
            Log::error('Lunch break-out failed', ['student_id' => Auth::guard('student')->id(), 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Unable to record lunch / break out. Please try again. If the problem persists, contact your coordinator.');
        }
    }

    public function submitManualRequest(StoreManualAttendanceRequest $request)
    {
        $student = Auth::guard('student')->user();
        if (! $student) {
            abort(401);
        }

        $data = $request->validated();
        if (! $this->hasAnyManualTime($data)) {
            return back()->with('error', 'Enter at least one time value so the coordinator has data to review.');
        }
        $sequenceError = $this->manualTimeOrderError(
            $data['time_in'] ?? null,
            $data['lunch_break_out'] ?? null,
            $data['afternoon_time_in'] ?? null,
            $data['time_out'] ?? null
        );
        if ($sequenceError) {
            return back()->with('error', $sequenceError);
        }

        $attendanceDate = Carbon::parse($data['attendance_date'], 'Asia/Manila')->startOfDay();

        if (Attendance::valid()->where('student_id', $student->id)->whereDate('date', $attendanceDate->toDateString())->exists()) {
            return back()->with('error', 'You already have an attendance record on that date. Ask your coordinator to use invalidation workflow if correction is needed.');
        }

        $pendingExisting = ManualAttendanceRequest::query()
            ->where('student_id', $student->id)
            ->whereDate('attendance_date', $attendanceDate->toDateString())
            ->where('status', ManualAttendanceRequest::STATUS_PENDING)
            ->first();
        if ($pendingExisting) {
            return back()->with('info', 'You already have a pending manual attendance request for that date.');
        }

        $requestRow = ManualAttendanceRequest::create([
            'student_id' => $student->id,
            'attendance_date' => $attendanceDate->toDateString(),
            'time_in' => $this->toStoredTime($data['time_in'] ?? null),
            'lunch_break_out' => $this->toStoredTime($data['lunch_break_out'] ?? null),
            'afternoon_time_in' => $this->toStoredTime($data['afternoon_time_in'] ?? null),
            'time_out' => $this->toStoredTime($data['time_out'] ?? null),
            'reason' => trim((string) $data['reason']),
            'status' => ManualAttendanceRequest::STATUS_PENDING,
        ]);

        AuditLog::create([
            'actor_type' => 'student',
            'actor_id' => $student->id,
            'action' => 'manual_attendance_request_submitted',
            'target_type' => 'manual_attendance_request',
            'target_id' => $requestRow->id,
            'details' => 'Student submitted manual attendance request.',
            'context' => [
                'attendance_date' => $requestRow->attendance_date ? Carbon::parse($requestRow->attendance_date)->format('Y-m-d') : null,
                'reason' => $requestRow->reason,
            ],
        ]);

        return back()->with('success', 'Manual attendance request submitted. It will only reflect after coordinator approval.');
    }

    public function coordinatorManualRequests(Request $request)
    {
        $coordinator = Auth::guard('coordinator')->user();
        if (! $coordinator) {
            abort(401);
        }

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

        $studentIds = \App\Models\Student::forCoordinator($coordinator)->pluck('id');
        $query = ManualAttendanceRequest::query()
            ->whereIn('student_id', $studentIds)
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

        $requests = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('coordinator.manual-attendance-requests', [
            'requests' => $requests,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function reviewManualRequest(ReviewManualAttendanceRequest $request, ManualAttendanceRequest $manualRequest)
    {
        $coordinator = Auth::guard('coordinator')->user();
        if (! $coordinator) {
            abort(401);
        }
        $redirect = redirect()->route('coordinator.manual.requests');

        $isVisible = \App\Models\Student::forCoordinator($coordinator)
            ->where('id', $manualRequest->student_id)
            ->exists();
        if (! $isVisible) {
            return $redirect->with('error', 'You do not have permission to review this request.');
        }
        if ($manualRequest->status !== ManualAttendanceRequest::STATUS_PENDING) {
            return $redirect->with('info', 'This request was already reviewed.');
        }

        $validated = $request->validated();
        $decision = $validated['decision'];
        $note = trim((string) ($validated['coordinator_note'] ?? ''));
        $result = $this->applyCoordinatorManualDecision($manualRequest, $coordinator, $decision, $note);
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }

        if ($decision === 'approve') {
            return $redirect->with('success', 'Manual request approved and attendance was posted.');
        }

        return $redirect->with('error', 'Manual request rejected.');
    }

    public function coordinatorBulkReviewManualRequests(Request $request)
    {
        $coordinator = Auth::guard('coordinator')->user();
        if (! $coordinator) {
            abort(401);
        }
        $redirect = redirect()->route('coordinator.manual.requests');
        $validated = $request->validate([
            'request_ids' => ['required', 'array', 'min:1'],
            'request_ids.*' => ['integer'],
            'decision' => ['required', 'in:approve,reject'],
            'coordinator_note' => ['nullable', 'string', 'max:1500'],
        ]);
        $decision = (string) $validated['decision'];
        $note = trim((string) ($validated['coordinator_note'] ?? ''));

        $studentIds = \App\Models\Student::forCoordinator($coordinator)->pluck('id');
        $targets = ManualAttendanceRequest::query()
            ->whereIn('id', $validated['request_ids'])
            ->whereIn('student_id', $studentIds)
            ->where('status', ManualAttendanceRequest::STATUS_PENDING)
            ->get();

        if ($targets->isEmpty()) {
            return $redirect->with('info', 'No eligible pending manual requests were selected.');
        }

        $approved = 0;
        $rejected = 0;
        $skipped = 0;
        foreach ($targets as $manualRequest) {
            $result = $this->applyCoordinatorManualDecision($manualRequest, $coordinator, $decision, $note, true);
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

    private function applyCoordinatorManualDecision(
        $manualRequest,
        $coordinator,
        string $decision,
        string $note,
        bool $skipOnInvalid = false
    ): bool|\Illuminate\Http\RedirectResponse {
        if ($decision === 'approve') {
            $sequenceError = $this->manualTimeOrderError(
                $manualRequest->time_in ? Carbon::parse($manualRequest->time_in)->format('H:i') : null,
                $manualRequest->lunch_break_out ? Carbon::parse($manualRequest->lunch_break_out)->format('H:i') : null,
                $manualRequest->afternoon_time_in ? Carbon::parse($manualRequest->afternoon_time_in)->format('H:i') : null,
                $manualRequest->time_out ? Carbon::parse($manualRequest->time_out)->format('H:i') : null
            );
            if ($sequenceError) {
                if ($skipOnInvalid) {
                    return false;
                }

                return redirect()->route('coordinator.manual.requests')->with('error', 'Cannot approve request due to invalid time order: '.$sequenceError);
            }

            $alreadyExists = Attendance::valid()
                ->where('student_id', $manualRequest->student_id)
                ->whereDate('date', Carbon::parse($manualRequest->attendance_date)->format('Y-m-d'))
                ->exists();
            if ($alreadyExists) {
                if ($skipOnInvalid) {
                    return false;
                }

                return redirect()->route('coordinator.manual.requests')->with('error', 'Attendance already exists for this date. Reject this request with a note instead.');
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
            $this->recordManualApprovalEvents($postedAttendance, $manualRequest, 'coordinator');

            $manualRequest->status = ManualAttendanceRequest::STATUS_APPROVED;
            $manualRequest->applied_at = now('Asia/Manila');
        } else {
            $manualRequest->status = ManualAttendanceRequest::STATUS_REJECTED;
        }

        $manualRequest->reviewed_by = $coordinator->id;
        $manualRequest->reviewed_at = now('Asia/Manila');
        $manualRequest->coordinator_note = $note !== '' ? $note : null;
        $manualRequest->save();

        AuditLog::create([
            'actor_type' => 'coordinator',
            'actor_id' => $coordinator->id,
            'action' => $decision === 'approve' ? 'manual_attendance_request_approved' : 'manual_attendance_request_rejected',
            'target_type' => 'manual_attendance_request',
            'target_id' => $manualRequest->id,
            'details' => $manualRequest->coordinator_note ?: 'No coordinator note provided.',
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

    private function hasAnyManualTime(array $data): bool
    {
        foreach (['time_in', 'lunch_break_out', 'afternoon_time_in', 'time_out'] as $field) {
            if (! empty($data[$field])) {
                return true;
            }
        }

        return false;
    }

    private function toStoredTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::createFromFormat('H:i', $value, 'Asia/Manila')->format('H:i:s');
    }

    private function manualTimeOrderError(?string $timeIn, ?string $lunchOut, ?string $afternoonIn, ?string $timeOut): ?string
    {
        $parse = function (?string $value): ?Carbon {
            return $value ? Carbon::createFromFormat('H:i', $value, 'Asia/Manila') : null;
        };
        $amIn = $parse($timeIn);
        $lunch = $parse($lunchOut);
        $pmIn = $parse($afternoonIn);
        $out = $parse($timeOut);
        $maxCutoff = Carbon::createFromTime(21, 0, 0, 'Asia/Manila');

        if ($lunch && ! $amIn) {
            return 'Lunch / break out requires Morning Time In.';
        }
        if ($lunch && $amIn && $lunch->lte($amIn)) {
            return 'Lunch / break out must be after Morning Time In.';
        }
        if ($pmIn && $pmIn->gt($maxCutoff)) {
            return 'Afternoon Time In is only allowed until 9:00 PM.';
        }
        if ($out && $out->gt($maxCutoff)) {
            return 'Time Out is only allowed until 9:00 PM.';
        }
        if ($pmIn && $lunch && $pmIn->lte($lunch)) {
            return 'Afternoon Time In must be after Lunch / break out.';
        }
        if ($out && $pmIn && $out->lte($pmIn)) {
            return 'Time Out must be after Afternoon Time In.';
        }
        if ($out && ! $pmIn && $amIn && $out->lte($amIn)) {
            return 'Time Out must be after Morning Time In.';
        }

        return null;
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

    private function runKioskAction(Request $request, callable $action)
    {
        $match = $this->resolveKioskFaceMatch($request);
        $kioskStudent = $match['student'] ?? null;
        if (! $kioskStudent) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => (string) ($match['message'] ?? 'Face not recognized. Please try again or contact coordinator/admin.'),
                ], 422);
            }

            return back()->with('error', (string) ($match['message'] ?? 'Face not recognized. Please try again or contact coordinator/admin.'));
        }

        $request->merge([
            'verification_method' => 'face',
            'kiosk_station_id' => (string) $request->input('kiosk_station_id', $request->query('station_id', 'station-1')),
            'kiosk_station_name' => (string) $request->input('kiosk_station_name', $request->query('station_name', 'Kiosk Station')),
            'kiosk_client_time' => (string) $request->input('kiosk_client_time', $request->input('recorded_at')),
        ]);

        Auth::guard('student')->onceUsingId((int) $kioskStudent->id);

        $response = $action();
        if (! $request->expectsJson()) {
            return $response;
        }

        $success = (string) $request->session()->get('success', '');
        $error = (string) $request->session()->get('error', '');
        $warning = (string) $request->session()->get('warning', '');

        if ($success !== '') {
            return response()->json([
                'ok' => true,
                'message' => $success,
            ]);
        }

        if ($error !== '') {
            return response()->json([
                'ok' => false,
                'message' => $error,
            ], 422);
        }

        if ($warning !== '') {
            return response()->json([
                'ok' => true,
                'message' => $warning,
                'warning' => true,
            ]);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Attendance processed.',
        ]);
    }

    private function resolveKioskFaceMatch(Request $request): ?array
    {
        if (! $request->filled('face_encoding')) {
            return ['message' => 'Face data missing. Please retry capture.'];
        }

        $provided = json_decode((string) $request->input('face_encoding'), true);
        if (! is_array($provided) || count($provided) !== FaceEncodingService::ENCODING_LENGTH) {
            return ['message' => 'Invalid face capture. Please face the camera and retry.'];
        }

        $bestStudentId = null;
        $bestDistance = INF;
        $secondBestDistance = INF;
        $threshold = (float) (config('services.face_same_person_threshold') ?? FaceEncodingService::SAME_PERSON_THRESHOLD);
        // Margin to avoid nearest-neighbor confusion across lookalike profiles.
        $ambiguityMargin = 0.015;

        $candidates = \App\Models\Student::query()
            ->verified()
            ->whereNotNull('face_encoding')
            ->get(['id', 'face_encoding']);

        foreach ($candidates as $candidate) {
            $stored = json_decode((string) $candidate->face_encoding, true);
            if (! is_array($stored) || count($stored) !== FaceEncodingService::ENCODING_LENGTH) {
                continue;
            }

            $distance = FaceEncodingService::distance($stored, $provided);
            if ($distance <= $threshold && $distance < $bestDistance) {
                $secondBestDistance = $bestDistance;
                $bestDistance = $distance;
                $bestStudentId = (int) $candidate->id;
                continue;
            }
            if ($distance <= $threshold && $distance < $secondBestDistance) {
                $secondBestDistance = $distance;
            }
        }

        if (! $bestStudentId) {
            return ['message' => 'Face not recognized. Please align your face and try again.'];
        }

        if ($secondBestDistance < INF && ($secondBestDistance - $bestDistance) < $ambiguityMargin) {
            return ['message' => 'Face match is ambiguous. Please face the camera directly and retry.'];
        }

        $student = \App\Models\Student::find($bestStudentId);
        if (! $student) {
            return ['message' => 'Matched profile is unavailable. Please contact administrator.'];
        }

        $confidence = max(0, min(100, (1 - ($bestDistance / $threshold)) * 100));

        return [
            'student' => $student,
            'distance' => $bestDistance,
            'confidence' => $confidence,
        ];
    }

    private function captureSource(Request $request): string
    {
        return $request->routeIs('kiosk.*') ? 'kiosk_station' : 'live_capture';
    }

    private function captureMeta(Request $request, array $meta = []): array
    {
        if ($request->routeIs('kiosk.*')) {
            $meta['kiosk_station_id'] = $request->input('kiosk_station_id');
            $meta['kiosk_station_name'] = $request->input('kiosk_station_name');
            $meta['kiosk_client_time'] = $request->input('kiosk_client_time');
        }

        return $meta;
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

    /**
     * When not using password verification, require a valid face encoding that matches the student's enrollment.
     * Previously, missing/invalid encodings skipped checks and attendance could be recorded without a real match.
     */
    private function rejectUnlessFaceMatchesStored(\App\Models\Student $student, Request $request): ?\Illuminate\Http\RedirectResponse
    {
        if (! $student->face_encoding) {
            return back()->with('error', 'No face is registered on this account. Enroll your face in Settings, or use “Verify with password” and select a reason.');
        }

        if (! $request->filled('face_encoding')) {
            return back()->with('error', 'Face verification data was missing. Use the camera flow again, or use password verification if allowed.');
        }

        $stored = json_decode($student->face_encoding, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($stored) || count($stored) !== FaceEncodingService::ENCODING_LENGTH) {
            return back()->with('error', 'Your enrolled face data is invalid. Please re-register your face in Settings.');
        }

        $provided = json_decode($request->input('face_encoding'), true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($provided) || count($provided) !== FaceEncodingService::ENCODING_LENGTH) {
            return back()->with('error', 'Invalid face capture from this device. Please try again.');
        }

        if (! FaceEncodingService::isSamePerson($stored, $provided)) {
            $distance = FaceEncodingService::distance($stored, $provided);
            Log::warning('Face attendance rejected: encoding mismatch', [
                'student_id' => $student->id,
                'distance' => round($distance, 4),
                'threshold' => (float) (config('services.face_same_person_threshold') ?? FaceEncodingService::SAME_PERSON_THRESHOLD),
            ]);

            return back()->with('error', 'Face verification failed: the face shown does not match the face enrolled for this account. Only the enrolled student may use face verification, or use password verification with a valid reason.');
        }

        return null;
    }

    /**
     * Confidence percentage is intentionally hidden from user-facing success text.
     */
    private function confidenceSuffix(Request $request): string
    {
        return '';
    }

    /**
     * Lightweight policy guard for repeated password-fallback use.
     * Logs fallback events and returns a coordinator-review warning when threshold is reached.
     */
    private function passwordFallbackPolicyNotice(\App\Models\Student $student, Request $request, string $action): ?string
    {
        if ($request->input('verification_method') !== 'password') {
            return null;
        }

        $reason = trim((string) $request->input('verification_reason', 'Unspecified'));
        AuditLog::create([
            'actor_type' => 'student',
            'actor_id' => $student->id,
            'action' => 'attendance_password_fallback_used',
            'target_type' => 'student',
            'target_id' => $student->id,
            'details' => 'Password fallback used for attendance verification.',
            'context' => [
                'action_type' => $action,
                'reason' => $reason,
                'student_no' => $student->student_no,
            ],
        ]);

        $windowDays = (int) config('dtr.password_fallback_review_window_days', 7);
        $threshold = (int) config('dtr.password_fallback_review_threshold', 3);
        $recentCount = AuditLog::query()
            ->where('actor_type', 'student')
            ->where('actor_id', $student->id)
            ->where('action', 'attendance_password_fallback_used')
            ->where('created_at', '>=', now()->subDays($windowDays))
            ->count();

        if ($recentCount >= $threshold) {
            return 'You have used password verification multiple times recently. Please request supervised face re-enrollment with your coordinator.';
        }

        return null;
    }

    /**
     * Verify face encoding for registration
     */
    public function verifyFaceEncoding(Request $request)
    {
        $request->validate([
            'face_encoding' => 'required|string',
        ]);

        $encoding = json_decode($request->face_encoding, true);

        if (! $encoding || ! is_array($encoding)) {
            return response()->json(['valid' => false, 'message' => 'Invalid face encoding']);
        }

        // Basic validation - encoding should have 128 dimensions (face-api.js default)
        if (count($encoding) !== 128) {
            return response()->json(['valid' => false, 'message' => 'Invalid encoding dimensions']);
        }

        return response()->json(['valid' => true, 'message' => 'Face encoding is valid']);
    }

    /**
     * Serve verification snapshot image. Authorized: student (own only) or coordinator (their program).
     */
    public function viewVerificationSnapshot(Request $request, Attendance $attendance, string $type)
    {
        $column = match ($type) {
            'morning' => 'verification_snapshot',
            'afternoon' => 'afternoon_verification_snapshot',
            'timeout' => 'timeout_verification_snapshot',
            default => null,
        };
        if (! $column || ! $attendance->{$column}) {
            abort(404);
        }
        $path = $attendance->{$column};

        if (Auth::guard('student')->check()) {
            if ($attendance->student_id !== Auth::guard('student')->id()) {
                abort(403);
            }
        } elseif (Auth::guard('coordinator')->check()) {
            $coordinator = Auth::guard('coordinator')->user();
            $allowed = \App\Models\Student::forCoordinator($coordinator)->where('id', $attendance->student_id)->exists();
            if (! $allowed) {
                abort(403);
            }
        } else {
            abort(401);
        }

        $fullPath = Storage::disk('public')->path($path);
        if (! is_file($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="verification-'.$type.'.jpg"',
        ]);
    }

    public function recentLogs(Request $request)
    {
        $student = Auth::guard('student')->user();
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

        $logs = collect();
        $weekLabel = '';
        $weekRange = $filter === 'week' ? WeekRangeFilter::parse($weekStartInput, $weekEndInput) : null;

        $start = null;
        $end = null;
        if ($filter === 'week' && $weekRange) {
            $start = Carbon::parse($weekRange['start_date']);
            $end = Carbon::parse($weekRange['end_date']);
            $weekLabel = $start->format('M j').' - '.$end->format('M j, Y');

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

        // Use term-specific hours from active term assignment
        $rendered = (float) $student->renderedHoursForAssignment($student->activeTermAssignment);
        $required = (float) $student->requiredHoursForAssignment($student->activeTermAssignment);
        $progressPct = $required > 0 ? min(100, round(100 * $rendered / $required, 1)) : 0;
        $remaining = max(0, $required - $rendered);
        $termSummary = $this->buildStudentTermSummary($student);

        return view('student.recent-logs', compact(
            'logs', 'rendered', 'required', 'progressPct', 'remaining',
            'filter', 'selectedMonth', 'weekInput', 'weekStartInput', 'weekEndInput', 'weekLabel', 'termSummary'
        ));
    }

    public function downloadRecentLogs(Request $request)
    {
        $student = Auth::guard('student')->user();
        $studentId = $student->id;

        $filter = $request->input('filter', 'month');
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $weekInput = $request->input('week');
        $weekStartInput = $request->input('week_start');
        $weekEndInput = $request->input('week_end');
        if ($filter === 'week') {
            [$weekStartInput, $weekEndInput] = WeekRangeFilter::defaultInputs($weekStartInput, $weekEndInput, $weekInput);
        }

        $logs = collect();
        $periodLabel = '';
        $weekRange = $filter === 'week' ? WeekRangeFilter::parse($weekStartInput, $weekEndInput) : null;

        if ($filter === 'week' && $weekRange) {
            $start = Carbon::parse($weekRange['start_date']);
            $end = Carbon::parse($weekRange['end_date']);
            $periodLabel = $start->format('F d, Y').' to '.$end->format('F d, Y');

            $logs = Attendance::valid()->where('student_id', $studentId)
                ->whereBetween('date', [$weekRange['start_date'], $weekRange['end_date']])
                ->orderBy('date')
                ->get();
        } else {
            $filter = 'month';
            $parts = explode('-', $selectedMonth);
            $year = (int) ($parts[0] ?? now()->format('Y'));
            $month = (int) ($parts[1] ?? now()->format('m'));
            $periodLabel = Carbon::createFromDate($year, $month, 1)->format('F Y');

            $logs = Attendance::valid()->where('student_id', $studentId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->orderBy('date')
                ->get();
        }

        $rows = [];
        $logDates = $logs->pluck('date')
            ->filter()
            ->map(fn ($value) => Carbon::parse($value)->format('Y-m-d'))
            ->unique()
            ->values();
        $manualRequestLunchDates = collect();
        $passwordFallbackLunchDates = collect();

        if ($logDates->isNotEmpty()) {
            $manualRequestLunchDates = ManualAttendanceRequest::query()
                ->where('student_id', $studentId)
                ->where('status', ManualAttendanceRequest::STATUS_APPROVED)
                ->whereNotNull('lunch_break_out')
                ->whereIn('attendance_date', $logDates)
                ->pluck('attendance_date')
                ->map(fn ($value) => Carbon::parse($value)->format('Y-m-d'))
                ->unique()
                ->values();

            $fallbackEvents = AuditLog::query()
                ->where('actor_type', 'student')
                ->where('actor_id', $studentId)
                ->where('action', 'attendance_password_fallback_used')
                ->whereBetween('created_at', [
                    Carbon::parse($logDates->min(), 'Asia/Manila')->startOfDay(),
                    Carbon::parse($logDates->max(), 'Asia/Manila')->endOfDay(),
                ])
                ->get(['created_at', 'context']);

            $passwordFallbackLunchDates = $fallbackEvents
                ->filter(function ($event) {
                    $context = is_array($event->context) ? $event->context : [];

                    return ($context['action_type'] ?? null) === 'lunch_break_out';
                })
                ->map(fn ($event) => Carbon::parse($event->created_at, 'Asia/Manila')->format('Y-m-d'))
                ->unique()
                ->values();
        }

        foreach ($logs as $log) {
            $date = Carbon::parse($log->date);
            $entryTypeFor = static fn (?string $snapshotPath): string => ! empty($snapshotPath)
                ? 'Facial Recognition'
                : 'Manual';

            if ($log->time_in) {
                $rows[] = [
                    'record_id' => $log->id.'-IN',
                    'day' => $date->format('D'),
                    'date' => $date->format('m/d/Y'),
                    'time' => Carbon::parse($log->time_in)->format('h:i A'),
                    'mode' => 'In',
                    'entry_type' => $entryTypeFor($log->verification_snapshot),
                    'reference' => 'WEB-STUDENT',
                ];
            }
            if ($log->lunch_break_out) {
                $dateKey = $date->format('Y-m-d');
                $isManualLunch = $manualRequestLunchDates->contains($dateKey)
                    || $passwordFallbackLunchDates->contains($dateKey);
                $rows[] = [
                    'record_id' => $log->id.'-LO',
                    'day' => $date->format('D'),
                    'date' => $date->format('m/d/Y'),
                    'time' => Carbon::parse($log->lunch_break_out)->format('h:i A'),
                    'mode' => 'Out',
                    'entry_type' => $isManualLunch ? 'Manual' : 'Facial Recognition',
                    'reference' => 'WEB-STUDENT',
                ];
            }
            if ($log->afternoon_time_in) {
                $rows[] = [
                    'record_id' => $log->id.'-AI',
                    'day' => $date->format('D'),
                    'date' => $date->format('m/d/Y'),
                    'time' => Carbon::parse($log->afternoon_time_in)->format('h:i A'),
                    'mode' => 'In',
                    'entry_type' => $entryTypeFor($log->afternoon_verification_snapshot),
                    'reference' => 'WEB-STUDENT',
                ];
            }
            if ($log->time_out) {
                $rows[] = [
                    'record_id' => $log->id.'-TO',
                    'day' => $date->format('D'),
                    'date' => $date->format('m/d/Y'),
                    'time' => Carbon::parse($log->time_out)->format('h:i A'),
                    'mode' => 'Out',
                    'entry_type' => $entryTypeFor($log->timeout_verification_snapshot),
                    'reference' => 'WEB-STUDENT',
                ];
            }
        }

        $pdf = Pdf::loadView('reports.student-time-logs', [
            'student' => $student,
            'periodLabel' => $periodLabel,
            'rows' => $rows,
        ])->setPaper('A4', 'portrait');

        $safePeriod = str_replace([' ', ',', '/'], ['_', '', '-'], $periodLabel);
        $filename = 'TIME_LOGS_'.$student->student_no.'_'.$safePeriod.'.pdf';

        return $pdf->download($filename);
    }

    private function buildStudentTermSummary($student): array
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
                'note' => 'This is the term currently used for your attendance and coordinator assignment.',
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
            'note' => 'You do not have an active OJT term yet.',
            'history' => collect(),
        ];
    }

    public function coordinatorLogs(Request $request)
    {
        $coordinator = Auth::guard('coordinator')->user();
        $search = $request->filled('q') ? trim($request->q) : '';
        $filter = $request->input('filter', 'month');
        $month = $request->input('month', now()->format('Y-m'));
        $weekInput = $request->input('week');
        $weekStartInput = $request->input('week_start');
        $weekEndInput = $request->input('week_end');
        if ($filter === 'week') {
            [$weekStartInput, $weekEndInput] = WeekRangeFilter::defaultInputs($weekStartInput, $weekEndInput, $weekInput);
        }

        $studentQuery = \App\Models\Student::forCoordinator($coordinator)->verified();
        $viewStudent = null;
        $studentIdParam = $request->input('student_id');
        if ($studentIdParam && is_numeric($studentIdParam)) {
            $viewStudent = $studentQuery->clone()->where('id', (int) $studentIdParam)->first();
            if ($viewStudent) {
                $studentQuery->where('id', $viewStudent->id);
            }
        }
        if ($search !== '' && ! $viewStudent) {
            $trim = trim($search);
            if (StudentSearch::usesWildcardSyntax($search)) {
                $term = StudentSearch::buildWildcardTerm($search);
                $studentQuery->where(function ($q) use ($term) {
                    StudentSearch::applyAttendanceLogsLike($q, $term);
                });
            } else {
                $term = StudentSearch::buildWildcardTerm($search);
                $studentQuery->where(function ($q) use ($term, $trim) {
                    StudentSearch::applyBroadNameHints($q, $trim, function ($inner) use ($term) {
                        StudentSearch::applyAttendanceLogsLike($inner, $term);
                    });
                });
                $matchedIds = StudentSearch::refinePlainSearch(
                    $studentQuery->get(),
                    $search,
                    false
                )->pluck('id');
                $studentQuery = \App\Models\Student::forCoordinator($coordinator)->verified()->whereIn('id', $matchedIds);
            }
        }
        $studentIds = $studentQuery->pluck('id');
        $totalStudents = \App\Models\Student::forCoordinator($coordinator)->verified()->count();

        $logs = collect();
        $lateCount = 0;
        $weekStart = null;
        $weekEnd = null;
        $weekLabel = '';
        $weekRange = $filter === 'week' ? WeekRangeFilter::parse($weekStartInput, $weekEndInput) : null;
        $start = null;
        $end = null;
        if ($weekRange) {
            $start = Carbon::parse($weekRange['start_date']);
            $end = Carbon::parse($weekRange['end_date']);
        }

        if ($filter === 'week' && $weekRange) {
            $weekLabel = $start->format('M j').' - '.$end->format('M j, Y');

            $start = Carbon::parse($weekRange['start_date']);
            $end = Carbon::parse($weekRange['end_date']);
            $weekStart = $weekRange['start_date'];
            $weekEnd = $weekRange['end_date'];
            $logs = \App\Models\Attendance::valid()->with('student')
                ->whereIn('student_id', $studentIds)
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->orderBy('date', 'desc')
                ->get();

            $lateCount = \App\Models\Attendance::valid()->whereIn('student_id', $studentIds)
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->where(function ($q) {
                    $q->where('is_late', true)->orWhere('afternoon_is_late', true);
                })
                ->count();
            $weekLabel = $weekRange['label'];
        } else {
            $filter = 'month';
            [$year, $monthNum] = explode('-', $month);
            $logs = \App\Models\Attendance::valid()->with('student')
                ->whereIn('student_id', $studentIds)
                ->whereYear('date', $year)
                ->whereMonth('date', $monthNum)
                ->orderBy('date', 'desc')
                ->get();

            $lateCount = \App\Models\Attendance::valid()->whereIn('student_id', $studentIds)
                ->whereYear('date', $year)
                ->whereMonth('date', $monthNum)
                ->where(function ($q) {
                    $q->where('is_late', true)->orWhere('afternoon_is_late', true);
                })
                ->count();
        }

        $presentToday = \App\Models\Attendance::valid()->whereIn('student_id', $studentIds)
            ->where('date', now()->format('Y-m-d'))
            ->distinct('student_id')
            ->count('student_id');
        $absentToday = $totalStudents - $presentToday;

        // Unique students with logs in this period (for the button list when not viewing one student)
        $studentsWithLogs = $logs->pluck('student')->unique('id')->filter()->sortBy('name')->values();

        return view('coordinator.attendance-logs', compact(
            'logs', 'totalStudents', 'presentToday', 'absentToday', 'month', 'search',
            'filter', 'weekInput', 'weekStartInput', 'weekEndInput', 'weekStart', 'weekEnd', 'weekLabel', 'lateCount', 'viewStudent', 'studentsWithLogs'
        ));
    }

    public function attendanceAnalytics(\Illuminate\Http\Request $request)
    {
        $coordinator = Auth::guard('coordinator')->user();
        $studentIds = \App\Models\Student::forCoordinator($coordinator)->verified()->pluck('id');
        $totalStudents = $studentIds->count();

        $monthlyAnalytics = [];
        for ($i = 0; $i < 12; $i++) {
            $d = now()->subMonths($i);
            $presentDays = \App\Models\Attendance::valid()->whereIn('student_id', $studentIds)
                ->whereYear('date', $d->year)
                ->whereMonth('date', $d->month)
                ->count();
            $uniquePresent = \App\Models\Attendance::valid()->whereIn('student_id', $studentIds)
                ->whereYear('date', $d->year)
                ->whereMonth('date', $d->month)
                ->distinct('student_id')
                ->count('student_id');
            $absentStudents = max(0, $totalStudents - $uniquePresent);
            $monthlyAnalytics[] = [
                'label' => $d->format('M Y'),
                'key' => $d->format('Y-m'),
                'present_days' => $presentDays,
                'unique_present' => $uniquePresent,
                'absent_students' => $absentStudents,
            ];
        }

        // Compare 2 or 3 months: ?compare[]=2025-01&compare[]=2025-02
        $compareInput = $request->input('compare', []);
        $compareInput = is_array($compareInput) ? $compareInput : [$compareInput];
        $compareInput = array_values(array_filter(array_unique($compareInput)));
        $comparisonMonths = [];
        $compareValues = []; // for repopulating the form

        if (count($compareInput) >= 2 && count($compareInput) <= 3) {
            foreach ($compareInput as $ym) {
                if (! preg_match('/^\d{4}-\d{2}$/', $ym)) {
                    continue;
                }
                [$y, $m] = explode('-', $ym);
                $presentDays = \App\Models\Attendance::valid()->whereIn('student_id', $studentIds)
                    ->whereYear('date', $y)
                    ->whereMonth('date', (int) $m)
                    ->count();
                $uniquePresent = \App\Models\Attendance::valid()->whereIn('student_id', $studentIds)
                    ->whereYear('date', $y)
                    ->whereMonth('date', (int) $m)
                    ->distinct('student_id')
                    ->count('student_id');
                $absentStudents = max(0, $totalStudents - $uniquePresent);
                $d = \Carbon\Carbon::createFromDate((int) $y, (int) $m, 1);
                $comparisonMonths[] = [
                    'label' => $d->format('M Y'),
                    'key' => $ym,
                    'present_days' => $presentDays,
                    'unique_present' => $uniquePresent,
                    'absent_students' => $absentStudents,
                    'rate' => $totalStudents > 0 ? round(100 * $uniquePresent / $totalStudents) : 0,
                ];
                $compareValues[] = $ym;
            }
        }

        return view('coordinator.attendance-analytics', compact('monthlyAnalytics', 'totalStudents', 'comparisonMonths', 'compareValues'));
    }

    public function invalidateAttendance(InvalidateAttendanceRequest $request, Attendance $attendance)
    {
        $coordinator = Auth::guard('coordinator')->user();
        if (! $coordinator) {
            abort(401);
        }

        $isVisible = \App\Models\Student::forCoordinator($coordinator)
            ->where('id', $attendance->student_id)
            ->exists();

        if (! $isVisible) {
            return back()->with('error', 'You do not have permission to invalidate this attendance record.');
        }

        if ($attendance->invalidation_status === 'requested') {
            return back()->with('info', 'An invalidation request for this attendance is already pending admin review.');
        }
        if ($attendance->is_invalid) {
            return back()->with('info', 'This attendance was already invalidated and approved.');
        }

        $attendance->is_invalid = false;
        $attendance->invalidation_status = 'requested';
        $attendance->invalidation_requested_at = now('Asia/Manila');
        $attendance->invalidated_by = $coordinator->id;
        $attendance->invalidation_reason = trim((string) $request->input('reason'));
        $attendance->invalidation_reviewed_by = null;
        $attendance->invalidation_reviewed_at = null;
        $attendance->invalidation_review_note = null;
        $attendance->save();

        AuditLog::create([
            'actor_type' => 'coordinator',
            'actor_id' => $coordinator->id,
            'action' => 'attendance_invalidation_requested',
            'target_type' => 'attendance',
            'target_id' => $attendance->id,
            'details' => $attendance->invalidation_reason,
            'context' => [
                'student_id' => $attendance->student_id,
                'date' => $attendance->date,
            ],
        ]);

        return back()->with('success', 'Invalidation request submitted. It will be applied after admin approval.');
    }
}
