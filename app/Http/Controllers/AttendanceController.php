<?php

namespace App\Http\Controllers;

use App\Http\Requests\TimeInRequest;
use App\Http\Requests\TimeOutRequest;
use App\Http\Requests\InvalidateAttendanceRequest;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\StudentTermAssignment;
use App\Support\StudentSearch;
use App\Support\WeekRangeFilter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
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

            // Password fallback is allowed only as an explicit exception.
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
            } elseif ($request->filled('face_encoding') && $student->face_encoding) {
                $storedEncoding = json_decode($student->face_encoding, true);
                $providedEncoding = json_decode($request->face_encoding, true);
                if ($storedEncoding && $providedEncoding) {
                    $similarity = $this->calculateFaceSimilarity($storedEncoding, $providedEncoding);
                    $threshold = 0.6;
                    if ($similarity > 0.8) {
                        return back()->with('error', 'Face verification failed. The detected face does not match your registered face. Please ensure you are using your own account.');
                    }
                    if ($similarity > $threshold) {
                        $confidence = max(0, min(100, (1 - ($similarity / $threshold)) * 100));

                        return back()->with('error', 'Face verification failed. Match confidence: '.round($confidence).'%. Try better lighting or look straight at the camera.');
                    }
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

            // CRITICAL: Determine if it's morning (before 12:00 PM) or afternoon (12:00 PM onwards)
            // Use explicit time comparison for accuracy - this ensures 2 PM goes to afternoon
            $noon = Carbon::createFromTime(12, 0, 0, 'Asia/Manila');
            $isAfternoon = $currentTime->gte($noon); // Greater than or equal to 12:00 PM (12:00:00 and later)

            // VALIDATION: Double-check the time string to ensure it matches the period
            $timeHour = (int) $currentTime->format('H'); // Get hour in 24-hour format (0-23)
            if ($timeHour >= 12 && ! $isAfternoon) {
                // Force afternoon if hour is 12 or greater (safety check)
                $isAfternoon = true;
            }
            if ($timeHour < 12 && $isAfternoon) {
                // Force morning if hour is less than 12 (safety check)
                $isAfternoon = false;
            }

            // Get or create attendance record
            $attendance = Attendance::valid()->firstOrNew([
                'student_id' => $studentId,
                'date' => $today,
            ]);

            if ($isAfternoon) {
                // AFTERNOON TIME-IN: 12:00 PM (12:00:00) onwards
                // Examples: 12:00 PM, 1:00 PM, 2:00 PM, 3:00 PM, etc. ALL go here

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

                // Afternoon return: on time through 1:00 PM (13:00–13:00:59). Late from 1:01 PM onward.
                $deadline1300 = Carbon::parse($today.' 13:00:00', 'Asia/Manila');
                $hm = ((int) $currentTime->format('H')) * 60 + (int) $currentTime->format('i');
                $isLate = $hm > (13 * 60); // strictly after minute 13:00
                $lateMinutes = null;
                if ($isLate) {
                    $lateMinutes = abs((int) $deadline1300->diffInMinutes($currentTime, false));
                }

                // FINAL VALIDATION: Ensure the time string hour is >= 12 before saving
                $savedTimeHour = (int) Carbon::parse($timeInString)->format('H');
                if ($savedTimeHour < 12) {
                    return back()->with('error', 'System error: Time validation failed. Please try again.');
                }

                // Save to AFTERNOON time-in field (NOT morning)
                $attendance->afternoon_time_in = $timeInString;
                $attendance->afternoon_is_late = $isLate;
                $attendance->afternoon_late_minutes = $lateMinutes;

                $verificationNote = $request->input('verification_method') === 'password'
                    ? ' (password verification)'
                    : (' with face verification.'.$this->confidenceSuffix($request));
                $message = $isLate
                    ? "Afternoon Time In recorded successfully. You are {$lateMinutes} minute(s) late.".($request->input('verification_method') === 'password' ? ' (password verification)' : $this->confidenceSuffix($request))
                    : 'Afternoon Time In recorded successfully'.$verificationNote;
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

                // Calculate lateness based on 8:00 AM cutoff
                $expectedTimeIn = Carbon::createFromTime(8, 0, 0, 'Asia/Manila'); // 8:00 AM
                $isLate = $currentTime->gt($expectedTimeIn);
                $lateMinutes = null;

                if ($isLate) {
                    // Ensure positive integer value for late minutes
                    $lateMinutes = abs((int) $expectedTimeIn->diffInMinutes($currentTime, false));
                }

                // FINAL VALIDATION: Ensure the time string hour is < 12 before saving
                $savedTimeHour = (int) Carbon::parse($timeInString)->format('H');
                if ($savedTimeHour >= 12) {
                    return back()->with('error', 'System error: Cannot save afternoon time to morning field. Please try again.');
                }

                // Save to MORNING time-in field (NOT afternoon)
                $attendance->time_in = $timeInString;
                $attendance->is_late = $isLate;
                $attendance->late_minutes = $lateMinutes;

                $verificationNote = $request->input('verification_method') === 'password'
                    ? ' (password verification)'
                    : (' with face verification.'.$this->confidenceSuffix($request));
                $message = $isLate
                    ? "Morning Time In recorded successfully. You are {$lateMinutes} minute(s) late.".($request->input('verification_method') === 'password' ? ' (password verification)' : $this->confidenceSuffix($request))
                    : 'Morning Time In recorded successfully'.$verificationNote;
            }

            if ($request->hasFile('verification_snapshot')) {
                $path = $request->file('verification_snapshot')->store('verification_snapshots', 'public');
                if ($isAfternoon) {
                    $attendance->afternoon_verification_snapshot = $path;
                } else {
                    $attendance->verification_snapshot = $path;
                }
            }

            $attendance->save();

            return back()->with($isLate ? 'warning' : 'success', $message);
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

            // Password fallback is allowed only as an explicit exception.
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
            } elseif ($request->filled('face_encoding') && $student->face_encoding) {
                $storedEncoding = json_decode($student->face_encoding, true);
                $providedEncoding = json_decode($request->face_encoding, true);
                if ($storedEncoding && $providedEncoding) {
                    $similarity = $this->calculateFaceSimilarity($storedEncoding, $providedEncoding);
                    $threshold = 0.6;
                    if ($similarity > 0.8) {
                        return back()->with('error', 'Face verification failed. The detected face does not match your registered face. Please ensure you are using your own account.');
                    }
                    if ($similarity > $threshold) {
                        $confidence = max(0, min(100, (1 - ($similarity / $threshold)) * 100));

                        return back()->with('error', 'Face verification failed. Match confidence: '.round($confidence).'%. Try better lighting or look straight at the camera.');
                    }
                }
            }

            $attendance = Attendance::valid()->where('student_id', $studentId)
                ->where('date', $today)
                ->first();

            // Check if student has at least one time-in (morning or afternoon)
            if (! $attendance || (! $attendance->time_in && ! $attendance->afternoon_time_in)) {
                return back()->with('error', 'You must record a time-in (morning or afternoon) before you can time out. No time-in on file for today.')
                    ->with('error_type', 'no_time_in');
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

            if ($request->hasFile('verification_snapshot')) {
                $path = $request->file('verification_snapshot')->store('verification_snapshots', 'public');
                $attendance->timeout_verification_snapshot = $path;
            }

            $attendance->save();

            $verificationNote = $request->input('verification_method') === 'password' ? ' (password verification)' : (' with face verification.'.$this->confidenceSuffix($request));

            return back()->with('success', 'Time Out recorded successfully'.$verificationNote);
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
            } elseif ($request->filled('face_encoding') && $student->face_encoding) {
                $storedEncoding = json_decode($student->face_encoding, true);
                $providedEncoding = json_decode($request->face_encoding, true);
                if ($storedEncoding && $providedEncoding) {
                    $similarity = $this->calculateFaceSimilarity($storedEncoding, $providedEncoding);
                    $threshold = 0.6;
                    if ($similarity > 0.8) {
                        return back()->with('error', 'Face verification failed. The detected face does not match your registered face. Please ensure you are using your own account.');
                    }
                    if ($similarity > $threshold) {
                        $confidence = max(0, min(100, (1 - ($similarity / $threshold)) * 100));

                        return back()->with('error', 'Face verification failed. Match confidence: '.round($confidence).'%. Try better lighting or look straight at the camera.');
                    }
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

            $morningIn = Carbon::parse($attendance->time_in);
            if ($currentTime->lte($morningIn)) {
                return back()->with('error', 'Lunch / break out must be after your morning time-in.');
            }

            $attendance->lunch_break_out = $currentTime->toTimeString();
            $attendance->save();

            $verificationNote = $request->input('verification_method') === 'password' ? ' (password verification)' : (' with face verification.'.$this->confidenceSuffix($request));

            return back()->with('success', 'Lunch / break out recorded. This fills your DTR morning (A.M.) departure.'.$verificationNote);
        } catch (\Throwable $e) {
            Log::error('Lunch break-out failed', ['student_id' => Auth::guard('student')->id(), 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Unable to record lunch / break out. Please try again. If the problem persists, contact your coordinator.');
        }
    }

    /**
     * Optional suffix for success message: " - 94% match" when verification_confidence is sent.
     */
    private function confidenceSuffix(Request $request): string
    {
        if (! $request->filled('verification_confidence')) {
            return '';
        }
        $pct = (int) round((float) $request->verification_confidence);
        if ($pct < 0 || $pct > 100) {
            return '';
        }

        return " - {$pct}% match";
    }

    /**
     * Calculate face similarity using Euclidean distance
     * Returns distance (lower = more similar)
     * Enhanced with additional validation
     */
    private function calculateFaceSimilarity(array $encoding1, array $encoding2): float
    {
        if (count($encoding1) !== count($encoding2)) {
            return 999; // Very different if dimensions don't match
        }

        // Validate encoding dimensions (should be 128 for face-api.js)
        if (count($encoding1) !== 128 || count($encoding2) !== 128) {
            return 999; // Invalid encoding
        }

        $sum = 0;
        for ($i = 0; $i < count($encoding1); $i++) {
            $diff = $encoding1[$i] - $encoding2[$i];
            $sum += $diff * $diff;
        }

        $distance = sqrt($sum);

        // Additional validation: Check for suspicious patterns
        // If all values are identical or very close, might be a spoof attempt
        $variance = 0;
        foreach ($encoding1 as $val) {
            $variance += abs($val);
        }
        $avgVariance = $variance / count($encoding1);

        // If variance is too low, encoding might be invalid
        if ($avgVariance < 0.001) {
            return 999; // Invalid encoding pattern
        }

        return $distance;
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
