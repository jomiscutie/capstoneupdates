<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\OfficeAssignmentRequest;
use App\Models\Student;
use App\Support\Services\FaceEncodingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class StudentSettingsController extends Controller
{
    /**
     * Student settings: change password and other account configurations.
     */
    public function index()
    {
        $student = Auth::guard('student')->user();
        $latestOfficeRequest = $student
            ? $student->officeAssignmentRequests()->latest('id')->first()
            : null;

        return view('student.settings', [
            'officeOptions' => Student::getOfficeOptions(),
            'latestOfficeRequest' => $latestOfficeRequest,
        ]);
    }

    public function submitOfficeAssignmentRequest(Request $request)
    {
        $student = Auth::guard('student')->user();
        if (! $student) {
            abort(401);
        }

        $validated = $request->validate([
            'requested_office' => ['required', 'string', Rule::in(Student::getOfficeOptions())],
            'student_remarks' => ['required', 'string', 'min:8', 'max:1000'],
        ]);

        $requestedOffice = trim((string) $validated['requested_office']);
        $currentOffice = trim((string) ($student->assigned_office ?? ''));

        if ($currentOffice !== '' && strcasecmp($currentOffice, $requestedOffice) === 0) {
            return back()->with('error', 'You are already assigned to this office.');
        }

        $existingPending = OfficeAssignmentRequest::query()
            ->where('student_id', $student->id)
            ->where('status', OfficeAssignmentRequest::STATUS_PENDING)
            ->first();

        if ($existingPending) {
            return back()->with('error', 'You already have a pending office request. Please wait for admin review.');
        }

        OfficeAssignmentRequest::create([
            'student_id' => $student->id,
            'old_office' => $currentOffice !== '' ? $currentOffice : null,
            'requested_office' => $requestedOffice,
            'student_remarks' => trim((string) $validated['student_remarks']),
            'status' => OfficeAssignmentRequest::STATUS_PENDING,
        ]);

        AuditLog::create([
            'actor_type' => 'student',
            'actor_id' => $student->id,
            'action' => 'office_assignment_requested',
            'target_type' => 'student',
            'target_id' => $student->id,
            'details' => 'Student requested office reassignment.',
            'context' => [
                'student_no' => $student->student_no,
                'from' => $currentOffice !== '' ? $currentOffice : null,
                'to' => $requestedOffice,
            ],
        ]);

        return back()->with('success', 'Office assignment request submitted and pending admin verification.');
    }

    public function saveFaceEnrollment(Request $request)
    {
        $request->validate([
            'face_encoding' => 'required|string',
        ]);

        $student = Auth::guard('student')->user();
        if (! $student) {
            abort(401);
        }

        $encoding = json_decode((string) $request->input('face_encoding'), true);
        if (! is_array($encoding) || count($encoding) !== FaceEncodingService::ENCODING_LENGTH) {
            return back()->with('error', 'Invalid face data. Please try capturing again.');
        }

        // Re-enrollment hard check: if the account already has an enrolled face,
        // the new capture must match that same person. This blocks replacing an account
        // face with a different person.
        if (! empty($student->face_encoding)) {
            $currentEncoding = json_decode((string) $student->face_encoding, true);
            if (! is_array($currentEncoding) || count($currentEncoding) !== FaceEncodingService::ENCODING_LENGTH) {
                return back()->with('error', 'Existing enrolled face data is invalid. Please contact your coordinator for assisted reset.');
            }
            if (! FaceEncodingService::isSamePerson($encoding, $currentEncoding)) {
                $distance = FaceEncodingService::distance($encoding, $currentEncoding);
                Log::warning('Student face re-enrollment rejected: mismatch with current enrolled face', [
                    'student_id' => $student->id,
                    'student_no' => $student->student_no,
                    'distance' => round($distance, 4),
                    'threshold' => (float) (config('services.face_same_person_threshold') ?? FaceEncodingService::SAME_PERSON_THRESHOLD),
                ]);

                return back()->with('error', 'Face mismatch detected. Re-enrollment was blocked because the captured face does not match the currently enrolled face for this account.');
            }
        }

        if (config('services.face_duplicate_check', true)) {
            $existingWithFace = \App\Models\Student::query()
                ->where('id', '!=', $student->id)
                ->whereNotNull('face_encoding')
                ->get();

            foreach ($existingWithFace as $existing) {
                $stored = json_decode((string) $existing->face_encoding, true);
                if (is_array($stored) && FaceEncodingService::isSamePerson($encoding, $stored)) {
                    return back()->with('error', 'This face appears to be registered to another account. Please contact your coordinator.');
                }
            }
        }

        $student->face_encoding = json_encode($encoding);
        $student->save();

        AuditLog::create([
            'actor_type' => 'student',
            'actor_id' => $student->id,
            'action' => 'face_enrollment_completed',
            'target_type' => 'student',
            'target_id' => $student->id,
            'details' => 'Student completed/re-enrolled face data.',
            'context' => [
                'student_no' => $student->student_no,
            ],
        ]);

        return back()->with('success', 'Face enrollment completed successfully. You can now use camera verification.');
    }
}
