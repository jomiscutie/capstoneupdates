<?php

namespace App\Http\Controllers;

use App\Http\Requests\TimeInRequest;
use App\Http\Requests\TimeOutRequest;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
        if (!$student || !$student->isVerified()) {
            return back()->with('error', 'Your account must be verified by your coordinator before you can record attendance. Please contact your OJT coordinator.');
        }

        // Alternative: password verification when camera is unavailable
        if ($request->input('verification_method') === 'password') {
            if (!$request->filled('password')) {
                return back()->with('error', 'Please enter your password to verify your identity.');
            }
            if (!Hash::check($request->password, $student->password)) {
                return back()->with('error', 'Incorrect password. Please try again.');
            }
            // Password valid; skip face verification
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
                    return back()->with('error', "Face verification failed. Match confidence: " . round($confidence) . "%. Try better lighting or look straight at the camera.");
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
        if ($timeHour >= 12 && !$isAfternoon) {
            // Force afternoon if hour is 12 or greater (safety check)
            $isAfternoon = true;
        }
        if ($timeHour < 12 && $isAfternoon) {
            // Force morning if hour is less than 12 (safety check)
            $isAfternoon = false;
        }
        
        // Get or create attendance record
        $attendance = Attendance::firstOrNew([
            'student_id' => $studentId,
            'date' => $today
        ]);
        
        if ($isAfternoon) {
            // AFTERNOON TIME-IN: 12:00 PM (12:00:00) onwards
            // Examples: 12:00 PM, 1:00 PM, 2:00 PM, 3:00 PM, etc. ALL go here
            
            if ($attendance->afternoon_time_in) {
                return back()->with('error', 'You have already timed in for the afternoon session today.');
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
            
            // Calculate lateness based on 1:00 PM cutoff
            $expectedTimeIn = Carbon::createFromTime(13, 0, 0, 'Asia/Manila'); // 1:00 PM
            $isLate = $currentTime->gt($expectedTimeIn);
            $lateMinutes = null;
            
            if ($isLate) {
                // Ensure positive integer value for late minutes
                $lateMinutes = abs((int) $expectedTimeIn->diffInMinutes($currentTime, false));
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
            
            $verificationNote = $request->input('verification_method') === 'password' ? ' (password verification)' : (' with face verification. ✓' . $this->confidenceSuffix($request));
            $message = $isLate 
                ? "Afternoon Time In recorded successfully. ⚠️ You are {$lateMinutes} minute(s) late." . ($request->input('verification_method') === 'password' ? ' (password verification)' : $this->confidenceSuffix($request))
                : 'Afternoon Time In recorded successfully' . $verificationNote;
        } else {
            // MORNING TIME-IN: Before 12:00 PM (00:00:00 to 11:59:59)
            // Examples: 6:00 AM, 7:00 AM, 8:00 AM, 9:00 AM, 10:00 AM, 11:00 AM, etc.
            
            if ($attendance->time_in) {
                return back()->with('error', 'You have already timed in for the morning session today.');
            }
            
            // CRITICAL SAFEGUARD: Prevent morning time-in if it's 12:00 PM or later
            // This ensures 2 PM CANNOT be saved to morning time_in
            if ($currentTime->gte($noon)) {
                return back()->with('error', 'It is already past 12:00 PM. Please use afternoon time-in instead.');
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
            
            $verificationNote = $request->input('verification_method') === 'password' ? ' (password verification)' : (' with face verification. ✓' . $this->confidenceSuffix($request));
            $message = $isLate 
                ? "Morning Time In recorded successfully. ⚠️ You are {$lateMinutes} minute(s) late." . ($request->input('verification_method') === 'password' ? ' (password verification)' : $this->confidenceSuffix($request))
                : 'Morning Time In recorded successfully' . $verificationNote;
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
        if (!$student || !$student->isVerified()) {
            return back()->with('error', 'Your account must be verified by your coordinator before you can record attendance. Please contact your OJT coordinator.');
        }

        // Alternative: password verification when camera is unavailable
        if ($request->input('verification_method') === 'password') {
            if (!$request->filled('password')) {
                return back()->with('error', 'Please enter your password to verify your identity.');
            }
            if (!Hash::check($request->password, $student->password)) {
                return back()->with('error', 'Incorrect password. Please try again.');
            }
            // Password valid; skip face verification
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
                    return back()->with('error', "Face verification failed. Match confidence: " . round($confidence) . "%. Try better lighting or look straight at the camera.");
                }
            }
        }

        $attendance = Attendance::where('student_id', $studentId)
            ->where('date', $today)
            ->first();

        // Check if student has at least one time-in (morning or afternoon)
        if (!$attendance || (!$attendance->time_in && !$attendance->afternoon_time_in)) {
            return back()->with('error', 'You must time in (morning or afternoon) before timing out.');
        }

        if ($attendance->time_out) {
            return back()->with('error', 'You have already timed out today.');
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
        $attendance->time_out = $currentTime->toTimeString();

        // Calculate total hours rendered (morning + afternoon)
        $totalMinutes = 0;
        
        // Morning hours: from morning time-in to time-out (if morning time-in exists)
        if ($attendance->time_in) {
            $morningIn = Carbon::parse($attendance->time_in);
            $timeOut = Carbon::parse($attendance->time_out);
            
            // If there's an afternoon time-in, only count morning hours until afternoon time-in
            // Otherwise, count from morning time-in to time-out
            if ($attendance->afternoon_time_in) {
                $afternoonIn = Carbon::parse($attendance->afternoon_time_in);
                // Count morning hours only up to afternoon time-in (or time-out if earlier)
                $morningEnd = $afternoonIn->lt($timeOut) ? $afternoonIn : $timeOut;
                if ($morningEnd->gt($morningIn)) {
                    $totalMinutes += abs($morningEnd->diffInMinutes($morningIn));
                }
            } else {
                // No afternoon time-in, count full morning to time-out
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

        $attendance->save();

        $verificationNote = $request->input('verification_method') === 'password' ? ' (password verification)' : (' with face verification.' . $this->confidenceSuffix($request));
            return back()->with('success', 'Time Out recorded successfully' . $verificationNote);
        } catch (\Throwable $e) {
            Log::error('Time-out failed', ['student_id' => Auth::guard('student')->id(), 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Unable to record time-out. Please try again. If the problem persists, contact your coordinator.');
        }
    }

    /**
     * Optional suffix for success message: " — 94% match" when verification_confidence is sent.
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
        return " — {$pct}% match";
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
        
        if (!$encoding || !is_array($encoding)) {
            return response()->json(['valid' => false, 'message' => 'Invalid face encoding']);
        }

        // Basic validation - encoding should have 128 dimensions (face-api.js default)
        if (count($encoding) !== 128) {
            return response()->json(['valid' => false, 'message' => 'Invalid encoding dimensions']);
        }

        return response()->json(['valid' => true, 'message' => 'Face encoding is valid']);
    }

    public function recentLogs()
    {
        $studentId = Auth::guard('student')->id();

        // Get last 7 days of attendance, latest first
        $logs = Attendance::where('student_id', $studentId)
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        return view('student.recent-logs', compact('logs'));
    }

    public function coordinatorLogs(Request $request)
    {
        $coordinator = Auth::guard('coordinator')->user();

        // Month filter
        $month = $request->input('month', now()->format('Y-m'));
        [$year, $monthNum] = explode('-', $month);

        $studentIds = \App\Models\Student::forCoordinator($coordinator)->verified()->pluck('id');

        $logs = \App\Models\Attendance::with('student')
            ->whereIn('student_id', $studentIds)
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->orderBy('date', 'desc')
            ->get();

        $totalStudents = $studentIds->count();
        $presentToday = \App\Models\Attendance::whereIn('student_id', $studentIds)
            ->where('date', now()->format('Y-m-d'))
            ->distinct('student_id')
            ->count('student_id');
        $absentToday = $totalStudents - $presentToday;

        // Count late arrivals for the selected month (both morning and afternoon)
        $lateArrivalsMonth = \App\Models\Attendance::whereIn('student_id', $studentIds)
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->where(function($query) {
                $query->where('is_late', true)
                      ->orWhere('afternoon_is_late', true);
            })
            ->count();

        return view('coordinator.attendance-logs', compact('logs', 'totalStudents', 'presentToday', 'absentToday', 'month', 'lateArrivalsMonth'));
    }
}
