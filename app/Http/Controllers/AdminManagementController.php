<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Coordinator;
use App\Models\CoordinatorAssignment;
use App\Models\DynamicOption;
use App\Models\OfficeAssignmentRequest;
use App\Models\Student;
use App\Models\StudentTermAssignment;
use App\Services\StudentDeletionService;
use App\Support\ProgramAlias;
use App\Support\StudentSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminManagementController extends Controller
{
    public function coordinators(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        $query = Coordinator::query();

        if ($search !== '') {
            $term = StudentSearch::buildWildcardTerm($search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('major', 'like', $term)
                    ->orWhere('college', 'like', $term);
            });
        }

        $coordinators = $query->with('assignments')->orderBy('name')->get();
        $programSectionMap = $this->buildProgramSectionMap();

        return view('admin.coordinators', compact('coordinators', 'search', 'programSectionMap'));
    }

    public function storeCoordinator(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'suffix' => 'nullable|string|max:20',
            'degree' => 'nullable|string|max:40',
            'email' => 'required|email|max:255|unique:coordinators,email',
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'college' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'custom_major' => 'nullable|string|max:255',
            'semester' => 'nullable|required_with:section|in:'.implode(',', Student::ASSIGNMENT_TERMS),
            'section' => 'nullable|required_with:semester|string|max:50',
            'custom_section' => 'nullable|string|max:50',
            'school_year' => 'nullable|string|max:20',
        ]);

        $major = $this->resolveSelectableValue(
            $validated['major'] ?? null,
            $validated['custom_major'] ?? null
        );

        if ($major === '') {
            return back()->withErrors(['major' => 'Please select or enter a program/course.'])->withInput();
        }
        $this->upsertDynamicOption(DynamicOption::TYPE_PROGRAM, $major);

        $section = $this->resolveSelectableValue(
            $validated['section'] ?? null,
            $validated['custom_section'] ?? null
        );

        if (! empty($validated['semester']) && $section === '') {
            return back()->withErrors(['section' => 'Please select or enter a section.'])->withInput();
        }
        if ($section !== '') {
            $this->upsertDynamicOption(DynamicOption::TYPE_SECTION, $section);
        }

        $displayName = $this->composeDisplayName(
            $validated['first_name'] ?? null,
            $validated['middle_name'] ?? null,
            $validated['last_name'] ?? null,
            $validated['suffix'] ?? null,
            $validated['degree'] ?? null
        );

        $finalName = $displayName !== '' ? $displayName : (string) ($validated['name'] ?? '');
        if ($finalName === '') {
            return back()
                ->withErrors(['first_name' => 'Please enter a complete name (first and last name at minimum).'])
                ->withInput();
        }

        if ($this->coordinatorDisplayNameAlreadyExists($finalName)) {
            return back()
                ->withErrors([
                    'duplicate_coordinator' => 'A coordinator with this full name already exists. Change middle name, suffix, degree suffix, or spelling—or deactivate/remove the existing account first.',
                ])
                ->withInput();
        }

        $coordinator = Coordinator::create([
            'name' => $finalName,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'department' => ($validated['college'] ?? null) ?: $major,
            'college' => $validated['college'] ?: null,
            'major' => $major,
            'is_active' => true,
        ]);

        if (! empty($validated['semester']) && $section !== '') {
            $coordinator->assignments()->create([
                'course' => $major,
                'semester' => $validated['semester'],
                'section' => $section,
                'school_year' => $validated['school_year'] ?: null,
            ]);
        }

        return redirect()->route('admin.coordinators')
            ->with('success', 'Coordinator account created successfully.');
    }

    public function toggleCoordinator(Request $request, Coordinator $coordinator)
    {
        $nextStatus = ! (bool) $coordinator->is_active;

        $coordinator->update([
            'is_active' => $nextStatus,
            'current_session_id' => $nextStatus ? $coordinator->current_session_id : null,
        ]);

        AuditLog::create([
            'actor_type' => 'admin',
            'actor_id' => Auth::guard('admin')->id(),
            'action' => 'coordinator_status_toggled',
            'target_type' => 'coordinator',
            'target_id' => $coordinator->id,
            'details' => 'Coordinator set to '.($coordinator->is_active ? 'active' : 'inactive').'.',
        ]);

        return back()->with('success', 'Coordinator "'.$coordinator->name.'" is now '.($coordinator->is_active ? 'active' : 'inactive').'.');
    }

    public function destroyCoordinator(Coordinator $coordinator)
    {
        $name = $coordinator->name;
        $id = $coordinator->id;

        DB::transaction(function () use ($coordinator, $name, $id) {
            $coordinator->delete();

            AuditLog::create([
                'actor_type' => 'admin',
                'actor_id' => Auth::guard('admin')->id(),
                'action' => 'coordinator_deleted',
                'target_type' => 'coordinator',
                'target_id' => $id,
                'details' => 'Coordinator "'.$name.'" was removed.',
            ]);
        });

        return back()->with('success', 'Coordinator "'.$name.'" has been removed.');
    }

    public function students(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $termFilter = trim((string) $request->input('term', ''));
        $sectionFilter = trim((string) $request->input('section', ''));

        $query = Student::query();

        if ($search !== '') {
            $trim = trim($search);
            if (StudentSearch::usesWildcardSyntax($search)) {
                $term = StudentSearch::buildWildcardTerm($search);
                $query->where(function ($q) use ($term) {
                    StudentSearch::applyAdminStudentLike($q, $term);
                });
            } else {
                $term = StudentSearch::buildWildcardTerm($search);
                $query->where(function ($q) use ($term, $trim) {
                    StudentSearch::applyBroadNameHints($q, $trim, function ($inner) use ($term) {
                        StudentSearch::applyAdminStudentLike($inner, $term);
                    });
                });
            }
        }

        if ($termFilter !== '') {
            $query->whereHas('activeTermAssignment', function ($assignmentQuery) use ($termFilter) {
                $assignmentQuery->where('term', $termFilter);
            });
        }

        if ($sectionFilter !== '') {
            $query->whereHas('activeTermAssignment', function ($assignmentQuery) use ($sectionFilter) {
                $assignmentQuery->where('section', $sectionFilter);
            });
        }

        $students = $query->with(['activeTermAssignment', 'termAssignments' => function ($assignmentQuery) {
            $assignmentQuery->latest('id');
        }])->orderBy('name')->get();

        if ($search !== '' && ! StudentSearch::usesWildcardSyntax($search)) {
            $students = StudentSearch::refinePlainSearch($students, $search, true);
        }

        return view('admin.students', [
            'students' => $students,
            'search' => $search,
            'selectedTerm' => $termFilter,
            'selectedSection' => $sectionFilter,
        ]);
    }

    public function officeRequests(Request $request)
    {
        $status = trim((string) $request->input('status', OfficeAssignmentRequest::STATUS_PENDING));
        $search = trim((string) $request->input('q', ''));

        if (! in_array($status, [
            OfficeAssignmentRequest::STATUS_PENDING,
            OfficeAssignmentRequest::STATUS_APPROVED,
            OfficeAssignmentRequest::STATUS_REJECTED,
            'all',
        ], true)) {
            $status = OfficeAssignmentRequest::STATUS_PENDING;
        }

        $query = OfficeAssignmentRequest::query()
            ->with(['student:id,name,student_no,assigned_office', 'reviewer:id,name']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $pattern = StudentSearch::buildWildcardTerm($search);
            $query->where(function ($outer) use ($pattern) {
                $outer->where('requested_office', 'like', $pattern)
                    ->orWhere('old_office', 'like', $pattern)
                    ->orWhere('student_remarks', 'like', $pattern)
                    ->orWhereHas('student', function ($studentQuery) use ($pattern) {
                        $studentQuery->where('name', 'like', $pattern)
                            ->orWhere('student_no', 'like', $pattern);
                    });
            });
        }

        $requests = $query->latest('id')->paginate(20)->withQueryString();

        return view('admin.office-requests', [
            'requests' => $requests,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function reviewOfficeAssignmentRequest(Request $request, OfficeAssignmentRequest $officeRequest)
    {
        if ($officeRequest->status !== OfficeAssignmentRequest::STATUS_PENDING) {
            return back()->with('info', 'This office request has already been reviewed.');
        }

        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'admin_remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $admin = Auth::guard('admin')->user();
        $decision = (string) $validated['decision'];
        $remarks = trim((string) ($validated['admin_remarks'] ?? ''));

        $officeRequest->update([
            'status' => $decision === 'approve'
                ? OfficeAssignmentRequest::STATUS_APPROVED
                : OfficeAssignmentRequest::STATUS_REJECTED,
            'admin_remarks' => $remarks,
            'reviewed_by' => $admin?->id,
            'reviewed_at' => now(),
        ]);

        if ($decision === 'approve') {
            $student = $officeRequest->student;
            if ($student) {
                $student->assigned_office = $officeRequest->requested_office;
                $student->save();
            }
        }

        AuditLog::create([
            'actor_type' => 'admin',
            'actor_id' => $admin?->id,
            'action' => $decision === 'approve' ? 'office_assignment_approved' : 'office_assignment_rejected',
            'target_type' => 'office_assignment_request',
            'target_id' => $officeRequest->id,
            'details' => $remarks,
            'context' => [
                'student_id' => $officeRequest->student_id,
                'from' => $officeRequest->old_office,
                'to' => $officeRequest->requested_office,
            ],
        ]);

        return back()->with(
            'success',
            $decision === 'approve'
                ? 'Office reassignment approved and updated.'
                : 'Office reassignment request rejected.'
        );
    }

    /**
     * Soft-delete a student (audited). Restore from Archived students.
     */
    public function destroyStudent(Request $request, Student $student)
    {
        $admin = Auth::guard('admin')->user();
        $name = $student->name;

        StudentDeletionService::deleteWithAudit($student, 'admin', (int) $admin->id);

        return redirect()->route('admin.students', $request->only(['q', 'term', 'section']))
            ->with('success', 'Student "'.$name.'" has been archived. You can restore them from Archived students if needed.');
    }

    /**
     * Batch soft-delete students (audited).
     */
    public function bulkDestroyStudents(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'student_ids' => 'required|array|min:1|max:'.StudentDeletionService::MAX_BATCH,
            'student_ids.*' => 'integer|distinct',
        ], [
            'student_ids.required' => 'Select at least one student first.',
        ]);

        $uniqueIds = array_values(array_unique(array_map('intval', $validated['student_ids'])));

        $students = Student::query()
            ->whereIn('id', $uniqueIds)
            ->orderBy('id')
            ->get();

        if ($students->count() !== count($uniqueIds)) {
            return redirect()->route('admin.students', $request->only(['q', 'term', 'section']))
                ->with('error', 'Some selected students could not be found. Refresh the page and try again.');
        }

        $count = StudentDeletionService::deleteManyWithAudit($students, 'admin', (int) $admin->id);

        return redirect()->route('admin.students', $request->only(['q', 'term', 'section']))
            ->with('success', $count.' student'.($count === 1 ? '' : 's').' archived. Restore them from Archived students if needed.');
    }

    /**
     * List soft-deleted students (admin only).
     */
    public function archivedStudents(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        $query = Student::onlyTrashed()->orderByDesc('deleted_at');

        if ($search !== '') {
            $term = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('student_no', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('course', 'like', $term);
            });
        }

        $students = $query->with(['activeTermAssignment', 'termAssignments' => fn ($q) => $q->latest('id')])->paginate(25)->withQueryString();

        return view('admin.students-archived', [
            'students' => $students,
            'search' => $search,
        ]);
    }

    /**
     * Restore a soft-deleted student (admin only).
     */
    public function restoreStudent(Request $request, int $id)
    {
        $admin = Auth::guard('admin')->user();
        $student = Student::onlyTrashed()->findOrFail($id);

        $name = $student->name;
        $studentNo = $student->student_no;
        $studentId = $student->id;

        $student->restore();

        AuditLog::create([
            'actor_type' => 'admin',
            'actor_id' => (int) $admin->id,
            'action' => 'student_restored',
            'target_type' => 'student',
            'target_id' => $studentId,
            'details' => 'Student restored from archive: '.$studentNo.' — '.$name.'.',
            'context' => [
                'student_no' => $studentNo,
                'name' => $name,
            ],
        ]);

        return redirect()->route('admin.students.archived', $request->only(['q']))
            ->with('success', 'Student "'.$name.'" has been restored to the active list.');
    }

    /**
     * Permanently remove a soft-deleted student from archive (admin only).
     */
    public function forceRemoveArchivedStudent(Request $request, int $id)
    {
        $validated = $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        $admin = Auth::guard('admin')->user();
        $student = Student::onlyTrashed()->findOrFail($id);

        $name = $student->name;
        $studentNo = $student->student_no;
        $studentId = $student->id;
        $remarks = trim((string) ($validated['remarks'] ?? ''));

        $student->forceDelete();

        AuditLog::create([
            'actor_type' => 'admin',
            'actor_id' => (int) $admin->id,
            'action' => 'student_permanently_removed',
            'target_type' => 'student',
            'target_id' => $studentId,
            'details' => 'Archived student permanently removed: '.$studentNo.' — '.$name.'.',
            'context' => [
                'student_no' => $studentNo,
                'name' => $name,
                'remarks' => $remarks,
            ],
        ]);

        return redirect()->route('admin.students.archived', $request->only(['q']))
            ->with('success', 'Student "'.$name.'" was permanently removed from archive.');
    }

    public function settings()
    {
        $defaultRequiredHours = (float) (
            StudentTermAssignment::query()->whereNotNull('required_ojt_hours')->min('required_ojt_hours')
            ?? Student::query()->whereNotNull('required_ojt_hours')->min('required_ojt_hours')
            ?? 120
        );
        $sessionLifetime = (int) config('session.lifetime');
        $kioskBaseUrl = route('kiosk.index');
        $kioskAccessKey = trim((string) config('dtr.kiosk_access_key', ''));

        return view('admin.settings', compact('defaultRequiredHours', 'sessionLifetime', 'kioskBaseUrl', 'kioskAccessKey'));
    }

    public function options()
    {
        $dynamicPrograms = DynamicOption::query()
            ->where('type', DynamicOption::TYPE_PROGRAM)
            ->orderBy('value')
            ->get();

        $dynamicSections = DynamicOption::query()
            ->where('type', DynamicOption::TYPE_SECTION)
            ->active()
            ->orderBy('value')
            ->get();

        return view('admin.options', compact('dynamicPrograms', 'dynamicSections'));
    }

    public function updateCoordinatorPassword(Request $request, Coordinator $coordinator)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $coordinator->update([
            'password' => Hash::make($validated['password']),
            'current_session_id' => null,
        ]);

        return back()->with('success', 'Coordinator password reset for "'.$coordinator->name.'".');
    }

    public function addCoordinatorAssignment(Request $request, Coordinator $coordinator)
    {
        $validated = $request->validate([
            'course' => 'nullable|string|max:100',
            'courses' => 'nullable|array',
            'courses.*' => 'nullable|string|max:100',
            'custom_course' => 'nullable|string|max:100',
            'school_year' => 'nullable|string|max:20',
            'semester' => 'required|in:'.implode(',', Student::ASSIGNMENT_TERMS),
            'section' => 'nullable|string|max:50',
            'custom_section' => 'nullable|string|max:50',
        ]);

        $singleCourse = ProgramAlias::normalizeCourse($this->resolveSelectableValue(
            $validated['course'] ?? null,
            $validated['custom_course'] ?? null
        ));
        $courses = collect($validated['courses'] ?? [])
            ->map(fn ($value) => ProgramAlias::normalizeCourse($this->normalizeOptionValue((string) $value)))
            ->filter(fn ($value) => is_string($value) && $value !== '' && $value !== '__custom__')
            ->values();
        if (is_string($singleCourse) && $singleCourse !== '') {
            $courses->push($singleCourse);
        }
        $courses = $courses->unique()->values();

        $section = $this->resolveSelectableValue(
            $validated['section'] ?? null,
            $validated['custom_section'] ?? null
        );

        if ($courses->isEmpty()) {
            return back()->withErrors(['course' => 'Please select or enter a program/course.'])->withInput();
        }
        if ($section === '') {
            return back()->withErrors(['section' => 'Please select or enter a section.'])->withInput();
        }
        $this->upsertDynamicOption(DynamicOption::TYPE_SECTION, $section);

        foreach ($courses as $course) {
            $this->upsertDynamicOption(DynamicOption::TYPE_PROGRAM, $course);

            // Uniqueness is (coordinator_id, course, semester, section) — not school_year.
            // firstOrCreate([...all columns...]) misses rows when school_year differs, then insert 500s.
            $coordinator->assignments()->updateOrCreate(
                [
                    'course' => $course,
                    'semester' => $validated['semester'],
                    'section' => $section,
                ],
                [
                    'school_year' => $validated['school_year'] ?: null,
                ]
            );
        }

        if (empty($coordinator->major)) {
            $coordinator->update(['major' => $courses->first()]);
        }

        $assignmentCount = $courses->count();

        return back()->with('success', $assignmentCount > 1
            ? $assignmentCount.' assignments added for "'.$coordinator->name.'".'
            : 'Assignment added for "'.$coordinator->name.'".');
    }

    public function updateCoordinatorAssignment(Request $request, CoordinatorAssignment $assignment)
    {
        $validated = $request->validate([
            'course' => 'nullable|string|max:100',
            'custom_course' => 'nullable|string|max:100',
            'school_year' => 'nullable|string|max:20',
            'semester' => 'required|in:'.implode(',', Student::ASSIGNMENT_TERMS),
            'section' => 'nullable|string|max:50',
            'custom_section' => 'nullable|string|max:50',
        ]);

        $course = $this->resolveSelectableValue(
            $validated['course'] ?? null,
            $validated['custom_course'] ?? null
        );
        $section = $this->resolveSelectableValue(
            $validated['section'] ?? null,
            $validated['custom_section'] ?? null
        );

        if ($course === '') {
            return back()->withErrors(['course' => 'Please select or enter a program/course.'])->withInput();
        }
        if ($section === '') {
            return back()->withErrors(['section' => 'Please select or enter a section.'])->withInput();
        }
        $this->upsertDynamicOption(DynamicOption::TYPE_PROGRAM, $course);
        $this->upsertDynamicOption(DynamicOption::TYPE_SECTION, $section);

        $assignment->update([
            'course' => $course,
            'semester' => $validated['semester'],
            'section' => $section,
            'school_year' => $validated['school_year'] ?: null,
        ]);

        return back()->with('success', 'Assignment updated successfully.');
    }

    public function storeProgramOption(Request $request)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:120',
        ]);

        $value = $this->normalizeOptionValue($validated['value']);
        if ($value === '') {
            return back()->withErrors(['value' => 'Program value cannot be empty.'])->withInput();
        }

        $this->upsertDynamicOption(DynamicOption::TYPE_PROGRAM, $value);

        return back()->with('success', 'Additional program added successfully.');
    }

    public function storeSectionOption(Request $request)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:120',
        ]);

        $value = $this->normalizeOptionValue($validated['value']);
        if ($value === '') {
            return back()->withErrors(['value' => 'Section value cannot be empty.'])->withInput();
        }

        $value = Student::normalizeDynamicSectionValue($value);
        if ($value === '') {
            return back()->withErrors(['value' => 'Section value cannot be empty.'])->withInput();
        }

        $this->upsertDynamicOption(DynamicOption::TYPE_SECTION, $value);

        return back()->with('success', 'Additional section added successfully.');
    }

    public function deactivateOption(DynamicOption $option)
    {
        $option->delete();

        return back()->with('success', 'That section was removed permanently and will no longer appear in this list or registration.');
    }

    public function removeCoordinatorAssignment(CoordinatorAssignment $assignment)
    {
        $assignment->delete();

        return back()->with('success', 'Coordinator assignment removed.');
    }

    public function storeStudentTermAssignment(Request $request, Student $student)
    {
        $validated = $request->validate([
            'school_year' => 'nullable|string|max:20',
            'term' => 'required|in:'.implode(',', StudentTermAssignment::TERMS),
            'section' => 'required|in:'.implode(',', StudentTermAssignment::SECTIONS),
            'required_ojt_hours' => 'required|numeric|min:1|max:9999',
        ]);

        DB::transaction(function () use ($student, $validated) {
            $this->assignTermToStudent($student, $validated);
        });

        return back()->with('success', 'New OJT term assigned for '.$student->name.'.');
    }

    public function batchStoreStudentTermAssignments(Request $request)
    {
        $officeOptions = Student::getOfficeOptions();

        // Debug: log incoming request data
        Log::debug('Bulk assignment request', [
            'student_ids' => $request->input('student_ids'),
            'assigned_office' => $request->input('assigned_office'),
            'all_input' => $request->all(),
        ]);

        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => ['integer', Rule::exists('students', 'id')->whereNull('deleted_at')],
            'school_year' => 'nullable|string|max:20',
            'term' => 'nullable|in:'.implode(',', StudentTermAssignment::TERMS),
            'section' => 'nullable|in:'.implode(',', StudentTermAssignment::SECTIONS),
            'required_ojt_hours' => 'nullable|numeric|min:1|max:9999',
            'assigned_office' => ['nullable', 'string', Rule::in($officeOptions)],
        ]);

        // Only treat as "term assignment" when the admin actually filled term-related fields.
        // The Hours input is always present in the form; when left blank it still appears in
        // $validated as null — do not use array_key_exists() or empty() on the key alone.
        $hasTermAssignmentInput =
            filled($validated['school_year'] ?? null) ||
            filled($validated['term'] ?? null) ||
            filled($validated['section'] ?? null) ||
            filled($validated['required_ojt_hours'] ?? null);
        $hasOfficeInput = filled($validated['assigned_office'] ?? null);

        Log::debug('Bulk assignment validation', [
            'hasTermAssignmentInput' => $hasTermAssignmentInput,
            'hasOfficeInput' => $hasOfficeInput,
            'validated' => $validated,
        ]);

        if (! $hasTermAssignmentInput && ! $hasOfficeInput) {
            return back()
                ->withErrors(['bulk_assign' => 'Provide at least one field to update (term details or assigned office).'])
                ->withInput();
        }

        if ($hasTermAssignmentInput && (
            empty($validated['term']) ||
            empty($validated['section']) ||
            ! filled($validated['required_ojt_hours'] ?? null)
        )) {
            return back()
                ->withErrors(['bulk_assign' => 'To assign a term, please provide term, section, and hours.'])
                ->withInput();
        }

        $studentIds = collect($validated['student_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $students = Student::query()
            ->whereIn('id', $studentIds)
            ->get();

        DB::transaction(function () use ($students, $validated, $hasTermAssignmentInput, $hasOfficeInput) {
            /** @var \App\Models\Student $student */
            foreach ($students as $student) {
                if ($hasTermAssignmentInput) {
                    $this->assignTermToStudent($student, $validated);
                }
                if ($hasOfficeInput) {
                    $student->assigned_office = $validated['assigned_office'];
                    $student->save();
                }
            }
        });

        $count = $students->count();

        if ($hasTermAssignmentInput && $hasOfficeInput) {
            return back()->with('success', 'Updated term details and assigned office for '.$count.' student'.($count === 1 ? '' : 's').'.');
        }
        if ($hasTermAssignmentInput) {
            return back()->with('success', 'Assigned a new OJT term to '.$count.' student'.($count === 1 ? '' : 's').'.');
        }

        return back()->with('success', 'Updated assigned office for '.$count.' student'.($count === 1 ? '' : 's').'.');
    }

    public function completeStudentTermAssignment(StudentTermAssignment $assignment)
    {
        if ($assignment->status !== StudentTermAssignment::STATUS_ACTIVE) {
            return back()->with('info', 'This term record is already completed.');
        }

        $assignment->update([
            'status' => StudentTermAssignment::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Marked the active term as completed.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $admin = Auth::guard('admin')->user();

        if (! $admin || ! Hash::check($validated['current_password'], $admin->password)) {
            return back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput($request->except(['current_password', 'password', 'password_confirmation']));
        }

        $admin->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Admin password updated successfully.');
    }

    private function assignTermToStudent(Student $student, array $validated): void
    {
        // Get course from active term or fallback to base student course
        $course = $student->activeTermAssignment?->course ?? trim((string) $student->course);

        // Mark any existing active term as completed
        $student->termAssignments()
            ->where('status', StudentTermAssignment::STATUS_ACTIVE)
            ->update([
                'status' => StudentTermAssignment::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

        // Create new term assignment - this is now the source of truth for:
        // - required_ojt_hours
        // - completion confirmation
        // Note: We no longer update the base student record with term-specific data
        // to ensure proper term isolation
        $student->termAssignments()->create([
            'course' => $course,
            'school_year' => $validated['school_year'] ?: null,
            'term' => $validated['term'],
            'section' => $validated['section'],
            'required_ojt_hours' => (float) $validated['required_ojt_hours'],
            'status' => StudentTermAssignment::STATUS_ACTIVE,
            'started_at' => now(),
        ]);

        // Update base student section only (for display purposes when no active term)
        // Note: required_ojt_hours is now term-specific only
        $student->section = $validated['section'];
        $student->save();
    }

    private function resolveSelectableValue(?string $selected, ?string $custom): string
    {
        $selectedValue = $this->normalizeOptionValue($selected);
        $customValue = $this->normalizeOptionValue($custom);

        if ($selectedValue === '__custom__' || $selectedValue === '') {
            return $customValue;
        }

        return $selectedValue;
    }

    private function normalizeOptionValue(?string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim((string) $value));

        return $normalized === null ? '' : $normalized;
    }

    /**
     * Normalize stored/display coordinator names so "Maria  Santos" matches "maria santos".
     */
    private function normalizeCoordinatorNameKey(string $name): string
    {
        $collapsed = preg_replace('/\s+/u', ' ', trim($name));

        return mb_strtolower((string) $collapsed, 'UTF-8');
    }

    private function coordinatorDisplayNameAlreadyExists(string $displayName): bool
    {
        $target = $this->normalizeCoordinatorNameKey($displayName);
        if ($target === '') {
            return false;
        }

        return Coordinator::query()
            ->pluck('name')
            ->contains(fn ($existing) => $this->normalizeCoordinatorNameKey((string) $existing) === $target);
    }

    private function composeDisplayName(
        ?string $firstName,
        ?string $middleName,
        ?string $lastName,
        ?string $suffix,
        ?string $degree
    ): string {
        $parts = array_filter([
            $this->normalizeOptionValue($firstName),
            $this->normalizeOptionValue($middleName),
            $this->normalizeOptionValue($lastName),
            $this->normalizeOptionValue($suffix),
        ]);

        $name = implode(' ', $parts);
        $degreeValue = $this->normalizeOptionValue($degree);
        if ($name !== '' && $degreeValue !== '') {
            $name .= ', '.$degreeValue;
        }

        return $name;
    }

    private function upsertDynamicOption(string $type, string $value): void
    {
        $normalized = $this->normalizeOptionValue($value);
        if ($normalized === '') {
            return;
        }

        DynamicOption::query()->updateOrCreate(
            ['type' => $type, 'value' => $normalized],
            ['is_active' => true]
        );
    }

    private function buildProgramSectionMap(): array
    {
        $programMap = [];

        $termPairs = StudentTermAssignment::query()
            ->select(['course', 'section'])
            ->whereNotNull('course')
            ->whereNotNull('section')
            ->get();

        $assignmentPairs = CoordinatorAssignment::query()
            ->select(['course', 'section'])
            ->whereNotNull('course')
            ->whereNotNull('section')
            ->get();

        foreach ($termPairs->concat($assignmentPairs) as $pair) {
            $course = $this->normalizeOptionValue((string) $pair->course);
            $section = $this->normalizeOptionValue((string) $pair->section);
            if ($course === '' || $section === '') {
                continue;
            }
            $programMap[$course][] = $section;
        }

        $dynamicSections = DynamicOption::query()
            ->active()
            ->where('type', DynamicOption::TYPE_SECTION)
            ->orderBy('value')
            ->pluck('value')
            ->map(fn ($value) => $this->normalizeOptionValue((string) $value))
            ->filter()
            ->values()
            ->all();

        $defaultSections = array_values(array_unique(array_merge(
            Student::ASSIGNMENT_SECTIONS,
            $dynamicSections
        )));

        $normalizeList = static function (array $values): array {
            $values = array_values(array_unique(array_filter($values, static fn ($item) => $item !== '')));
            $hasAll = in_array('All', $values, true);
            $values = array_values(array_filter($values, static fn ($item) => $item !== 'All'));
            natcasesort($values);
            $values = array_values($values);
            if ($hasAll) {
                array_unshift($values, 'All');
            }

            return $values;
        };

        $defaultSections = $normalizeList($defaultSections);
        $programOptions = Student::getProgramOptions();

        foreach ($programOptions as $program) {
            $course = $this->normalizeOptionValue((string) $program);
            $specific = $programMap[$course] ?? [];
            $programMap[$course] = $normalizeList(array_merge($specific, $dynamicSections, ['All']));
        }

        foreach ($programMap as $course => $sections) {
            $programMap[$course] = $normalizeList(array_merge($sections, $dynamicSections));
        }

        $programMap['__default__'] = $defaultSections;

        return $programMap;
    }
}
