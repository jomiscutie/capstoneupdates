<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Student;
use App\Support\StudentSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentVerificationController extends Controller
{
    /**
     * List students in the coordinator's course who are pending verification.
     * Supports wildcard search by name, student number, or course (GET ?q=).
     */
    public function index(Request $request)
    {
        $coordinator = Auth::guard('coordinator')->user();
        $search = $request->filled('q') ? trim($request->q) : '';

        $query = Student::forCoordinator($coordinator)
            ->pendingVerification()
            ->orderBy('created_at', 'desc');

        if ($search !== '') {
            $trim = trim($search);
            if (StudentSearch::usesWildcardSyntax($search)) {
                $term = StudentSearch::buildWildcardTerm($search);
                $query->where(function ($q) use ($term) {
                    StudentSearch::applyPendingVerificationLike($q, $term);
                });
            } else {
                $term = StudentSearch::buildWildcardTerm($search);
                $query->where(function ($q) use ($term, $trim) {
                    StudentSearch::applyBroadNameHints($q, $trim, function ($inner) use ($term) {
                        StudentSearch::applyPendingVerificationLike($inner, $term);
                    });
                });
            }
        }

        $pending = $query->get();
        if ($search !== '' && ! StudentSearch::usesWildcardSyntax($search)) {
            $pending = StudentSearch::refinePlainSearch($pending, $search, false);
        }

        if ($request->boolean('suggest')) {
            return response()->json([
                'students' => $pending->take(10)->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'student_no' => $s->student_no,
                    'course' => $s->course ?? '',
                ]),
            ]);
        }

        return view('coordinator.pending-verification', compact('pending', 'search'));
    }

    /**
     * Verify that the student belongs to the coordinator's class. Student can then log in and be supervised.
     */
    public function verify(Student $student)
    {
        $coordinator = Auth::guard('coordinator')->user();

        if (! $student->isVisibleToCoordinator($coordinator)) {
            abort(403, 'You cannot verify this student.');
        }

        if (! $student->isPendingVerification()) {
            return back()->with('info', 'This student is already verified or was rejected.');
        }

        try {
            $student->update([
                'verification_status' => 'verified',
                'verified_at' => now(),
                'verified_by' => $coordinator->id,
                'rejected_at' => null,
                'rejected_by' => null,
            ]);
            Log::info('Student verified by coordinator', [
                'coordinator_id' => $coordinator->id,
                'student_id' => $student->id,
                'student_no' => $student->student_no,
            ]);
            AuditLog::create([
                'actor_type' => 'coordinator',
                'actor_id' => $coordinator->id,
                'action' => 'student_verified',
                'target_type' => 'student',
                'target_id' => $student->id,
                'details' => 'Student verified by coordinator.',
                'context' => ['student_no' => $student->student_no],
            ]);

            return back()->with('success', $student->name.' has been verified and can now log in and record attendance.');
        } catch (\Throwable $e) {
            Log::error('Student verification failed', [
                'coordinator_id' => $coordinator->id,
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to verify student. Please try again.');
        }
    }

    /**
     * Reject the student (e.g. wrong course, duplicate, or does not belong to this class).
     */
    public function reject(Student $student)
    {
        $coordinator = Auth::guard('coordinator')->user();

        if (! $student->isVisibleToCoordinator($coordinator)) {
            abort(403, 'You cannot reject this student.');
        }

        if (! $student->isPendingVerification()) {
            return back()->with('info', 'This student is already verified or was rejected.');
        }

        try {
            $student->update([
                'verification_status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => $coordinator->id,
            ]);
            Log::info('Student rejected by coordinator', [
                'coordinator_id' => $coordinator->id,
                'student_id' => $student->id,
                'student_no' => $student->student_no,
            ]);
            AuditLog::create([
                'actor_type' => 'coordinator',
                'actor_id' => $coordinator->id,
                'action' => 'student_rejected',
                'target_type' => 'student',
                'target_id' => $student->id,
                'details' => 'Student rejected by coordinator.',
                'context' => ['student_no' => $student->student_no],
            ]);

            return back()->with('success', $student->name.' has been rejected. They will not be able to log in unless you verify them later.');
        } catch (\Throwable $e) {
            Log::error('Student reject failed', [
                'coordinator_id' => $coordinator->id,
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to reject student. Please try again.');
        }
    }
}
