@extends('layouts.admin')

@section('title', 'Student Management')

@section('content')
<h1 class="page-title">Student Management</h1>
<p class="page-sub text-center">Assign OJT terms one by one or in batch while keeping each student's term history intact. Removing a student <strong>archives</strong> them (soft delete); restore from <a href="{{ route('admin.students.archived') }}">Archived students</a>.</p>

<div class="card mb-4">
    <div class="card-body">
        <div class="toolbar mb-3">
            <div>
                <h2 class="h5 mb-1">All Students</h2>
                <div class="text-muted small">Search across student number, name, course, term, section, or school year.</div>
            </div>
            <form action="{{ route('admin.students') }}" method="GET" class="student-filter-form" role="search">
                <div class="search-inner">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" name="q" class="search-input" placeholder="Name & ID number" value="{{ $search ?? '' }}">
                    @if(!empty($search))
                        <a href="{{ route('admin.students', ['term' => $selectedTerm, 'section' => $selectedSection]) }}" class="search-clear"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
                <select name="term" class="form-select filter-select" aria-label="Filter by term">
                    <option value="">All terms</option>
                    @foreach(\App\Models\StudentTermAssignment::TERMS as $termOption)
                        <option value="{{ $termOption }}" {{ ($selectedTerm ?? '') === $termOption ? 'selected' : '' }}>{{ $termOption }}</option>
                    @endforeach
                </select>
                <select name="section" class="form-select filter-select" aria-label="Filter by section">
                    <option value="">All sections</option>
                    @foreach(\App\Models\StudentTermAssignment::SECTIONS as $sectionOption)
                        <option value="{{ $sectionOption }}" {{ ($selectedSection ?? '') === $sectionOption ? 'selected' : '' }}>{{ $sectionOption }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-search"><i class="bi bi-search" aria-hidden="true"></i> Search</button>
                @if(!empty($search) || !empty($selectedTerm) || !empty($selectedSection))
                    <a href="{{ route('admin.students') }}" class="btn btn-outline-secondary btn-search">Clear</a>
                @endif
            </form>
        </div>

        <form method="POST" action="{{ route('admin.students.terms.batch') }}" id="bulkStudentTermForm" data-max-batch="{{ \App\Services\StudentDeletionService::MAX_BATCH }}">
            @csrf
            <div class="bulk-assign-card">
                <div class="bulk-assign-head">
                    <div>
                        <div class="bulk-kicker">Bulk Assignment</div>
                        <h3 class="bulk-title">Assign One Term To Multiple Students</h3>
                        <p class="bulk-sub mb-0">Use this for whole sections or newly enrolled groups. Existing active terms will be completed automatically.</p>
                    </div>
                    <div class="bulk-meta">
                        <span class="selected-pill">
                            <span id="selectedStudentsCount">0</span>
                            <span class="selected-pill-label">selected</span>
                        </span>
                    </div>
                </div>

                <div id="bulkStudentIds"></div>

                <div class="row g-3 bulk-fields-grid">

                    @php($bulkSchoolYearMin = now('Asia/Manila')->format('Y-m-d'))
                    <div class="col-xl-4 col-lg-6 bulk-col-school-year">
                        <label class="form-label">School Year</label>
                        <input type="hidden" name="school_year" id="bulk_school_year" value="">
                        <div class="d-flex gap-2 school-year-range">
                            <input type="date" class="form-control" id="bulk_school_year_start" min="{{ $bulkSchoolYearMin }}" aria-label="School year start">
                            <input type="date" class="form-control" id="bulk_school_year_end" min="{{ $bulkSchoolYearMin }}" aria-label="School year end">
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-6 bulk-col-term">
                        <label class="form-label">Term</label>
                        <select name="term" class="form-select">
                            <option value="">Skip</option>
                            @foreach(\App\Models\StudentTermAssignment::TERMS as $termOption)
                                <option value="{{ $termOption }}">{{ $termOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-3 bulk-col-section">
                        <label class="form-label">Section</label>
                        <select name="section" class="form-select">
                            <option value="">Skip</option>
                            @foreach(\App\Models\StudentTermAssignment::SECTIONS as $sectionOption)
                                <option value="{{ $sectionOption }}">{{ $sectionOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-3 bulk-col-hours">
                        <label class="form-label">Hours</label>
                        <input type="number" step="0.5" min="1" max="9999" name="required_ojt_hours" class="form-control" placeholder="Skip">
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-6 bulk-col-office">
                        <label class="form-label">Assigned Office</label>
                        <select name="assigned_office" class="form-select">
                            <option value="">Keep current</option>
                            @foreach(\App\Models\Student::getOfficeOptions() as $officeOption)
                                <option value="{{ $officeOption }}">{{ $officeOption }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="bulk-actions">
                    
                    <div class="d-flex flex-wrap gap-3 align-items-center bulk-actions-btns">
                        <button type="submit" class="btn bulk-assign-toolbar-btn bulk-assign-toolbar-btn--primary" id="bulkAssignSubmit" disabled>
                            <i class="bi bi-layers" aria-hidden="true"></i> Assign to selected
                        </button>
                        <button type="button" class="btn bulk-assign-toolbar-btn bulk-assign-toolbar-btn--danger" id="adminBulkDeleteBtn" disabled aria-disabled="true">
                            <i class="bi bi-trash" aria-hidden="true"></i> Delete selected
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <form id="adminDeleteBatchForm" method="POST" action="{{ route('admin.students.delete-batch') }}" class="d-none" aria-hidden="true">
            @csrf
            @if(!empty($search))
                <input type="hidden" name="q" value="{{ $search }}">
            @endif
            @if(!empty($selectedTerm))
                <input type="hidden" name="term" value="{{ $selectedTerm }}">
            @endif
            @if(!empty($selectedSection))
                <input type="hidden" name="section" value="{{ $selectedSection }}">
            @endif
            <div id="adminDeleteBatchIds"></div>
        </form>

        <p class="admin-students-scroll-hint text-muted small mb-2 mt-3" role="note">
            <span class="d-none d-md-inline"><i class="bi bi-arrows-expand" aria-hidden="true"></i> Scroll horizontally if columns are clipped. The header stays visible while you scroll the page.</span>
            <span class="d-md-none"><i class="bi bi-layout-text-sidebar-reverse" aria-hidden="true"></i> Each student appears as a card on small screens.</span>
        </p>

        <div class="table-responsive student-table-wrap admin-students-roster admin-students-scroll-container mt-2">
                <table class="table align-middle mb-0 admin-students-table">
                    <colgroup>
                        <col class="col-admin-check">
                        <col class="col-admin-student-no">
                        <col class="col-admin-name">
                        <col class="col-admin-ojt">
                        <col class="col-admin-status">
                        <col class="col-admin-office">
                        <col class="col-admin-required">
                        <col class="col-admin-remove">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="checkbox-cell">
                                <input type="checkbox" class="form-check-input" id="tableSelectAll" aria-label="Select all students shown">
                            </th>
                            <th>Student No</th>
                            <th>Name</th>
                            <th>Current OJT</th>
                            <th class="text-center text-nowrap">Status</th>
                            <th class="text-center text-nowrap">Assigned Office</th>
                            <th scope="col" class="admin-col-required text-center text-nowrap">Required hours</th>
                            <th scope="col" class="admin-col-remove text-center text-nowrap">Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            @php($activeAssignment = $student->activeTermAssignment)
                            @php($adminRowSearch = \Illuminate\Support\Str::lower(implode(' ', [
                                $student->student_no,
                                $student->name,
                                $student->display_course ?: '',
                                $activeAssignment?->term ?: '',
                                $activeAssignment?->section ?: '',
                                $activeAssignment?->school_year ?: '',
                                $student->assigned_office ?: '',
                            ])))
                            <tr class="admin-student-data-row" data-live-row data-live-search="{{ $adminRowSearch }}" data-live-name="{{ \Illuminate\Support\Str::lower($student->name) }}">
                                <td class="checkbox-cell" data-label="Select">
                                    <input type="checkbox" class="form-check-input student-select" value="{{ $student->id }}" aria-label="Select {{ $student->name }}">
                                </td>
                                <td class="fw-semibold" data-label="Student no.">{{ $student->student_no }}</td>
                                <td data-label="Name / course">
                                    <div class="fw-semibold">{{ $student->name }}</div>
                                    <div class="text-muted small">{{ $student->display_course ?: '-' }}</div>
                                </td>
                                <td class="admin-student-ojt-cell" data-label="Current OJT">
                                    <div class="admin-student-ojt-inner">
                                    @if($activeAssignment)
                                        <div class="fw-semibold">{{ $activeAssignment->term }}</div>
                                        <div class="text-muted small">
                                            {{ $activeAssignment->section }}
                                            @if($activeAssignment->school_year)
                                                - {{ $activeAssignment->school_year }}
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">No active term</span>
                                    @endif
                                    </div>
                                </td>
                                <td class="text-center" data-label="Status">
                                    <span class="admin-roster-status-badge badge {{ $student->isVerified() ? 'bg-success' : ($student->isPendingVerification() ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                        {{ $student->isVerified() ? 'Verified' : ($student->isPendingVerification() ? 'Pending' : 'Rejected') }}
                                    </span>
                                </td>
                                <td class="text-center admin-col-office" data-label="Assigned office">
                                    @if(!empty($student->assigned_office))
                                        <span class="admin-office-pill">{{ $student->assigned_office }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="admin-col-required text-center" data-label="Required hours">
                                    <span class="fw-semibold tabular-nums">{{ number_format((float) ($activeAssignment->required_ojt_hours ?? $student->current_required_hours ?? config('dtr.default_required_hours', 120)), 1) }}</span>
                                </td>
                                <td class="admin-col-remove text-center" data-label="Remove">
                                    <div class="admin-action-desktop">
                                        <form method="POST" action="{{ route('admin.students.destroy', $student) }}" class="admin-remove-student-form m-0" data-norsu-confirm="Archive student {{ e($student->student_no) }}? They will be hidden from lists until an admin restores them from Archived students." data-norsu-variant="danger">
                                            @csrf
                                            @method('DELETE')
                                            @if(!empty($search))
                                                <input type="hidden" name="q" value="{{ $search }}">
                                            @endif
                                            @if(!empty($selectedTerm))
                                                <input type="hidden" name="term" value="{{ $selectedTerm }}">
                                            @endif
                                            @if(!empty($selectedSection))
                                                <input type="hidden" name="section" value="{{ $selectedSection }}">
                                            @endif
                                            <button type="submit" class="btn-admin-remove-ghost" title="Archive this student (soft delete)">
                                                <i class="bi bi-trash" aria-hidden="true"></i> Remove
                                            </button>
                                        </form>
                                    </div>
                                    <div class="admin-action-mobile">
                                        <form method="POST" action="{{ route('admin.students.destroy', $student) }}" class="admin-remove-student-form m-0" data-norsu-confirm="Archive student {{ e($student->student_no) }}? They will be hidden from lists until an admin restores them from Archived students." data-norsu-variant="danger">
                                            @csrf
                                            @method('DELETE')
                                            @if(!empty($search))
                                                <input type="hidden" name="q" value="{{ $search }}">
                                            @endif
                                            @if(!empty($selectedTerm))
                                                <input type="hidden" name="term" value="{{ $selectedTerm }}">
                                            @endif
                                            @if(!empty($selectedSection))
                                                <input type="hidden" name="section" value="{{ $selectedSection }}">
                                            @endif
                                            <button type="submit" class="btn-admin-remove-ghost" title="Archive this student (soft delete)">
                                                <i class="bi bi-trash" aria-hidden="true"></i> Remove
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-secondary admin-mobile-details-toggle" data-student-details-toggle aria-expanded="false">View details</button>
                                        <div class="admin-mobile-details" data-student-details hidden>
                                            <div class="admin-mobile-details-label">Course</div>
                                            <div class="admin-mobile-details-value">{{ $student->display_course ?: '-' }}</div>
                                            <div class="admin-mobile-details-label mt-2">Current OJT</div>
                                            <div class="admin-mobile-details-value">
                                                @if($activeAssignment)
                                                    {{ $activeAssignment->term }} — {{ $activeAssignment->section }}@if($activeAssignment->school_year), {{ $activeAssignment->school_year }}@endif
                                                @else
                                                    No active term
                                                @endif
                                            </div>
                                            <div class="admin-mobile-details-label mt-2">Assigned office</div>
                                            <div class="admin-mobile-details-value">{{ $student->assigned_office ?: 'Not assigned' }}</div>
                                            <div class="admin-mobile-details-label mt-2">Required hours</div>
                                            <div class="admin-mobile-details-value">{{ number_format((float) ($activeAssignment->required_ojt_hours ?? $student->current_required_hours ?? config('dtr.default_required_hours', 120)), 1) }} hrs</div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="admin-student-empty-row">
                                <td colspan="8" class="text-muted">No students found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
    </div>
</div>

@endsection


@push('styles')
<style>
.toolbar.mb-3 {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
    min-width: 0;
}
@media (min-width: 992px) {
    .toolbar.mb-3 {
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-end;
    }
}
.student-filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 0.65rem 0.75rem;
    align-items: flex-end;
    justify-content: flex-start;
    width: 100%;
    max-width: 100%;
    min-width: 0;
}

.student-filter-form .search-inner {
    flex: 1 1 240px;
    min-width: 220px;
}

.filter-select {
    min-width: 150px;
    max-width: 180px;
}

.student-filter-form .btn-search {
    white-space: nowrap;
}
.bulk-assign-card {
    border: 1px solid var(--dtr-border-soft);
    border-radius: 18px;
    background: linear-gradient(135deg, rgba(45, 212, 191, 0.08), transparent 50%), var(--dtr-card-solid);
    padding: 1.15rem 1.2rem;
}

.bulk-assign-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}

.bulk-kicker {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--dtr-primary);
    font-weight: 700;
    margin-bottom: 0.2rem;
}

.bulk-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 800;
    color: var(--dtr-heading);
}

.bulk-sub,
.bulk-note {
    color: var(--dtr-muted);
    font-size: 0.86rem;
    line-height: 1.45;
}

.bulk-fields-grid {
    --bs-gutter-x: 0.75rem;
    --bs-gutter-y: 0.7rem;
    align-items: end;
}

.bulk-fields-grid > [class*="col-"] {
    display: flex;
    flex-direction: column;
    gap: 0.32rem;
    min-width: 0;
}

.bulk-assign-card .form-label {
    margin-bottom: 0;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--dtr-heading);
}

.school-year-range {
    display: grid !important;
    grid-template-columns: 1fr 1fr;
    gap: 0.55rem !important;
    width: 100%;
}

.school-year-range > .form-control {
    min-width: 0;
}

.bulk-col-office .form-select {
    max-width: 100%;
}

.bulk-assign-card .form-control,
.bulk-assign-card .form-select {
    width: 100%;
    max-width: 100%;
}

.bulk-meta {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.selected-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    min-width: 104px;
    padding: 0.46rem 0.85rem;
    border-radius: 999px;
    background: rgba(45, 212, 191, 0.12);
    border: 1px solid rgba(45, 212, 191, 0.2);
    color: var(--dtr-heading);
    font-size: 0.82rem;
    font-weight: 700;
}

.checkbox-cell .form-check-input {
    width: 1rem;
    height: 1rem;
    border-color: var(--dtr-input-border);
    background-color: var(--dtr-input-bg);
}

.bulk-actions {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--dtr-border-soft);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.bulk-actions .d-flex {
    align-items: center;
    justify-content: flex-end;
}

/* Bulk bar CTAs — matching pill ghosts: indigo assign + muted red delete (beats classic-ui .btn-primary) */
.layout-wrap .main-content .bulk-assign-toolbar-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0.5rem !important;
    padding: 0.55rem 1.25rem !important;
    min-height: 42px !important;
    border-radius: 999px !important;
    font-size: 0.875rem !important;
    font-weight: 600 !important;
    letter-spacing: 0.015em !important;
    line-height: 1.25 !important;
    font-family: inherit !important;
    box-sizing: border-box !important;
    cursor: pointer;
    transition: background 0.2s ease, border-color 0.2s ease, color 0.18s ease, transform 0.12s ease, box-shadow 0.2s ease !important;
}
.layout-wrap .main-content .bulk-assign-toolbar-btn i {
    font-size: 1.05rem !important;
    line-height: 1 !important;
}
.layout-wrap .main-content .bulk-assign-toolbar-btn--primary {
    border: 1px solid color-mix(in srgb, #6366f1 58%, var(--dtr-input-border) 42%) !important;
    box-shadow: none !important;
    background-color: transparent !important;
    background-image: none !important;
    color: color-mix(in srgb, #4f46e5 88%, var(--dtr-heading) 12%) !important;
}
.layout-wrap .main-content .bulk-assign-toolbar-btn--primary:hover:not(:disabled),
.layout-wrap .main-content .bulk-assign-toolbar-btn--primary:focus-visible:not(:disabled) {
    background-color: color-mix(in srgb, #6366f1 14%, var(--dtr-card-bg) 86%) !important;
    border-color: color-mix(in srgb, #4f46e5 72%, var(--dtr-input-border) 28%) !important;
    color: color-mix(in srgb, #4338ca 94%, var(--dtr-heading) 6%) !important;
}
.layout-wrap .main-content .bulk-assign-toolbar-btn--primary:focus-visible {
    outline: none !important;
    box-shadow:
        0 0 0 2px color-mix(in srgb, var(--dtr-card-bg) 100%, transparent),
        0 0 0 4px color-mix(in srgb, #818cf8 36%, transparent) !important;
}
.layout-wrap .main-content .bulk-assign-toolbar-btn--primary:active:not(:disabled) {
    transform: scale(0.985);
}
.layout-wrap .main-content .bulk-assign-toolbar-btn--primary:disabled {
    opacity: 0.45 !important;
    cursor: not-allowed !important;
    transform: none !important;
}
html[data-theme="dark"] .layout-wrap .main-content .bulk-assign-toolbar-btn--primary {
    border-color: color-mix(in srgb, #a5b4fc 52%, rgba(79, 70, 229, 0.45) 48%) !important;
    background-color: color-mix(in srgb, #312e81 26%, transparent) !important;
    color: color-mix(in srgb, #e0e7ff 78%, #c7d2fe 22%) !important;
}
html[data-theme="dark"] .layout-wrap .main-content .bulk-assign-toolbar-btn--primary:hover:not(:disabled),
html[data-theme="dark"] .layout-wrap .main-content .bulk-assign-toolbar-btn--primary:focus-visible:not(:disabled) {
    background-color: color-mix(in srgb, #4338ca 42%, transparent) !important;
    border-color: color-mix(in srgb, #a5b4fc 62%, var(--dtr-input-border) 38%) !important;
    color: #f5f7ff !important;
}
html[data-theme="dark"] .layout-wrap .main-content .bulk-assign-toolbar-btn--primary:focus-visible {
    box-shadow:
        0 0 0 2px color-mix(in srgb, var(--dtr-card-bg) 100%, transparent),
        0 0 0 4px color-mix(in srgb, #818cf8 38%, transparent) !important;
}
.layout-wrap .main-content .bulk-assign-toolbar-btn--danger {
    border: 1px solid color-mix(in srgb, #9f1239 65%, var(--dtr-input-border) 35%) !important;
    background-color: transparent !important;
    background-image: none !important;
    color: color-mix(in srgb, #9f1239 88%, var(--dtr-heading) 12%) !important;
    box-shadow: none !important;
}
.layout-wrap .main-content .bulk-assign-toolbar-btn--danger:hover:not(:disabled),
.layout-wrap .main-content .bulk-assign-toolbar-btn--danger:focus-visible:not(:disabled) {
    background-color: color-mix(in srgb, #9f1239 12%, var(--dtr-card-bg) 88%) !important;
    border-color: color-mix(in srgb, #881337 75%, var(--dtr-input-border) 25%) !important;
    color: color-mix(in srgb, #881337 95%, var(--dtr-heading) 5%) !important;
}
.layout-wrap .main-content .bulk-assign-toolbar-btn--danger:focus-visible {
    outline: none !important;
    box-shadow:
        0 0 0 2px color-mix(in srgb, var(--dtr-card-bg) 100%, transparent),
        0 0 0 4px color-mix(in srgb, #f43f5e 28%, transparent) !important;
}
.layout-wrap .main-content .bulk-assign-toolbar-btn--danger:active:not(:disabled) {
    transform: scale(0.985);
}
.layout-wrap .main-content .bulk-assign-toolbar-btn--danger:disabled {
    opacity: 0.45 !important;
    cursor: not-allowed !important;
    transform: none !important;
}
html[data-theme="dark"] .layout-wrap .main-content .bulk-assign-toolbar-btn--danger {
    border-color: color-mix(in srgb, #f87171 55%, rgba(127, 29, 29, 0.5) 45%) !important;
    color: color-mix(in srgb, #fecdd3 70%, #fda4af 30%) !important;
    background-color: color-mix(in srgb, #450a0a 22%, transparent) !important;
}
html[data-theme="dark"] .layout-wrap .main-content .bulk-assign-toolbar-btn--danger:hover:not(:disabled),
html[data-theme="dark"] .layout-wrap .main-content .bulk-assign-toolbar-btn--danger:focus-visible:not(:disabled) {
    background-color: color-mix(in srgb, #881337 45%, transparent) !important;
    border-color: color-mix(in srgb, #fb7185 65%, var(--dtr-input-border) 35%) !important;
    color: #fff1f2 !important;
}
html[data-theme="dark"] .layout-wrap .main-content .bulk-assign-toolbar-btn--danger:focus-visible {
    box-shadow:
        0 0 0 2px color-mix(in srgb, var(--dtr-card-bg) 100%, transparent),
        0 0 0 4px rgba(251, 113, 133, 0.35) !important;
}
@media (prefers-reduced-motion: reduce) {
    .layout-wrap .main-content .bulk-assign-toolbar-btn {
        transition: background 0.2s ease, border-color 0.2s ease, color 0.18s ease !important;
    }
    .layout-wrap .main-content .bulk-assign-toolbar-btn--primary:active:not(:disabled),
    .layout-wrap .main-content .bulk-assign-toolbar-btn--danger:active:not(:disabled) {
        transform: none;
    }
}

.student-table-wrap {
    border-radius: 16px;
}

/* Roster table — reference layout: header band, row rules, status/remove pills */
.admin-students-roster.student-table-wrap .admin-students-table {
    border-collapse: separate;
    border-spacing: 0;
}
.admin-students-roster.student-table-wrap .admin-students-table thead th {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--dtr-muted) !important;
    vertical-align: middle;
    padding-top: 0.95rem;
    padding-bottom: 0.95rem;
    border-bottom: 1px solid var(--dtr-border-soft);
}
html[data-theme="dark"] .admin-students-roster.student-table-wrap .admin-students-table thead,
html[data-theme="dark"] .admin-students-roster.student-table-wrap .admin-students-table thead th {
    background: color-mix(in srgb, var(--dtr-surface-soft) 55%, #0f172a 45%) !important;
}
.admin-students-roster.student-table-wrap .admin-students-table tbody td {
    padding-top: 0.85rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid var(--dtr-border-soft);
    vertical-align: middle;
}
.admin-students-roster.student-table-wrap .admin-students-table tbody tr:last-child td {
    border-bottom: none;
}
.admin-students-roster.student-table-wrap .admin-students-table tbody td:nth-child(2) {
    font-weight: 700;
    font-size: 0.98rem;
    letter-spacing: -0.02em;
    color: var(--dtr-heading) !important;
}
.admin-students-roster.student-table-wrap .admin-students-table tbody td:nth-child(3) .fw-semibold,
.admin-students-roster.student-table-wrap .admin-students-table tbody td:nth-child(4) .fw-semibold {
    color: var(--dtr-heading) !important;
    font-size: 0.96rem;
    line-height: 1.25;
}
.admin-students-roster.student-table-wrap .admin-students-table tbody td:nth-child(3) .text-muted.small,
.admin-students-roster.student-table-wrap .admin-students-table tbody td:nth-child(4) .text-muted.small {
    font-size: 0.78rem;
    line-height: 1.3;
    margin-top: 0.12rem;
}
.admin-students-roster.student-table-wrap .admin-students-table .admin-roster-status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-transform: uppercase;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    padding: 0.45rem 0.85rem;
    border-radius: 999px;
    border: 1px solid transparent;
    line-height: 1.2;
}
.admin-students-roster.student-table-wrap .admin-students-table .admin-roster-status-badge.bg-success {
    background: rgba(22, 101, 52, 0.92) !important;
    color: #ecfdf5 !important;
    border-color: rgba(52, 211, 153, 0.55);
}
html[data-theme="light"] .admin-students-roster.student-table-wrap .admin-students-table .admin-roster-status-badge.bg-success {
    background: rgba(22, 163, 74, 0.16) !important;
    color: #14532d !important;
    border-color: rgba(34, 197, 94, 0.42);
}
.admin-students-roster.student-table-wrap .admin-students-table .admin-roster-status-badge.bg-warning {
    border-color: rgba(234, 179, 8, 0.5);
}
.admin-students-roster.student-table-wrap .admin-students-table .admin-roster-status-badge.bg-secondary {
    border-color: rgba(148, 163, 184, 0.35);
}
.admin-students-roster.student-table-wrap .admin-students-table .checkbox-cell .form-check-input {
    border-radius: 6px;
    width: 1.05rem;
    height: 1.05rem;
}

/* Same column grid for thead + tbody so labels sit above values */
.student-table-wrap .admin-students-table {
    table-layout: fixed;
    width: 100%;
}

.student-table-wrap col.col-admin-check { width: 2.85rem; }
.student-table-wrap col.col-admin-student-no { width: 7.25rem; }
.student-table-wrap col.col-admin-name { width: 20%; }
.student-table-wrap col.col-admin-ojt { width: 16%; }
.student-table-wrap col.col-admin-status { width: 6.75rem; }
.student-table-wrap col.col-admin-office { width: 11.5rem; }
.student-table-wrap col.col-admin-required { width: 10rem; }
.student-table-wrap col.col-admin-remove { width: 9.5rem; }

.admin-students-scroll-hint {
    line-height: 1.45;
}
.admin-students-scroll-hint i {
    opacity: 0.85;
    margin-right: 0.15rem;
}
@media (min-width: 768px) {
    .admin-students-scroll-container.admin-students-roster.student-table-wrap {
        overflow-x: auto !important;
        border-radius: 14px;
        border: 1px solid var(--dtr-border-soft);
    }
    .admin-students-roster.student-table-wrap .admin-students-table {
        min-width: 52rem;
    }
    .admin-students-roster.student-table-wrap .admin-students-table thead th {
        position: sticky;
        top: 0;
        z-index: 4;
        background: var(--dtr-card-bg);
        box-shadow: 0 1px 0 var(--dtr-border-soft);
    }
    html[data-theme="dark"] .admin-students-roster.student-table-wrap .admin-students-table thead th {
        background: color-mix(in srgb, var(--dtr-surface-soft) 55%, #0f172a 45%) !important;
    }
}
.admin-students-roster .admin-student-ojt-inner {
    max-height: none;
    overflow: visible;
    padding-right: 0;
    line-height: 1.3;
}

.admin-office-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    max-width: 100%;
    padding: 0.33rem 0.62rem;
    border-radius: 999px;
    background: color-mix(in srgb, var(--dtr-primary) 16%, transparent 84%);
    border: 1px solid color-mix(in srgb, var(--dtr-primary) 36%, var(--dtr-border-soft) 64%);
    color: var(--dtr-heading);
    font-size: 0.74rem;
    font-weight: 600;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.admin-col-office {
    max-width: 0;
}

@media (max-width: 767.98px) {
    .admin-students-scroll-container.admin-students-roster.student-table-wrap {
        overflow-x: visible !important;
        border: none;
    }
    .admin-students-roster.student-table-wrap .admin-students-table {
        min-width: 0 !important;
        width: 100%;
        table-layout: auto;
    }
    .admin-students-roster.student-table-wrap .admin-students-table thead {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    .admin-students-roster.student-table-wrap .admin-students-table tbody tr.admin-student-data-row {
        display: block;
        margin-bottom: 1rem;
        padding: 1rem 1.1rem;
        border: 1px solid var(--dtr-border-soft);
        border-radius: 16px;
        background: var(--dtr-card-solid);
        box-shadow: var(--dtr-shadow-soft);
    }
    .admin-students-roster.student-table-wrap .admin-students-table tbody tr.admin-student-data-row > td {
        background-color: transparent !important;
    }
    .admin-students-roster.student-table-wrap .admin-students-table tbody tr.admin-student-data-row td {
        display: grid;
        grid-template-columns: minmax(6.75rem, 34%) 1fr;
        gap: 0.35rem 0.85rem;
        align-items: start;
        text-align: left !important;
        border-bottom: 1px solid var(--dtr-border-soft);
        padding: 0.55rem 0 !important;
        vertical-align: top;
    }
    .admin-students-roster.student-table-wrap .admin-students-table tbody tr.admin-student-data-row td:last-child {
        border-bottom: none;
        grid-column: 1 / -1;
        padding-top: 0.85rem !important;
        margin-top: 0.15rem;
        border-top: 1px dashed var(--dtr-border-soft);
    }
    .admin-students-roster.student-table-wrap .admin-students-table tbody tr.admin-student-data-row td::before {
        content: attr(data-label);
        font-weight: 700;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: var(--dtr-muted);
        padding-top: 0.2rem;
    }
    .admin-students-roster.student-table-wrap .admin-students-table tbody tr.admin-student-data-row td.admin-student-ojt-cell,
    .admin-students-roster.student-table-wrap .admin-students-table tbody tr.admin-student-data-row td.admin-col-required {
        display: none !important;
    }
    .admin-action-desktop {
        display: none !important;
    }
    .admin-action-mobile {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        justify-content: flex-start;
        width: 100%;
    }
    .admin-mobile-details-toggle[aria-expanded="true"] {
        background: var(--dtr-hover-bg);
        border-color: var(--dtr-input-border);
    }
    .admin-students-roster.student-table-wrap .admin-students-table tbody tr.admin-student-empty-row {
        display: table-row;
    }
    .admin-students-roster.student-table-wrap .admin-students-table tbody tr.admin-student-empty-row td {
        display: table-cell;
        padding: 1.5rem 1rem !important;
    }
    .admin-students-roster .admin-student-ojt-inner {
        max-height: none;
        overflow: visible;
    }
}

.checkbox-cell {
    width: 46px;
    text-align: center;
}

.tabular-nums {
    font-variant-numeric: tabular-nums;
}

/* Required hours: header + values centered in the column */
.student-table-wrap .admin-students-table thead th.admin-col-required,
.student-table-wrap .admin-students-table tbody td.admin-col-required {
    text-align: center !important;
    vertical-align: middle;
    white-space: nowrap;
    padding-left: 0.75rem;
    padding-right: 0.75rem;
}

/* Remove: header + buttons centered in the column */
.student-table-wrap .admin-students-table thead th.admin-col-remove,
.student-table-wrap .admin-students-table tbody td.admin-col-remove {
    text-align: center !important;
    vertical-align: middle;
    white-space: nowrap;
    padding-left: 0.75rem;
    padding-right: 0.75rem;
}

.student-table-wrap .admin-students-table thead th.admin-col-remove,
.student-table-wrap .admin-students-table thead th.admin-col-required {
    border-bottom: 1px solid var(--dtr-border-soft);
}

.admin-remove-student-form {
    display: block;
    text-align: center;
    margin: 0;
}
.admin-action-mobile {
    display: none;
}
.admin-mobile-details {
    width: 100%;
    margin-top: 0.45rem;
    padding: 0.7rem 0.8rem;
    border: 1px solid var(--dtr-border-soft);
    border-radius: 10px;
    background: var(--dtr-surface-soft);
    text-align: left;
}
.admin-mobile-details-label {
    font-size: 0.68rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--dtr-muted);
    font-weight: 700;
    margin-bottom: 0.15rem;
}
.admin-mobile-details-value {
    font-size: 0.88rem;
    color: var(--dtr-text);
    font-weight: 600;
}

.btn-admin-remove-ghost {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.38rem;
    padding: 0.4rem 1rem;
    font-size: 0.8125rem;
    font-weight: 600;
    font-family: inherit;
    line-height: 1.25;
    border-radius: 999px;
    border: 1px solid #ff1a1a;
    color: #ff1414;
    background: transparent;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
}

.btn-admin-remove-ghost:hover:not(:disabled) {
    background: rgba(255, 24, 24, 0.16);
    border-color: #ff0505;
    color: #ff0000;
}

.btn-admin-remove-ghost:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.btn-admin-remove-ghost:focus-visible {
    outline: 2px solid #ff2222;
    outline-offset: 2px;
}

html[data-theme="dark"] .btn-admin-remove-ghost {
    color: #fca5a5;
    border-color: #ef4444;
    background: rgba(15, 23, 42, 0.88);
}

html[data-theme="dark"] .btn-admin-remove-ghost:hover:not(:disabled) {
    background: rgba(127, 29, 29, 0.35);
    border-color: #f87171;
    color: #fecaca;
}


.student-term-modal {
    background: linear-gradient(180deg, color-mix(in srgb, var(--dtr-card-solid) 92%, white 8%), var(--dtr-card-solid));
    border: 1px solid var(--dtr-border-soft);
    border-radius: 24px;
    box-shadow: var(--dtr-shadow-strong);
    color: var(--dtr-text);
}

#studentTermModal .modal-dialog {
    max-width: 560px;
}

#studentTermModal .modal-header,
#studentTermModal .modal-body {
    padding-left: 1.35rem;
    padding-right: 1.35rem;
}

#studentTermModal .modal-title {
    color: var(--dtr-heading) !important;
    font-weight: 800;
    letter-spacing: -0.02em;
}

#studentTermModal .text-muted,
#studentTermModal .small {
    color: var(--dtr-muted) !important;
}

#studentTermModal .form-label {
    color: var(--dtr-heading);
    font-size: 0.82rem;
    font-weight: 700;
    margin-bottom: 0.45rem;
}

#studentTermModal .form-control,
#studentTermModal .form-select,
.bulk-assign-card .form-control,
.bulk-assign-card .form-select {
    min-height: 50px;
    border-radius: 14px;
    background: var(--dtr-input-bg);
    border: 1px solid var(--dtr-input-border);
    color: var(--dtr-text);
}

#studentTermModal .form-control::placeholder,
.bulk-assign-card .form-control::placeholder {
    color: var(--dtr-muted);
}

#studentTermModal .form-control:focus,
#studentTermModal .form-select:focus,
.bulk-assign-card .form-control:focus,
.bulk-assign-card .form-select:focus {
    background: color-mix(in srgb, var(--dtr-input-bg) 88%, white 12%);
    border-color: rgba(45, 212, 191, 0.72);
    box-shadow: 0 0 0 4px rgba(45, 212, 191, 0.14);
    color: var(--dtr-text);
}

#studentTermModal .btn-close {
    opacity: 0.8;
    filter: var(--modal-close-filter, none);
}

html[data-theme="dark"] #studentTermModal {
    --modal-close-filter: invert(1) grayscale(1) brightness(1.4);
}

html[data-theme="dark"] .student-term-modal {
    background:
        linear-gradient(180deg, rgba(15, 23, 42, 0.98), rgba(8, 15, 28, 0.96)),
        radial-gradient(circle at top right, rgba(45, 212, 191, 0.12), transparent 38%);
}

html[data-theme="light"] .student-term-modal {
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(244, 248, 251, 0.98)),
        radial-gradient(circle at top right, rgba(20, 184, 166, 0.08), transparent 38%);
}

@media (max-width: 768px) {
    .bulk-fields-grid {
        --bs-gutter-y: 0.85rem;
    }
    .school-year-range {
        grid-template-columns: 1fr;
    }
    .bulk-assign-head,
    .bulk-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .bulk-meta {
        justify-content: flex-start;
    }
}

@media (max-width: 1399.98px) {
    .school-year-range {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    var schoolYearHidden = document.getElementById('bulk_school_year');
    var schoolYearStart = document.getElementById('bulk_school_year_start');
    var schoolYearEnd = document.getElementById('bulk_school_year_end');
    var bulkMinDate = (schoolYearStart && schoolYearStart.getAttribute('min')) ? schoolYearStart.getAttribute('min').trim() : '';

    function extractYear(value) {
        if (!value || typeof value !== 'string' || value.length < 4) return null;
        var year = parseInt(value.slice(0, 4), 10);
        return Number.isFinite(year) ? year : null;
    }

    function syncBulkSchoolYear() {
        if (!schoolYearHidden || !schoolYearStart || !schoolYearEnd) return;
        var minD = bulkMinDate;
        if (schoolYearStart.value && minD && schoolYearStart.value < minD) {
            schoolYearStart.value = minD;
        }
        if (schoolYearEnd.value && minD && schoolYearEnd.value < minD) {
            schoolYearEnd.value = minD;
        }

        if (schoolYearStart.value && schoolYearEnd.value && schoolYearEnd.value < schoolYearStart.value) {
            schoolYearEnd.value = schoolYearStart.value;
        }

        var startYear = extractYear(schoolYearStart.value);
        var endYear = extractYear(schoolYearEnd.value);

        if (startYear && endYear) {
            schoolYearHidden.value = String(startYear) + '-' + String(endYear);
            return;
        }

        schoolYearHidden.value = '';
    }

    if (schoolYearStart && schoolYearEnd) {
        schoolYearStart.addEventListener('change', syncBulkSchoolYear);
        schoolYearEnd.addEventListener('change', syncBulkSchoolYear);
        syncBulkSchoolYear();
    }

    var rowCheckboxes = Array.prototype.slice.call(document.querySelectorAll('.student-select'));
    var tableSelectAll = document.getElementById('tableSelectAll');
    var countEl = document.getElementById('selectedStudentsCount');
    var hiddenContainer = document.getElementById('bulkStudentIds');
    var submitBtn = document.getElementById('bulkAssignSubmit');
    var bulkForm = document.getElementById('bulkStudentTermForm');
    var bulkDeleteBtn = document.getElementById('adminBulkDeleteBtn');
    var adminDeleteBatchForm = document.getElementById('adminDeleteBatchForm');
    var adminDeleteBatchIds = document.getElementById('adminDeleteBatchIds');
    var maxBatch = bulkForm ? parseInt(bulkForm.getAttribute('data-max-batch') || '40', 10) : 40;
    if (!maxBatch || maxBatch < 1) {
        maxBatch = 40;
    }
    var searchInput = document.querySelector('.student-filter-form input[name="q"]');
    var liveRows = Array.prototype.slice.call(document.querySelectorAll('tr[data-live-row]'));

    if (!rowCheckboxes.length || !countEl || !hiddenContainer || !submitBtn || !bulkForm) return;

    function syncSelection(sourceChecked) {
        rowCheckboxes.forEach(function (checkbox) {
            checkbox.checked = sourceChecked;
        });
        refreshSelectionUi();
    }

    function refreshSelectionUi() {
        var selected = rowCheckboxes.filter(function (checkbox) { return checkbox.checked; });
        countEl.textContent = String(selected.length);
        submitBtn.disabled = selected.length === 0;
        if (bulkDeleteBtn) {
            bulkDeleteBtn.disabled = selected.length === 0;
            bulkDeleteBtn.setAttribute('aria-disabled', selected.length === 0 ? 'true' : 'false');
        }

        hiddenContainer.innerHTML = '';
        selected.forEach(function (checkbox) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'student_ids[]';
            input.value = checkbox.value;
            hiddenContainer.appendChild(input);
        });

        var allChecked = selected.length > 0 && selected.length === rowCheckboxes.length;
        if (tableSelectAll) tableSelectAll.checked = allChecked;
    }

    rowCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', refreshSelectionUi);
    });

    if (tableSelectAll) {
        tableSelectAll.addEventListener('change', function () {
            syncSelection(tableSelectAll.checked);
        });
    }

    bulkForm.addEventListener('submit', async function (event) {
        if (bulkForm.dataset.promptBypass === '1') {
            bulkForm.dataset.promptBypass = '';
            return;
        }
        event.preventDefault();
        var selectedCount = rowCheckboxes.filter(function (checkbox) { return checkbox.checked; }).length;
        if (!selectedCount) {
            return;
        }
        var termSel = bulkForm.querySelector('select[name="term"]');
        var secSel = bulkForm.querySelector('select[name="section"]');
        var hrsEl = bulkForm.querySelector('input[name="required_ojt_hours"]');
        var officeSel = bulkForm.querySelector('select[name="assigned_office"]');
        var t = termSel && termSel.value;
        var s = secSel && secSel.value;
        var h = hrsEl && hrsEl.value && String(hrsEl.value).trim() !== '';
        var o = officeSel && officeSel.value;
        var sy = schoolYearHidden && schoolYearHidden.value && String(schoolYearHidden.value).trim() !== '';
        var fullTerm = t && s && h;
        var patchMeta = !t && !s && (h || sy);

        var bulkMsg;
        var bulkTitle = 'Bulk update';
        if (fullTerm && o) {
            bulkMsg = 'Assign a new OJT term and update assigned office for ' + selectedCount + ' selected student' + (selectedCount === 1 ? '' : 's') + '? Existing active terms will be completed first.';
        } else if (fullTerm) {
            bulkMsg = 'Assign this OJT term to ' + selectedCount + ' selected student' + (selectedCount === 1 ? '' : 's') + '? Existing active terms will be completed first.';
        } else if (patchMeta && o) {
            bulkMsg = 'Update hours and/or school year on the active assignment, and update assigned office, for ' + selectedCount + ' selected student' + (selectedCount === 1 ? '' : 's') + '? Students without an active assignment will be skipped for hours/school year.';
        } else if (patchMeta) {
            bulkMsg = 'Update hours and/or school year on the current active assignment for ' + selectedCount + ' selected student' + (selectedCount === 1 ? '' : 's') + '? Students without an active assignment will be skipped for that part.';
        } else if (o) {
            bulkMsg = 'Apply the selected assigned office to ' + selectedCount + ' selected student' + (selectedCount === 1 ? '' : 's') + '?';
            bulkTitle = 'Update assigned office';
        } else {
            bulkMsg = 'Apply this bulk update to ' + selectedCount + ' selected student' + (selectedCount === 1 ? '' : 's') + '?';
        }

        var ok = await window.norsuPrompt.confirm(
            bulkMsg,
            { variant: 'warning', title: bulkTitle, confirmText: 'Yes, apply' }
        );
        if (!ok) {
            return;
        }
        bulkForm.dataset.promptBypass = '1';
        if (typeof bulkForm.requestSubmit === 'function') bulkForm.requestSubmit(); else bulkForm.submit();
    });

    if (bulkDeleteBtn && adminDeleteBatchForm && adminDeleteBatchIds) {
        bulkDeleteBtn.addEventListener('click', async function () {
            var selected = rowCheckboxes.filter(function (checkbox) { return checkbox.checked; });
            if (selected.length === 0) {
                await window.norsuPrompt.alert('Select at least one student first.', { variant: 'warning', title: 'Nothing selected' });
                return;
            }
            if (selected.length > maxBatch) {
                await window.norsuPrompt.alert('You can remove at most ' + maxBatch + ' students per request.', { variant: 'warning', title: 'Too many selected' });
                return;
            }
            var ok = await window.norsuPrompt.confirm(
                'Archive ' + selected.length + ' selected student' + (selected.length === 1 ? '' : 's') + '? They will be hidden until an admin restores them from Archived students.',
                { variant: 'danger', title: 'Archive students', confirmText: 'Yes, archive' }
            );
            if (!ok) {
                return;
            }
            adminDeleteBatchIds.innerHTML = '';
            selected.forEach(function (checkbox) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'student_ids[]';
                input.value = checkbox.value;
                adminDeleteBatchIds.appendChild(input);
            });
            adminDeleteBatchForm.submit();
        });
    }

    function normalizePattern(s) {
        return (s || '').trim().replace(/%/g, '*');
    }
    function usesWildcardTokens(raw) {
        return /[*?%_]/.test((raw || '').trim());
    }
    function matchesWildcard(text, raw) {
        var p = normalizePattern(raw);
        if (!p) return true;
        var lower = (text || '').toLowerCase();
        var pattern = p.toLowerCase();
        if (pattern.indexOf('*') === -1) {
            return lower.indexOf(pattern) !== -1;
        }
        var parts = pattern.split('*');
        var pos = 0;
        for (var i = 0; i < parts.length; i++) {
            var seg = parts[i];
            if (!seg) continue;
            var j = lower.indexOf(seg, pos);
            if (j === -1) return false;
            pos = j + seg.length;
        }
        return true;
    }
    function levenshtein(a, b) {
        if (a === b) return 0;
        if (!a.length) return b.length;
        if (!b.length) return a.length;
        var v0 = new Array(b.length + 1);
        var v1 = new Array(b.length + 1);
        var i, j;
        for (j = 0; j <= b.length; j++) v0[j] = j;
        for (i = 0; i < a.length; i++) {
            v1[0] = i + 1;
            for (j = 0; j < b.length; j++) {
                var cost = a[i] === b[j] ? 0 : 1;
                v1[j + 1] = Math.min(v1[j] + 1, v0[j + 1] + 1, v0[j] + cost);
            }
            var t = v0; v0 = v1; v1 = t;
        }
        return v0[b.length];
    }
    function fuzzyNameMatch(name, query) {
        query = (query || '').trim().toLowerCase();
        if (!query) return false;
        var n = (name || '').trim().toLowerCase();
        if (!n) return false;
        if (n.indexOf(query) !== -1 || n.replace(/\s+/g, '').indexOf(query.replace(/\s+/g, '')) !== -1) return true;
        var words = n.split(/\s+/);
        for (var i = 0; i < words.length; i++) {
            var w = words[i];
            if (!w) continue;
            if (w.indexOf(query) !== -1 || query.indexOf(w) !== -1) return true;
            var max = Math.max(w.length, query.length, 1);
            if (max <= 255 && /^[\x00-\x7F]*$/.test(w) && /^[\x00-\x7F]*$/.test(query)) {
                if (levenshtein(w, query) <= Math.max(1, Math.floor(max * 0.28))) return true;
            }
        }
        return false;
    }
    function rowMatches(row, q) {
        var hay = row.getAttribute('data-live-search') || '';
        if (matchesWildcard(hay, q)) return true;
        if (usesWildcardTokens(q)) return false;
        return fuzzyNameMatch(row.getAttribute('data-live-name') || '', q);
    }
    function applyLiveFilter() {
        if (!searchInput || !liveRows.length) return;
        var q = searchInput.value || '';
        liveRows.forEach(function (row) {
            row.style.display = rowMatches(row, q) ? '' : 'none';
        });
    }
    if (searchInput) {
        searchInput.addEventListener('input', applyLiveFilter);
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') e.preventDefault();
        });
    }

    document.querySelectorAll('[data-student-details-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var host = btn.closest('.admin-action-mobile');
            if (!host) return;
            var details = host.querySelector('[data-student-details]');
            if (!details) return;
            var isExpanded = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
            btn.textContent = isExpanded ? 'View details' : 'Hide details';
            details.hidden = isExpanded;
        });
    });

    applyLiveFilter();
    refreshSelectionUi();
})();
</script>
@endpush


















