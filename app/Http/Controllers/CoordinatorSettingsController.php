<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\StudentDeletionService;
use App\Support\ProgramAlias;
use App\Support\StudentSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CoordinatorSettingsController extends Controller
{
    /**
     * Settings / configurations: change own password, student passwords, required OJT hours.
     * Now uses term-specific data from active term assignments.
     */
    public function index(Request $request)
    {
        $coordinator = Auth::guard('coordinator')->user();
        $search = $request->filled('q') ? trim($request->q) : '';

        $students = $this->fetchScopedStudents($coordinator, $search);

        return view('coordinator.settings', compact('students', 'search'));
    }

    /**
     * Show verified/enrolled students assigned to the coordinator.
     */
    public function students(Request $request)
    {
        $coordinator = Auth::guard('coordinator')->user();
        $search = $request->filled('q') ? trim($request->q) : '';
        $enrollmentFilter = $request->input('enrollment_filter', 'all');
        $sectionFilter = $request->filled('section_filter') ? trim((string) $request->input('section_filter')) : '';
        if ($sectionFilter !== '') {
            $sectionFilter = preg_replace('/^section\s+/i', '', $sectionFilter) ?? $sectionFilter;
            $sectionFilter = trim($sectionFilter);
        }
        $programFilter = $request->filled('program_filter') ? trim((string) $request->input('program_filter')) : '';
        $officeFilter = $request->filled('office_filter') ? trim((string) $request->input('office_filter')) : '';
        $newlyEnrolledDays = 30;

        $students = $this->fetchScopedStudents($coordinator, $search, $enrollmentFilter, $newlyEnrolledDays, $sectionFilter, $programFilter, $officeFilter);

        $assignedPrograms = $coordinator->assignedProgramLabels();

        $scopedStudentPrograms = $this->fetchScopedStudents($coordinator, '', 'all', $newlyEnrolledDays, '', '', '')
            ->map(function ($student) {
                return ProgramAlias::normalizeCourse(trim((string) ($student->activeTermAssignment?->course ?: $student->display_course ?: '')));
            })
            ->filter()
            ->values();

        $programOptions = $assignedPrograms
            ->concat($scopedStudentPrograms)
            ->unique()
            ->sort()
            ->values();

        $officeOptions = collect(Student::getOfficeOptions())
            ->concat(
                $this->fetchScopedStudents($coordinator, '', 'all', $newlyEnrolledDays, '', '', '')
                    ->pluck('assigned_office')
            )
            ->map(fn ($office) => trim((string) $office))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('coordinator.students', compact(
            'students',
            'search',
            'enrollmentFilter',
            'newlyEnrolledDays',
            'sectionFilter',
            'programFilter',
            'officeFilter',
            'programOptions',
            'officeOptions'
        ));
    }

    /**
     * Update the logged-in coordinator's password.
     */
    public function updatePassword(Request $request)
    {
        $coordinator = Auth::guard('coordinator')->user();

        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [
            'current_password.required' => 'Please enter your current password.',
            'password.required' => 'Please enter a new password.',
            'password.confirmed' => 'The new password confirmation does not match.',
        ]);

        if (! Hash::check($request->current_password, $coordinator->password)) {
            return redirect()->route('coordinator.settings')
                ->with('error', 'Current password is incorrect.')
                ->withInput($request->only('password', 'password_confirmation'));
        }

        $coordinator->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('coordinator.settings')->with('success', 'Your password has been updated.');
    }

    /**
     * Batch update required OJT hours for selected verified students' active terms.
     * Hours are stored on the active term assignments, not the base student records.
     */
    public function bulkUpdateRequiredHours(Request $request)
    {
        $coordinator = Auth::guard('coordinator')->user();

        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer',
            'required_ojt_hours' => 'required|numeric|min:1|max:9999',
        ], [
            'student_ids.required' => 'Select at least one student first.',
            'required_ojt_hours.required' => 'Enter the required OJT hours to apply.',
        ]);

        // Get students with their active term assignments
        $students = Student::forCoordinator($coordinator)
            ->verified()
            ->with('activeTermAssignment')
            ->whereIn('id', $validated['student_ids'])
            ->get();

        $updated = 0;
        $skipped = 0;

        foreach ($students as $student) {
            $termAssignment = $student->activeTermAssignment;

            if ($termAssignment) {
                // Update hours on the term assignment
                $termAssignment->update([
                    'required_ojt_hours' => (float) $validated['required_ojt_hours'],
                ]);
                $updated++;
            } else {
                // No active term - skip this student
                $skipped++;
            }
        }

        if ($updated === 0) {
            return redirect()->route('coordinator.settings')
                ->with('error', 'No students have an active OJT term. Please assign terms first.');
        }

        $message = "Required hours updated for {$updated} student".($updated === 1 ? '' : 's').'.';
        if ($skipped > 0) {
            $message .= " ({$skipped} skipped - no active term).";
        }

        return redirect()->route('coordinator.settings', array_filter([
            'q' => $request->input('q'),
        ]))->with('success', $message);
    }

    /**
     * Soft-delete one verified student visible to this coordinator (audited).
     */
    public function destroyStudent(Request $request, Student $student)
    {
        $coordinator = Auth::guard('coordinator')->user();

        if (! $student->isVerified() || ! $student->isVisibleToCoordinator($coordinator)) {
            abort(403, 'You cannot remove this student.');
        }

        $name = $student->name;
        StudentDeletionService::deleteWithAudit($student, 'coordinator', (int) $coordinator->id);

        return redirect()->route('coordinator.settings', array_filter([
            'q' => $request->input('q'),
        ]))->with('success', 'Student "'.$name.'" has been archived. An administrator can restore them from Archived students if needed.');
    }

    /**
     * Batch-remove verified students in scope (audited, one log per student).
     */
    public function bulkDestroyStudents(Request $request)
    {
        $coordinator = Auth::guard('coordinator')->user();

        $validated = $request->validate([
            'student_ids' => 'required|array|min:1|max:'.StudentDeletionService::MAX_BATCH,
            'student_ids.*' => 'integer|distinct',
        ], [
            'student_ids.required' => 'Select at least one student first.',
        ]);

        $uniqueIds = array_values(array_unique(array_map('intval', $validated['student_ids'])));

        $students = Student::forCoordinator($coordinator)
            ->verified()
            ->whereIn('id', $uniqueIds)
            ->orderBy('id')
            ->get();

        if ($students->count() !== count($uniqueIds)) {
            return redirect()->route('coordinator.settings', array_filter([
                'q' => $request->input('q'),
            ]))->with('error', 'Some selected students could not be removed. Refresh the page and try again.');
        }

        $count = StudentDeletionService::deleteManyWithAudit($students, 'coordinator', (int) $coordinator->id);

        return redirect()->route('coordinator.settings', array_filter([
            'q' => $request->input('q'),
        ]))->with('success', $count.' student'.($count === 1 ? '' : 's').' archived. An administrator can restore them from Archived students if needed.');
    }

    private function fetchScopedStudents($coordinator, string $search = '', string $enrollmentFilter = 'all', int $newlyEnrolledDays = 30, string $sectionFilter = '', string $programFilter = '', string $officeFilter = '')
    {
        $base = Student::forCoordinator($coordinator)
            ->verified()
            ->with(['activeTermAssignment', 'termAssignments' => fn ($q) => $q->latest('id')]);

        if ($enrollmentFilter === 'newly_enrolled') {
            $base->whereDate('created_at', '>=', now()->subDays($newlyEnrolledDays)->toDateString());
        }

        if ($sectionFilter !== '') {
            $sectionCandidates = collect([
                $sectionFilter,
                'Section '.$sectionFilter,
            ])->map(fn ($value) => trim((string) $value))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $base->where(function ($query) use ($sectionCandidates) {
                $query->whereHas('activeTermAssignment', function ($assignmentQuery) use ($sectionCandidates) {
                    $assignmentQuery->whereIn('section', $sectionCandidates);
                })->orWhere(function ($fallbackQuery) use ($sectionCandidates) {
                    $fallbackQuery->whereDoesntHave('activeTermAssignment')
                        ->whereIn('section', $sectionCandidates);
                });
            });
        }

        if ($programFilter !== '') {
            $base->where(function ($query) use ($programFilter) {
                $query->whereHas('activeTermAssignment', function ($assignmentQuery) use ($programFilter) {
                    $assignmentQuery->where('course', $programFilter);
                })->orWhere(function ($fallbackQuery) use ($programFilter) {
                    $fallbackQuery->whereDoesntHave('activeTermAssignment')
                        ->where('course', $programFilter);
                });
            });
        }

        if ($officeFilter !== '') {
            $base->where('assigned_office', $officeFilter);
        }

        if ($search === '') {
            return $base
                ->orderByDesc('created_at')
                ->orderBy('name')
                ->get();
        }

        if (StudentSearch::usesWildcardSyntax($search)) {
            $term = StudentSearch::buildWildcardTerm($search);

            return $base->where(function ($q) use ($term) {
                StudentSearch::applyCoordinatorDirectoryLike($q, $term);
            })->orderByDesc('created_at')->orderBy('name')->get();
        }

        $trim = trim($search);
        $term = StudentSearch::buildWildcardTerm($search);

        $rows = $base->where(function ($q) use ($term, $trim) {
            StudentSearch::applyBroadNameHints($q, $trim, function ($inner) use ($term) {
                StudentSearch::applyCoordinatorDirectoryLike($inner, $term);
            });
        })->orderByDesc('created_at')->orderBy('name')->get();

        return StudentSearch::refinePlainSearch($rows, $search, true);
    }
}
