<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Support\StudentSearch;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class OjtCompletionController extends Controller
{
    private function ensureStudentBelongsToCoordinator(Student $student)
    {
        $coordinator = Auth::guard('coordinator')->user();
        $allowed = Student::forCoordinator($coordinator)
            ->verified()
            ->where('id', $student->id)
            ->exists();

        if (! $allowed) {
            abort(403, 'You cannot manage this student.');
        }

        return $coordinator;
    }

    /**
     * List students in coordinator's program with term-specific hours, required hours, and confirmation status.
     * Supports wildcard search by name, student number, or course (GET ?q=).
     * Now shows term-specific data from the active term assignment.
     */
    public function index(Request $request)
    {
        $coordinator = Auth::guard('coordinator')->user();
        $search = $request->filled('q') ? trim($request->q) : '';

        $query = Student::forCoordinator($coordinator)
            ->verified()
            ->with([
                'activeTermAssignment.confirmedBy',
                'termAssignments' => fn ($q) => $q->latest('id'),
            ]);

        if ($search !== '') {
            $trim = trim($search);
            if (StudentSearch::usesWildcardSyntax($search)) {
                $term = StudentSearch::buildWildcardTerm($search);
                $query->where(function ($q) use ($term) {
                    StudentSearch::applyOjtCompletionLike($q, $term);
                });
            } else {
                $term = StudentSearch::buildWildcardTerm($search);
                $query->where(function ($q) use ($term, $trim) {
                    StudentSearch::applyBroadNameHints($q, $trim, function ($inner) use ($term) {
                        StudentSearch::applyOjtCompletionLike($inner, $term);
                    });
                });
            }
        }

        $students = $query->get();
        if ($search !== '' && ! StudentSearch::usesWildcardSyntax($search)) {
            $students = StudentSearch::refinePlainSearch($students, $search, true);
        }

        $students = $students
            ->sort(function ($a, $b) {
                // Sort by term-specific rendered hours
                $aHours = $a->renderedHoursForAssignment($a->activeTermAssignment);
                $bHours = $b->renderedHoursForAssignment($b->activeTermAssignment);
                $diff = $bHours <=> $aHours;

                return $diff !== 0 ? $diff : strcasecmp($a->name, $b->name);
            })
            ->values();

        if ($request->boolean('suggest')) {
            return response()->json([
                'students' => $students->take(10)->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'student_no' => $s->student_no,
                    'course' => $s->display_course ?? '',
                ]),
            ]);
        }

        return view('coordinator.ojt-completion', compact('students', 'search'));
    }

    /**
     * Confirm that a student has completed their required OJT hours.
     * Completion is stored on the active term assignment, not the base student record.
     */
    public function confirm(Request $request, Student $student)
    {
        $coordinator = $this->ensureStudentBelongsToCoordinator($student);

        // Get the active term assignment
        $termAssignment = $student->activeTermAssignment;

        if (! $termAssignment) {
            return back()->with('error', 'Student has no active OJT term. Please assign a term first.');
        }

        // Check hours for the specific term assignment
        if (! $student->hasReachedRequiredHours($termAssignment)) {
            return back()->with('error', 'Student has not reached the required hours for the current term yet.');
        }

        // Check completion on the term assignment
        if ($student->isOjtCompletionConfirmed($termAssignment)) {
            return back()->with('info', 'This student\'s completion was already confirmed for the current term.');
        }

        try {
            // Update completion on the term assignment, not the base student
            $termAssignment->update([
                'confirmed_at' => now(),
                'confirmed_by' => $coordinator->id,
            ]);
            Log::info('OJT completion confirmed on term', ['coordinator_id' => $coordinator->id, 'student_id' => $student->id, 'student_name' => $student->name, 'term_assignment_id' => $termAssignment->id]);

            return back()->with('success', 'OJT completion confirmed for '.$student->name.' ('.$termAssignment->term.').');
        } catch (\Throwable $e) {
            Log::error('OJT confirm failed', ['coordinator_id' => $coordinator->id, 'student_id' => $student->id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Unable to confirm completion. Please try again.');
        }
    }

    /**
     * Update required OJT hours for a student's active term.
     * Hours are stored on the active term assignment, not the base student record.
     */
    public function updateRequiredHours(Request $request, Student $student)
    {
        $this->ensureStudentBelongsToCoordinator($student);

        $request->validate([
            'required_ojt_hours' => 'required|numeric|min:1|max:9999',
        ]);

        // Get the active term assignment
        $termAssignment = $student->activeTermAssignment;

        if (! $termAssignment) {
            return back()->with('error', 'Student has no active OJT term. Please assign a term first.');
        }

        // Update hours on the term assignment, not the base student
        $termAssignment->update([
            'required_ojt_hours' => (float) $request->required_ojt_hours,
        ]);

        return back()->with('success', 'Required hours updated for '.$student->name.' ('.$termAssignment->term.').');
    }

    /**
     * Download e-certificate of OJT completion for a student (coordinator only).
     * Only available when completion has been confirmed.
     */
    public function downloadCertificate(Student $student)
    {
        $coordinator = $this->ensureStudentBelongsToCoordinator($student);

        // Check completion on the active term assignment
        $termAssignment = $student->activeTermAssignment;
        if (! $termAssignment || ! $student->isOjtCompletionConfirmed($termAssignment)) {
            return back()->with('error', 'Certificate is only available after OJT completion has been confirmed for the current term.');
        }

        try {
            $student->load('activeTermAssignment.confirmedBy');
            $issuedAt = Carbon::now('Asia/Manila')->format('F d, Y');

            // Pass term assignment info to the certificate
            $pdf = Pdf::loadView('reports.certificate-of-completion', [
                'student' => $student,
                'termAssignment' => $termAssignment,
                'issuedAt' => $issuedAt,
            ]);
            $pdf->setPaper('A4', 'portrait');

            $filename = 'OJT_Certificate_'.$student->student_no.'_'.now()->format('Y-m-d').'.pdf';

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            Log::error('Certificate download failed', ['coordinator_id' => $coordinator->id ?? null, 'student_id' => $student->id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Unable to generate the certificate. Please try again.');
        }
    }

    /**
     * Set a new (temporary) password for a student. Coordinator can only set for students in their program.
     */
    public function setStudentPassword(Request $request, Student $student)
    {
        $coordinator = $this->ensureStudentBelongsToCoordinator($student);

        $request->validate([
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [
            'password.required' => 'Please enter a new password.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        try {
            $student->update([
                'password' => Hash::make($request->password),
            ]);
            Log::info('Coordinator set student password', ['coordinator_id' => $coordinator->id, 'student_id' => $student->id, 'student_no' => $student->student_no]);

            return back()->with('success', 'Password updated for '.$student->name.'. They can log in with the new password.');
        } catch (\Throwable $e) {
            Log::error('Set student password failed', ['coordinator_id' => $coordinator->id, 'student_id' => $student->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Unable to update password. Please try again.');
        }
    }
}
