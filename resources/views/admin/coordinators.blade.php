@extends('layouts.admin')

@section('title', 'Coordinator Management')

@section('content')
@php
    $coordShown = $coordinators->count();
@endphp
<style>
    .coordinator-admin-card,
    .admin-coordinators-table {
        color: var(--dtr-text);
    }
    .admin-coordinators-table thead th {
        color: var(--dtr-heading) !important;
        border-bottom-color: var(--dtr-border-strong) !important;
    }
    .admin-coordinators-table tbody td {
        border-bottom-color: var(--dtr-row-divider) !important;
    }
    .coordinator-search .search-input {
        background: var(--dtr-input-bg);
        color: var(--dtr-text);
        border-color: var(--dtr-input-border);
    }
</style>
<div id="createCoordinatorOpenFlag" data-open="{{ old('form_context') === 'create_coordinator' ? '1' : '0' }}" class="d-none"></div>
<div id="programSectionMapData" data-map='@json($programSectionMap ?? [])' class="d-none"></div>
<h1 class="page-title">Coordinator Management</h1>
<p class="page-sub text-center">Create coordinator accounts, assign sections by term, and manage access.</p>

<div class="card coordinator-admin-card mb-4">
    <div class="card-body p-4 p-xl-4">
        <div class="coord-admin-toolbar">
            <div class="coord-admin-toolbar-intro">
                <h2 class="h5 mb-1 coord-admin-title">Coordinator accounts</h2>
                <p class="text-muted small mb-0">Each assignment links a coordinator to one program, school year, term, and section. Search by name, email, program, or college.</p>
            </div>
            <div class="coord-admin-toolbar-actions">
                <button type="button" class="btn btn-toolbar-cta-ghost btn-add-coordinator" data-bs-toggle="modal" data-bs-target="#createCoordinatorModal">
                    <i class="bi bi-person-plus" aria-hidden="true"></i> Add coordinator
                </button>
                <form action="{{ route('admin.coordinators') }}" method="GET" class="search-row coordinator-search" role="search">
                    <div class="search-inner">
                        <i class="bi bi-search search-icon" aria-hidden="true"></i>
                        <input type="text" name="q" class="search-input" placeholder="Name, email, program…" value="{{ $search ?? '' }}" autocomplete="off" aria-label="Search coordinators">
                        @if(!empty($search))
                            <a href="{{ route('admin.coordinators') }}" class="search-clear" aria-label="Clear search"><i class="bi bi-x-lg"></i></a>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary btn-search"><i class="bi bi-search" aria-hidden="true"></i> Search</button>
                    @if(!empty($search))
                        <a href="{{ route('admin.coordinators') }}" class="btn btn-outline-secondary btn-search">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="coord-roster-stats" role="status" aria-live="polite">
            <span class="coord-roster-stat-pill"><strong>{{ $coordShown }}</strong> {{ $coordShown === 1 ? 'coordinator' : 'coordinators' }}</span>
            @if(!empty($search))
                <span class="coord-roster-stat-pill coord-roster-stat-pill--filter">Search: <em class="coord-roster-stat-query">{{ $search }}</em></span>
            @endif
        </div>

        <p class="coord-roster-scroll-hint text-muted small mb-2 mt-3" role="note">
            <span class="d-none d-md-inline"><i class="bi bi-arrows-expand" aria-hidden="true"></i> Actions now wrap to keep everything visible in one page view.</span>
            <span class="d-md-none"><i class="bi bi-layout-text-sidebar-reverse" aria-hidden="true"></i> Each coordinator appears as a card on small screens.</span>
        </p>

        <div class="table-responsive student-table-wrap admin-coordinators-roster coord-roster-scroll-container mt-2">
            {{-- Block 100% wide so table % widths resolve to the scrollport; inline-block shrink-wrap caused short row borders in Edge --}}
            <div class="coord-roster-table-sizer">
            <table class="table align-middle mb-0 admin-coordinators-table">
                <colgroup>
                    <col class="col-coord-coordinator">
                    <col class="col-coord-college">
                    <col class="col-coord-assignments">
                    <col class="col-coord-status">
                    <col class="col-coord-actions">
                </colgroup>
                <thead>
                    <tr>
                        <th scope="col" class="coord-roster-col-coordinator">Coordinator</th>
                        <th scope="col" class="coord-roster-col-org">College</th>
                        <th scope="col" class="coord-roster-col-assign text-center">Assignments</th>
                        <th scope="col" class="coord-roster-col-status text-center text-nowrap">Status</th>
                        <th scope="col" class="coord-roster-col-actions text-center text-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coordinators as $coordinator)
                        <tr class="coord-roster-data-row">
                            <td class="coord-roster-col-coordinator" data-label="Coordinator">
                                <div class="coordinator-identity">
                                    <div class="identity-avatar">{{ strtoupper(substr($coordinator->name, 0, 1)) }}</div>
                                    <div class="identity-copy">
                                        <div class="identity-name fw-semibold">{{ $coordinator->name }}</div>
                                        <div class="identity-meta text-muted small" title="{{ $coordinator->email }}">{{ $coordinator->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="coord-roster-col-org" data-label="College / program">
                                <div class="coord-org-primary fw-semibold">{{ $coordinator->college ?: ($coordinator->major ?: '—') }}</div>
                                @if($coordinator->college && $coordinator->major)
                                    <div class="coord-org-secondary text-muted small">{{ $coordinator->major }}</div>
                                @endif
                            </td>
                            <td class="coord-roster-col-assign text-center" data-label="Assignments">
                                <div class="assignment-stack assignment-stack--bounded">
                                    @forelse($coordinator->assignments as $assignment)
                                        <div class="assignment-chip-wrap">
                                            <button
                                                type="button"
                                                class="assignment-chip assignment-edit-trigger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#assignmentModal"
                                                data-action="{{ route('admin.coordinators.assignments.update', $assignment) }}"
                                                data-mode="edit"
                                                data-name="{{ $coordinator->name }}"
                                                data-course="{{ $assignment->course }}"
                                                data-semester="{{ $assignment->semester }}"
                                                data-section="{{ $assignment->section }}"
                                                data-school-year="{{ $assignment->school_year }}"
                                                title="Edit assignment: {{ $assignment->course }} ({{ $assignment->semester }}, {{ $assignment->section }}@if($assignment->school_year), {{ $assignment->school_year }}@endif)"
                                            >
                                                <span class="assignment-chip-text">{{ $assignment->course }} ({{ $assignment->semester }}, {{ $assignment->section }}@if($assignment->school_year), {{ $assignment->school_year }}@endif)</span>
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form action="{{ route('admin.coordinators.assignments.remove', $assignment) }}" method="POST" class="assignment-chip-form">
                                                @csrf
                                                <button type="submit" class="assignment-chip assignment-chip-remove" title="Remove assignment: {{ $assignment->course }} ({{ $assignment->semester }}, {{ $assignment->section }}@if($assignment->school_year), {{ $assignment->school_year }}@endif)">
                                                    <span class="assignment-chip-text">Remove</span>
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @empty
                                        <span class="cell-value text-muted small">No section assignment yet</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="coord-roster-col-status text-center" data-label="Status">
                                <span class="admin-roster-status-badge badge {{ ($coordinator->is_active ?? true) ? 'bg-success' : 'badge-status-inactive' }}">
                                    {{ ($coordinator->is_active ?? true) ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="coord-roster-col-actions text-center" data-label="Actions">
                                <div class="coord-actions-toolbar-wrap">
                                    <div class="coord-action-toolbar coord-action-toolbar-desktop" role="group" aria-label="Coordinator actions">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary coord-roster-action-btn coordinator-assignment-trigger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#assignmentModal"
                                            data-action="{{ route('admin.coordinators.assignments.store', $coordinator) }}"
                                            data-mode="create"
                                            data-name="{{ $coordinator->name }}"
                                            data-course="{{ $coordinator->major }}"
                                        >
                                            Assign
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary coord-roster-action-btn coordinator-password-trigger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#coordinatorPasswordModal"
                                            data-action="{{ route('admin.coordinators.password', $coordinator) }}"
                                            data-name="{{ $coordinator->name }}"
                                        >
                                            Reset password
                                        </button>
                                        <form action="{{ route('admin.coordinators.toggle', $coordinator) }}" method="POST" class="coord-action-form">
                                            @csrf
                                            <button type="submit" class="btn btn-sm coord-roster-action-btn {{ ($coordinator->is_active ?? true) ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                {{ ($coordinator->is_active ?? true) ? 'Disable' : 'Enable' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.coordinators.destroy', $coordinator) }}" method="POST" class="coord-action-form admin-remove-coordinator-form m-0" data-norsu-confirm="Remove this coordinator account? All section assignments will be removed. This cannot be undone." data-norsu-variant="danger">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary coord-roster-action-btn coord-roster-remove-btn" title="Remove this coordinator account">
                                                <i class="bi bi-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <div class="coord-action-toolbar-mobile" aria-label="Coordinator actions mobile">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary coord-roster-action-btn coordinator-assignment-trigger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#assignmentModal"
                                            data-action="{{ route('admin.coordinators.assignments.store', $coordinator) }}"
                                            data-mode="create"
                                            data-name="{{ $coordinator->name }}"
                                            data-course="{{ $coordinator->major }}"
                                        >
                                            Assign
                                        </button>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle coord-mobile-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                                More
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end coord-mobile-more-menu">
                                                <li>
                                                    <button
                                                        type="button"
                                                        class="dropdown-item coordinator-password-trigger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#coordinatorPasswordModal"
                                                        data-action="{{ route('admin.coordinators.password', $coordinator) }}"
                                                        data-name="{{ $coordinator->name }}"
                                                    >Reset password</button>
                                                </li>
                                                <li>
                                                    <form action="{{ route('admin.coordinators.toggle', $coordinator) }}" method="POST" class="m-0">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">{{ ($coordinator->is_active ?? true) ? 'Disable' : 'Enable' }}</button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('admin.coordinators.destroy', $coordinator) }}" method="POST" class="admin-remove-coordinator-form m-0" data-norsu-confirm="Remove this coordinator account? All section assignments will be removed. This cannot be undone." data-norsu-variant="danger">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">Remove</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary coord-mobile-details-toggle" data-coord-details-toggle>
                                            View details
                                        </button>
                                        <div class="coord-mobile-details" data-coord-details hidden>
                                            <div class="coord-mobile-details-block">
                                                <div class="coord-mobile-details-label">College / Program</div>
                                                <div class="coord-mobile-details-value">{{ $coordinator->college ?: ($coordinator->major ?: '—') }}</div>
                                                @if($coordinator->college && $coordinator->major)
                                                    <div class="coord-mobile-details-sub">{{ $coordinator->major }}</div>
                                                @endif
                                            </div>
                                            <div class="coord-mobile-details-block">
                                                <div class="coord-mobile-details-label">Assignments</div>
                                                @if($coordinator->assignments->isNotEmpty())
                                                    <ul class="coord-mobile-details-list">
                                                        @foreach($coordinator->assignments as $assignment)
                                                            <li>{{ $assignment->course }} ({{ $assignment->semester }}, {{ $assignment->section }}@if($assignment->school_year), {{ $assignment->school_year }}@endif)</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <div class="coord-mobile-details-sub">No section assignment yet</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="coord-roster-empty-row">
                            <td colspan="5" class="empty-state-cell">
                                <div class="coord-empty-state">
                                    <div class="coord-empty-icon" aria-hidden="true"><i class="bi bi-people"></i></div>
                                    <p class="coord-empty-title mb-1">No coordinators match this view</p>
                                    <p class="coord-empty-copy mb-0">
                                        @if(!empty($search))
                                            Try another keyword or <a href="{{ route('admin.coordinators') }}">clear the search</a>.
                                        @else
                                            Add the first coordinator with <strong>Add coordinator</strong>.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createCoordinatorModal" tabindex="-1" aria-labelledby="createCoordinatorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable create-coordinator-dialog">
        <div class="modal-content coordinator-modal create-coordinator-modal">
            <div class="modal-header border-0 flex-wrap align-items-start gap-3 pb-0 position-relative">
                <div class="create-coordinator-header create-coordinator-header--modal w-100 pe-4">
                    <div class="create-coordinator-header-icon" aria-hidden="true">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="create-coordinator-header-copy">
                        <div class="panel-kicker">Create</div>
                        <h2 class="modal-title create-coordinator-title h5 mb-1" id="createCoordinatorModalLabel">New coordinator</h2>
                        <p class="create-coordinator-lead mb-0">Set up login, college/program, and optionally link a term and section now—or assign later from the directory.</p>
                    </div>
                </div>
                <button type="button" class="btn-close create-coordinator-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3 pb-2">
                @php $createFormCtx = old('form_context') === 'create_coordinator'; @endphp
                <form method="POST" action="{{ route('admin.coordinators.store') }}" id="createCoordinatorForm" class="coordinator-form coordinator-create-form" autocomplete="on">
                    @csrf
                    <input type="hidden" name="form_context" value="create_coordinator">

                    <fieldset class="coordinator-form-section coordinator-form-section--account">
                        <legend class="form-section-legend"><i class="bi bi-person-vcard" aria-hidden="true"></i> Account</legend>
                        <input type="hidden" name="name" id="coord_name" value="{{ $createFormCtx ? old('name') : '' }}">
                        <div class="name-fields-grid">
                            <div class="form-block">
                                <label class="form-label" for="coord_first_name">First name <span class="text-danger" aria-hidden="true">*</span></label>
                                <input type="text" name="first_name" id="coord_first_name" class="form-control {{ $createFormCtx && $errors->has('first_name') ? 'is-invalid' : '' }}" required value="{{ $createFormCtx ? old('first_name') : '' }}" autocomplete="given-name" placeholder="e.g. Maria">
                                @if($createFormCtx) @error('first_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
                            </div>
                            <div class="form-block">
                                <label class="form-label" for="coord_middle_name">Middle name</label>
                                <input type="text" name="middle_name" id="coord_middle_name" class="form-control {{ $createFormCtx && $errors->has('middle_name') ? 'is-invalid' : '' }}" value="{{ $createFormCtx ? old('middle_name') : '' }}" autocomplete="additional-name" placeholder="Optional">
                                @if($createFormCtx) @error('middle_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
                            </div>
                            <div class="form-block">
                                <label class="form-label" for="coord_last_name">Last name <span class="text-danger" aria-hidden="true">*</span></label>
                                <input type="text" name="last_name" id="coord_last_name" class="form-control {{ $createFormCtx && $errors->has('last_name') ? 'is-invalid' : '' }}" required value="{{ $createFormCtx ? old('last_name') : '' }}" autocomplete="family-name" placeholder="e.g. Santos">
                                @if($createFormCtx) @error('last_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
                            </div>
                            <div class="form-block">
                                <label class="form-label" for="coord_suffix">Suffix</label>
                                <select name="suffix" id="coord_suffix" class="form-select {{ $createFormCtx && $errors->has('suffix') ? 'is-invalid' : '' }}">
                                    <option value="">None</option>
                                    @foreach(['Jr.', 'Sr.', 'II', 'III', 'IV'] as $suffixOption)
                                        <option value="{{ $suffixOption }}" {{ ($createFormCtx ? old('suffix') : '') === $suffixOption ? 'selected' : '' }}>{{ $suffixOption }}</option>
                                    @endforeach
                                </select>
                                @if($createFormCtx) @error('suffix')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
                            </div>
                            <div class="form-block">
                                <label class="form-label" for="coord_degree">Degree (optional)</label>
                                <select name="degree" id="coord_degree" class="form-select {{ $createFormCtx && $errors->has('degree') ? 'is-invalid' : '' }}">
                                    <option value="">None</option>
                                    @foreach(['BA', 'BS', 'MA', 'MS', 'MBA', 'PhD', 'EdD'] as $degreeOption)
                                        <option value="{{ $degreeOption }}" {{ ($createFormCtx ? old('degree') : '') === $degreeOption ? 'selected' : '' }}>{{ $degreeOption }}</option>
                                    @endforeach
                                </select>
                                @if($createFormCtx) @error('degree')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
                            </div>
                        </div>
                        @if($createFormCtx) @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
                        <div class="form-field-hint">Preview name is auto-built from these fields.</div>
                        <div class="name-preview-pill" id="coord_name_preview">—</div>
                        <div class="form-block">
                            <label class="form-label" for="coord_email">Email address <span class="text-danger" aria-hidden="true">*</span></label>
                            <input type="email" name="email" id="coord_email" class="form-control {{ $createFormCtx && $errors->has('email') ? 'is-invalid' : '' }}" required value="{{ $createFormCtx ? old('email') : '' }}" autocomplete="email" placeholder="Email address for login">
                            @if($createFormCtx) @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
                        </div>
                    </fieldset>

                    <fieldset class="coordinator-form-section coordinator-form-section--organization">
                        <legend class="form-section-legend"><i class="bi bi-building" aria-hidden="true"></i> Organization</legend>
                        <div class="form-block">
                            <label class="form-label" for="coord_college">College</label>
                            @php
                                $collegeOptions = array_values(array_filter(array_map('trim', array_keys(\App\Models\Student::getProgramCatalog()))));
                                $oldCollege = $createFormCtx ? trim((string) old('college', '')) : '';
                            @endphp
                            <select name="college" id="coord_college" class="form-select {{ $createFormCtx && $errors->has('college') ? 'is-invalid' : '' }}">
                                <option value="">Select college</option>
                                @foreach($collegeOptions as $collegeOption)
                                    <option value="{{ $collegeOption }}" {{ $oldCollege === $collegeOption ? 'selected' : '' }}>{{ $collegeOption }}</option>
                                @endforeach
                                @if($oldCollege !== '' && !in_array($oldCollege, $collegeOptions, true))
                                    <option value="{{ $oldCollege }}" selected>{{ $oldCollege }}</option>
                                @endif
                            </select>
                            <div class="form-field-hint">Optional. Shown in the directory.</div>
                            @if($createFormCtx) @error('college')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
                        </div>
                        <div class="form-block mb-0">
                            <label class="form-label" for="coord_major">Program / course <span class="text-danger" aria-hidden="true">*</span></label>
                            <select name="major" id="coord_major" class="form-select {{ $createFormCtx && $errors->has('major') ? 'is-invalid' : '' }}" required>
                                @php $oldMajor = $createFormCtx ? old('major') : null; @endphp
                                <option value="" disabled {{ $oldMajor ? '' : 'selected' }}>Choose program</option>
                                @foreach(\App\Models\Student::getProgramOptions() as $programOption)
                                    <option value="{{ $programOption }}" {{ $oldMajor == $programOption ? 'selected' : '' }}>{{ $programOption }}</option>
                                @endforeach
                                <option value="__custom__" {{ $createFormCtx && !empty(old('custom_major')) ? 'selected' : '' }}>Other (type program)</option>
                            </select>
                            <input type="text" name="custom_major" id="coord_custom_major" class="form-control mt-2 {{ $createFormCtx && $errors->has('custom_major') ? 'is-invalid' : '' }} {{ $createFormCtx && !empty(old('custom_major')) ? '' : 'd-none' }}" value="{{ $createFormCtx ? old('custom_major') : '' }}" placeholder="Enter custom program/course">
                            @if($createFormCtx) @error('major')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
                            @if($createFormCtx) @error('custom_major')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
                        </div>
                    </fieldset>

                    <fieldset class="coordinator-form-section coordinator-form-section--assignment">
                        <legend class="form-section-legend"><i class="bi bi-calendar3" aria-hidden="true"></i> Initial OJT assignment</legend>
                        <p class="form-section-intro">Optional. You can add or change assignments anytime with <strong>Assign section</strong> in the table.</p>
                        <div class="assignment-row assignment-row-create">
                            <div class="form-block mb-0">
                                <label class="form-label" for="coord_school_year">School year</label>
                                <input type="hidden" name="school_year" id="coord_school_year" value="{{ $createFormCtx ? old('school_year') : '' }}">
                                <div class="school-year-range-grid">
                                    <input
                                        type="date"
                                        id="coord_school_year_start"
                                        class="form-control school-year-date-picker {{ $createFormCtx && $errors->has('school_year') ? 'is-invalid' : '' }}"
                                        data-school-year-start
                                        data-school-year-target="coord_school_year"
                                        data-school-year-end="coord_school_year_end"
                                        data-school-year-preset="{{ $createFormCtx ? old('school_year') : '' }}"
                                        aria-label="School year start"
                                    />
                                    <input
                                        type="date"
                                        id="coord_school_year_end"
                                        class="form-control school-year-date-picker {{ $createFormCtx && $errors->has('school_year') ? 'is-invalid' : '' }}"
                                        data-school-year-end
                                        data-school-year-target="coord_school_year"
                                        data-school-year-start="coord_school_year_start"
                                        data-school-year-preset="{{ $createFormCtx ? old('school_year') : '' }}"
                                        aria-label="School year end"
                                    />
                                </div>
                                <div class="form-field-hint">Pick start and end dates from calendar.</div>
                                @if($createFormCtx) @error('school_year')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
                            </div>
                            <div class="form-block mb-0">
                                <label class="form-label" for="coord_semester">Term</label>
                                <select name="semester" id="coord_semester" class="form-select {{ $createFormCtx && $errors->has('semester') ? 'is-invalid' : '' }}">
                                    <option value="">— Later —</option>
                                    @foreach(\App\Models\Student::ASSIGNMENT_TERMS as $semesterOption)
                                        <option value="{{ $semesterOption }}" {{ $createFormCtx && old('semester') == $semesterOption ? 'selected' : '' }}>{{ $semesterOption }}</option>
                                    @endforeach
                                </select>
                                @if($createFormCtx) @error('semester')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
                            </div>
                            <div class="form-block mb-0">
                                <label class="form-label" for="coord_section">Section</label>
                                <select name="section" id="coord_section" class="form-select {{ $createFormCtx && $errors->has('section') ? 'is-invalid' : '' }}">
                                    <option value="">— Later —</option>
                                    <option value="All" {{ $createFormCtx && old('section') === 'All' ? 'selected' : '' }}>All</option>
                                    @foreach(\App\Models\Student::getSectionOptions() as $sectionOption)
                                        <option value="{{ $sectionOption }}" {{ $createFormCtx && old('section') == $sectionOption ? 'selected' : '' }}>{{ \App\Models\Student::sectionOptionLabel($sectionOption) }}</option>
                                    @endforeach
                                  
                                </select>
                                <input type="text" name="custom_section" id="coord_custom_section" class="form-control mt-2 {{ $createFormCtx && $errors->has('custom_section') ? 'is-invalid' : '' }} {{ $createFormCtx && !empty(old('custom_section')) ? '' : 'd-none' }}" value="{{ $createFormCtx ? old('custom_section') : '' }}" placeholder="Enter custom section">
                                @if($createFormCtx) @error('section')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
                                @if($createFormCtx) @error('custom_section')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="coordinator-form-section coordinator-form-section--security mb-0">
                        <legend class="form-section-legend"><i class="bi bi-shield-lock" aria-hidden="true"></i> Security</legend>
                        <div class="form-block">
                            <label class="form-label" for="coord_password">Password <span class="text-danger" aria-hidden="true">*</span></label>
                            <div class="password-field-wrap">
                                <input type="password" name="password" id="coord_password" class="form-control {{ $createFormCtx && $errors->has('password') ? 'is-invalid' : '' }}" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters">
                                <button type="button" class="password-reveal-btn" data-password-target="coord_password" aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
                            </div>
                            <div class="form-field-hint">Use at least 8 characters.</div>
                            @if($createFormCtx) @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
                        </div>
                        <div class="form-block mb-0">
                            <label class="form-label" for="coord_password_confirmation">Confirm password <span class="text-danger" aria-hidden="true">*</span></label>
                            <div class="password-field-wrap">
                                <input type="password" name="password_confirmation" id="coord_password_confirmation" class="form-control {{ $createFormCtx && $errors->has('password_confirmation') ? 'is-invalid' : '' }}" required minlength="8" autocomplete="new-password" placeholder="Re-enter password">
                                <button type="button" class="password-reveal-btn" data-password-target="coord_password_confirmation" aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
                            </div>
                            <div class="password-match-indicator" id="coord_password_match_indicator" aria-live="polite"></div>
                            @if($createFormCtx) @error('password_confirmation')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
                        </div>
                    </fieldset>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 flex-column flex-sm-row gap-2 align-items-center">
                <p class="create-form-footnote text-sm-start text-center order-sm-0 order-1 mb-0 me-sm-auto flex-grow-1">Active coordinators can sign in right away.</p>
                <button type="button" class="btn dtr-mbtn dtr-mbtn--cancel order-sm-1" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="createCoordinatorForm" class="btn btn-primary dtr-mbtn order-sm-2 coord-modal-submit">
                    <i class="bi bi-check-lg" aria-hidden="true"></i> Create account
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="assignmentModal" tabindex="-1" aria-labelledby="assignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content coordinator-modal coordinator-modal--side">
            <div class="modal-header border-0 pb-0 position-relative align-items-start">
                <div class="coord-modal-head">
                    <div class="coord-modal-head-icon" aria-hidden="true"><i class="bi bi-diagram-3"></i></div>
                    <div>
                        <div class="panel-kicker mb-2">Assignment</div>
                        <h2 class="modal-title h5 mb-1 text-start" id="assignmentModalLabel">Assign section</h2>
                        <p class="text-muted small text-start mb-0" id="assignmentModalHelp">Link this coordinator to one or more programs for one term and section.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3 pb-2">
                <form method="POST" id="assignmentForm" class="coordinator-form compact-form">
                    @csrf
                    <div class="form-block">
                        <label class="form-label" for="assignmentCourse">Program / course</label>
                        <select name="courses[]" id="assignmentCourse" class="form-select" multiple size="6" required>
                            @foreach(\App\Models\Student::getProgramOptions() as $programOption)
                                <option value="{{ $programOption }}">{{ $programOption }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Tip: hold Ctrl (or Cmd on Mac) to select multiple programs.</div>
                    </div>
                    <div class="assignment-row assignment-row-modal">
                        <div class="form-block mb-0">
                            <label class="form-label" for="assignmentSchoolYear">School year</label>
                            <input type="hidden" name="school_year" id="assignmentSchoolYear" value="{{ old('school_year') }}">
                            <div class="school-year-range-grid">
                                <input
                                    type="date"
                                    id="assignmentSchoolYearStart"
                                    class="form-control school-year-date-picker"
                                    data-school-year-start
                                    data-school-year-target="assignmentSchoolYear"
                                    data-school-year-end="assignmentSchoolYearEnd"
                                    data-school-year-preset="{{ old('school_year') }}"
                                    aria-label="Assignment school year start"
                                />
                                <input
                                    type="date"
                                    id="assignmentSchoolYearEnd"
                                    class="form-control school-year-date-picker"
                                    data-school-year-end
                                    data-school-year-target="assignmentSchoolYear"
                                    data-school-year-start="assignmentSchoolYearStart"
                                    data-school-year-preset="{{ old('school_year') }}"
                                    aria-label="Assignment school year end"
                                />
                            </div>
                            <div class="form-text">Calendar picker with month/day. Stored as YYYY-YYYY.</div>
                        </div>
                        <div class="form-block mb-0">
                            <label class="form-label" for="assignmentSemester">Term</label>
                            <select name="semester" id="assignmentSemester" class="form-select" required>
                                <option value="">Select</option>
                                @foreach(\App\Models\Student::ASSIGNMENT_TERMS as $semesterOption)
                                <option value="{{ $semesterOption }}">{{ $semesterOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-block mb-0">
                            <label class="form-label" for="assignmentSection">Section</label>
                            @php
                                $assignmentSectionOptions = collect(\App\Models\Student::getSectionOptions())
                                    ->map(function ($sectionOption) {
                                        $raw = trim((string) $sectionOption);
                                        $clean = trim((string) (preg_replace('/^section\s+/i', '', $raw) ?? $raw));
                                        return [
                                            'value' => $clean,
                                            'canonical' => mb_strtoupper($clean, 'UTF-8'),
                                        ];
                                    })
                                    ->filter(fn ($option) => $option['value'] !== '')
                                    ->unique('canonical')
                                    ->sortBy(fn ($option) => $option['canonical'] === 'ALL' ? '0' : '1-' . $option['canonical'])
                                    ->values();
                            @endphp
                            <select name="section" id="assignmentSection" class="form-select" required>
                                <option value="">Select</option>
                                <option value="All">All</option>
                                @foreach($assignmentSectionOptions as $sectionOption)
                                @continue($sectionOption['canonical'] === 'ALL')
                                <option value="{{ $sectionOption['value'] }}">{{ \App\Models\Student::sectionOptionLabel($sectionOption['value']) }}</option>
                                @endforeach
                                
                            </select>
                            <input type="text" name="custom_section" id="assignmentCustomSection" class="form-control mt-2 d-none" placeholder="Enter custom section">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 coord-modal-footer">
                <button type="button" class="btn dtr-mbtn dtr-mbtn--cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="assignmentForm" class="btn btn-primary dtr-mbtn coord-modal-submit">
                    <i class="bi bi-check-lg" aria-hidden="true"></i> Save assignment
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="coordinatorPasswordModal" tabindex="-1" aria-labelledby="coordinatorPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content coordinator-modal coordinator-modal--side">
            <div class="modal-header border-0 pb-0 position-relative align-items-start">
                <div class="coord-modal-head">
                    <div class="coord-modal-head-icon" aria-hidden="true"><i class="bi bi-shield-lock"></i></div>
                    <div>
                        <div class="panel-kicker mb-2">Security</div>
                        <h2 class="modal-title h5 mb-1 text-start" id="coordinatorPasswordModalLabel">Reset password</h2>
                        <p class="text-muted small text-start mb-0" id="coordinatorPasswordHelp">Set a new password for this coordinator account.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3 pb-2">
                <form method="POST" id="coordinatorPasswordForm" class="coordinator-form compact-form" autocomplete="off">
                    @csrf
                    <div class="form-block">
                        <label class="form-label" for="coord_pwd_new">New password</label>
                        <div class="password-field-wrap">
                            <input type="password" name="password" id="coord_pwd_new" class="form-control" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters">
                            <button type="button" class="password-reveal-btn" data-password-target="coord_pwd_new" aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
                        </div>
                        <div class="form-field-hint">Use at least 8 characters.</div>
                    </div>
                    <div class="form-block mb-0">
                        <label class="form-label" for="coord_pwd_confirm">Confirm password</label>
                        <div class="password-field-wrap">
                            <input type="password" name="password_confirmation" id="coord_pwd_confirm" class="form-control" required minlength="8" autocomplete="new-password" placeholder="Re-enter password">
                            <button type="button" class="password-reveal-btn" data-password-target="coord_pwd_confirm" aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
                        </div>
                        <div class="password-match-indicator" id="coord_reset_password_match_indicator" aria-live="polite"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 coord-modal-footer">
                <button type="button" class="btn dtr-mbtn dtr-mbtn--cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="coordinatorPasswordForm" class="btn btn-primary dtr-mbtn coord-modal-submit">
                    <i class="bi bi-check-lg" aria-hidden="true"></i> Save password
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Same as students admin card: no overflow trap so sticky thead can follow page scroll */
    .coordinator-admin-card {
        border: 1px solid var(--dtr-border-soft);
        border-radius: 18px;
        background: var(--dtr-card-bg);
        box-shadow: var(--dtr-shadow-soft);
    }
    .coord-admin-title {
        color: var(--dtr-heading);
        font-weight: 700;
        letter-spacing: -0.02em;
    }
    .coord-admin-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1.1rem;
        margin-bottom: 0.75rem;
    }
    .coord-admin-toolbar-intro {
        flex: 1 1 16rem;
        min-width: 0;
        max-width: 36rem;
        overflow-wrap: break-word;
        word-break: normal;
    }
    .coord-admin-toolbar-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.65rem;
        justify-content: flex-end;
        flex: 1 1 auto;
        min-width: 0;
    }
    .coord-admin-toolbar-actions > .btn-add-coordinator {
        flex-shrink: 0;
    }
    .coordinator-admin-card form.search-row.coordinator-search {
        display: flex;
        flex-wrap: nowrap;
        align-items: stretch;
        gap: 0.6rem;
        flex: 1 1 min(560px, 100%);
        min-width: 0;
        max-width: 100%;
        margin: 0;
    }
    .coordinator-search .search-inner {
        position: relative;
        flex: 1 1 auto;
        min-width: 0;
    }
    .coordinator-search .search-icon {
        position: absolute;
        left: 0.72rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--dtr-muted);
        font-size: 0.95rem;
        pointer-events: none;
        z-index: 2;
    }
    .coordinator-search .search-input {
        display: block;
        width: 100%;
        padding-left: 2.45rem;
        padding-right: 2.35rem;
        min-height: 40px;
        border-radius: 10px;
        border: 1px solid var(--dtr-input-border);
        background: var(--dtr-input-bg);
        color: var(--dtr-text);
        font-size: 0.9rem;
    }
    .coordinator-search .search-input::placeholder {
        color: var(--dtr-muted);
    }
    .coordinator-search .search-clear {
        position: absolute;
        right: 0.35rem;
        top: 50%;
        transform: translateY(-50%);
        z-index: 3;
        width: 28px;
        height: 28px;
        border: none;
        border-radius: 8px;
        background: transparent;
        color: var(--dtr-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: color 0.15s ease, background 0.15s ease;
    }
    .coordinator-search .search-clear:hover {
        color: var(--dtr-text);
        background: var(--dtr-hover-bg);
    }
    .coordinator-search .btn-search {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        font-weight: 600;
    }
    .coord-roster-stats {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
    }
    .coord-roster-stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.32rem 0.7rem;
        border-radius: 999px;
        font-size: 0.78rem;
        color: var(--dtr-muted);
        background: var(--dtr-surface-soft);
        border: 1px solid var(--dtr-border-soft);
    }
    .coord-roster-stat-pill strong {
        color: var(--dtr-heading);
        font-weight: 700;
    }
    .coord-roster-stat-pill--filter {
        max-width: 100%;
    }
    .coord-roster-stat-query {
        font-style: normal;
        color: var(--dtr-heading);
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: min(18rem, 55vw);
        display: inline-block;
        vertical-align: bottom;
    }
    .btn-add-coordinator {
        white-space: nowrap;
    }
    .create-coordinator-dialog {
        max-width: min(1120px, 96vw);
    }
    .create-coordinator-modal {
        border-radius: 16px;
        overflow: hidden;
        background: var(--dtr-card-bg) !important;
        border: 1px solid var(--dtr-border-soft);
        box-shadow: 0 20px 40px -14px rgba(15, 23, 42, 0.12);
    }
    html[data-theme="dark"] .create-coordinator-modal {
        box-shadow: 0 24px 56px -12px rgba(0, 0, 0, 0.5);
        border-color: color-mix(in srgb, var(--dtr-border-soft) 85%, var(--dtr-muted));
    }
    .create-coordinator-modal .modal-body {
        max-height: min(72vh, 680px);
        min-width: 0;
        padding: 1.25rem 1.35rem 1.05rem;
        background: var(--dtr-card-bg);
    }
    .create-coordinator-modal > .modal-header {
        padding-left: 1.35rem;
        padding-right: 1.35rem;
        padding-top: 1.2rem;
        padding-bottom: 0.5rem;
        border-bottom: none;
    }
    .create-coordinator-header--modal {
        padding: 0 0 0.5rem;
        border-bottom: none;
        margin-bottom: 0;
    }
    .create-coordinator-modal-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 2;
    }
    .create-coordinator-modal .modal-footer .btn.btn-primary.coord-modal-submit {
        min-width: 140px;
    }
    .create-coordinator-header {
        display: flex;
        gap: 0.85rem;
        align-items: flex-start;
        padding: 0.1rem 0 1rem;
        margin-bottom: 0;
        border-bottom: 1px solid color-mix(in srgb, var(--dtr-border-soft) 92%, transparent);
    }
    .create-coordinator-header-icon {
        flex: 0 0 auto;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: color-mix(in srgb, var(--dtr-primary) 10%, var(--dtr-card-bg)) !important;
        color: var(--dtr-primary);
        font-size: 1.2rem;
        border: 1px solid color-mix(in srgb, var(--dtr-primary) 22%, var(--dtr-border-soft));
    }
    html[data-theme="dark"] .create-coordinator-header-icon {
        background: color-mix(in srgb, var(--dtr-primary) 14%, var(--dtr-card-bg)) !important;
        border-color: color-mix(in srgb, var(--dtr-primary) 28%, var(--dtr-input-border));
    }
    .create-coordinator-header-copy {
        min-width: 0;
        text-align: left;
        flex: 1 1 auto;
    }
    .create-coordinator-header .panel-kicker {
        margin-bottom: 0.4rem;
        padding: 0.18rem 0.5rem;
        font-size: 0.65rem;
        font-weight: 600;
        letter-spacing: 0.1em;
        background: color-mix(in srgb, var(--dtr-primary) 11%, transparent);
        color: color-mix(in srgb, var(--dtr-primary) 88%, var(--dtr-heading));
    }
    .create-coordinator-title {
        margin-top: 0 !important;
        margin-bottom: 0.4rem !important;
        text-align: left;
        font-size: 1.15rem;
        font-weight: 650;
        letter-spacing: -0.02em;
        color: var(--dtr-heading);
    }
    .create-coordinator-lead {
        margin: 0;
        color: var(--dtr-muted);
        font-size: 0.875rem;
        line-height: 1.55;
        overflow-wrap: break-word;
        word-break: normal;
        max-width: 52rem;
    }
    .coordinator-create-form {
        margin-top: 0.65rem;
        display: grid;
        gap: 1.1rem;
        grid-template-columns: 1fr;
    }
    .create-coordinator-modal fieldset.coordinator-form-section {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.85rem;
        margin: 0;
        padding: 1.15rem 1.2rem 1.2rem;
        border-radius: 14px;
        border: 1px solid color-mix(in srgb, var(--dtr-input-border) 52%, var(--dtr-border-soft)) !important;
        background: color-mix(in srgb, var(--dtr-surface-soft) 75%, var(--dtr-card-bg) 25%) !important;
        box-shadow: none !important;
        min-width: 0;
        max-width: 100%;
        transition: border-color 0.2s ease;
    }
    html[data-theme="dark"] .create-coordinator-modal .coordinator-form-section {
        background: color-mix(in srgb, var(--dtr-card-bg) 92%, var(--dtr-surface-soft) 8%) !important;
        border-color: color-mix(in srgb, var(--dtr-input-border) 55%, #64748b 45%) !important;
        box-shadow: inset 0 1px 0 color-mix(in srgb, #fff 4%, transparent) !important;
    }
    .create-coordinator-modal .coordinator-form-section:hover {
        border-color: color-mix(in srgb, var(--dtr-primary) 26%, var(--dtr-input-border)) !important;
    }
    .create-coordinator-modal .form-section-legend {
        float: none;
        width: 100%;
        max-width: 100%;
        margin: 0 0 0.15rem;
        padding: 0;
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.11em;
        text-transform: uppercase;
        color: color-mix(in srgb, var(--dtr-muted) 72%, var(--dtr-primary) 28%);
        gap: 0.42rem;
    }
    .create-coordinator-modal .form-section-legend i {
        font-size: 0.95rem;
        color: var(--dtr-primary);
        opacity: 0.92;
    }
    html[data-theme="dark"] .create-coordinator-modal .form-section-legend {
        color: color-mix(in srgb, var(--dtr-muted) 82%, var(--dtr-primary) 18%);
        letter-spacing: 0.09em;
    }
    html[data-theme="dark"] .create-coordinator-modal .form-section-legend i {
        color: color-mix(in srgb, var(--dtr-primary) 62%, var(--dtr-muted) 38%);
        opacity: 1;
    }
    .create-coordinator-modal .name-fields-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem 0.85rem;
        align-items: start;
        width: 100%;
        min-width: 0;
    }
    .create-coordinator-modal .name-preview-pill {
        border: 1px solid color-mix(in srgb, var(--dtr-input-border) 65%, transparent);
        border-radius: 10px;
        padding: 0.55rem 0.78rem;
        font-size: 0.8125rem;
        color: var(--dtr-text);
        background: var(--dtr-input-bg);
        min-height: 2.35rem;
        display: flex;
        align-items: center;
        margin-top: 0.35rem;
        margin-bottom: 0.95rem;
    }
    html[data-theme="dark"] .create-coordinator-modal .name-preview-pill {
        border-color: color-mix(in srgb, var(--dtr-input-border) 62%, #94a3b8 38%);
        background: color-mix(in srgb, var(--dtr-input-bg) 92%, var(--dtr-card-bg) 8%);
    }
    .create-coordinator-modal .coordinator-form-section--account .name-fields-grid ~ .form-field-hint {
        margin-top: 0.25rem;
        margin-bottom: 0.45rem;
    }
    .form-section-intro {
        margin: -0.35rem 0 0.85rem;
        font-size: 0.84rem;
        color: var(--dtr-muted);
        line-height: 1.4;
        overflow-wrap: break-word;
        word-break: normal;
    }
    .form-field-hint {
        margin: 0.35rem 0 0;
        font-size: 0.78rem;
        color: var(--dtr-muted);
        line-height: 1.35;
        overflow-wrap: break-word;
        word-break: normal;
    }
    .create-coordinator-modal fieldset.coordinator-form-section .form-block {
        margin-bottom: 0;
    }
    .create-coordinator-modal fieldset.coordinator-form-section .form-field-hint {
        margin-top: 0.38rem;
        margin-bottom: 0;
    }
    .password-field-wrap {
        position: relative;
    }
    .password-field-wrap .form-control {
        padding-right: 2.75rem;
    }
    .password-reveal-btn {
        position: absolute;
        right: 0.35rem;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        color: var(--dtr-muted);
        padding: 0.35rem;
        border-radius: 8px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .password-reveal-btn:hover {
        color: var(--dtr-primary);
        background: var(--dtr-hover-bg);
    }
    .password-match-indicator {
        margin-top: 0.42rem;
        min-height: 1rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        color: var(--dtr-muted);
    }
    .password-match-indicator.is-match {
        color: #16a34a;
    }
    .password-match-indicator.is-mismatch {
        color: #ef4444;
    }
    .create-form-footnote {
        margin: 0;
        text-align: center;
        font-size: 0.8125rem;
        line-height: 1.4;
        color: var(--dtr-muted);
        overflow-wrap: break-word;
        word-break: normal;
    }
    .create-coordinator-modal .form-section-intro {
        margin: 0 0 0.75rem;
        font-size: 0.8125rem;
        line-height: 1.5;
        color: var(--dtr-muted);
    }
    .school-year-year-picker {
        font-variant-numeric: tabular-nums;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .school-year-year-picker.year-picker-native {
        display: none !important;
    }
    .year-picker-shell {
        position: relative;
    }
    .year-picker-display {
        width: 100%;
        min-height: 50px;
        border-radius: 14px;
        border: 1px solid var(--dtr-input-border);
        background: var(--dtr-input-bg);
        color: var(--dtr-text);
        padding: 0.62rem 0.85rem;
        text-align: left;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }
    .year-picker-popover {
        position: absolute;
        top: calc(100% + 0.4rem);
        left: 0;
        z-index: 30;
        width: min(100%, 360px);
        border: 1px solid var(--dtr-border-soft);
        border-radius: 12px;
        background: var(--dtr-card-solid);
        box-shadow: var(--dtr-shadow-strong);
        padding: 0.55rem;
    }
    .year-picker-popover[hidden] { display: none !important; }
    .year-picker-head {
        display: grid;
        grid-template-columns: 2rem 1fr 2rem;
        align-items: center;
        gap: 0.3rem;
        margin-bottom: 0.45rem;
    }
    .year-picker-nav {
        border: 1px solid var(--dtr-input-border);
        background: var(--dtr-card-solid);
        color: var(--dtr-text);
        border-radius: 8px;
        min-height: 2rem;
        font-size: 1rem;
    }
    .year-picker-title {
        text-align: center;
        font-weight: 700;
        color: var(--dtr-heading);
        font-size: 0.88rem;
    }
    .year-picker-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.35rem;
    }
    .year-picker-cell {
        border: 1px solid transparent;
        background: color-mix(in srgb, var(--dtr-surface-soft) 90%, transparent);
        border-radius: 8px;
        min-height: 2.25rem;
        color: var(--dtr-text);
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }
    .year-picker-cell.is-selected {
        border-color: color-mix(in srgb, var(--dtr-primary) 45%, var(--dtr-input-border));
        background: var(--dtr-primary-soft);
        color: var(--dtr-primary-dark);
    }
    .school-year-range-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.55rem;
        width: 100%;
        align-items: stretch;
    }
    .school-year-range-grid > .form-control,
    .school-year-range-grid > .form-select {
        width: 100%;
        min-width: 12rem;
    }
    .create-coordinator-modal .school-year-range-grid > .form-control,
    .create-coordinator-modal .school-year-range-grid > .form-select {
        min-width: 0;
    }
    .school-year-range-grid input[type="date"] {
        font-size: 0.92rem;
        letter-spacing: 0.01em;
        padding-right: 0.6rem;
        cursor: pointer;
        pointer-events: auto;
    }
    .create-coordinator-modal .modal-footer {
        border-top: 1px solid var(--dtr-border-soft);
        background: color-mix(in srgb, var(--dtr-card-bg) 97%, var(--dtr-surface-soft) 3%);
        padding: 1rem 1.35rem 1.15rem !important;
        margin-top: 0.15rem;
    }
    .create-coordinator-modal .modal-footer .btn {
        min-height: 44px;
        border-radius: 12px;
    }
    @media (max-width: 575.98px) {
        .school-year-range-grid {
            grid-template-columns: 1fr;
        }
        .school-year-range-grid > .form-control,
        .school-year-range-grid > .form-select {
            min-width: 0;
        }
        .create-coordinator-modal .name-fields-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 991px) {
        .create-coordinator-modal .name-fields-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .assignment-row.assignment-row-create {
            grid-template-columns: 1fr;
        }
    }
    @media (min-width: 1200px) {
        .coordinator-create-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.15rem 1.25rem;
            align-items: start;
        }
        .coordinator-create-form .coordinator-form-section--assignment,
        .coordinator-create-form .coordinator-form-section--security {
            grid-column: 1 / -1;
        }
    }
    .panel-head {
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 1rem;
    }
    .panel-kicker {
        display: inline-flex;
        align-items: center;
        padding: 0.28rem 0.62rem;
        border-radius: 999px;
        background: var(--dtr-primary-soft);
        color: var(--dtr-primary);
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }
    .coordinator-form {
        display: grid;
        gap: 0.95rem;
        margin-top: 1.25rem;
    }
    .compact-form {
        margin-top: 0;
    }
    .form-block .form-label {
        margin-bottom: 0.42rem;
        color: var(--dtr-muted);
        font-size: 0.84rem;
        font-weight: 600;
    }
    .coordinator-form .form-control,
    .coordinator-form .form-select {
        min-height: 50px;
        min-width: 0;
        max-width: 100%;
        border-radius: 14px;
        background: var(--dtr-input-bg);
        border: 1px solid var(--dtr-input-border);
        color: var(--dtr-text);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.03);
    }
    .coordinator-form .form-control::placeholder {
        color: var(--dtr-muted);
    }
    .coordinator-form .form-control:focus,
    .coordinator-form .form-select:focus {
        background: color-mix(in srgb, var(--dtr-input-bg) 88%, white 12%);
        border-color: rgba(45,212,191,0.72);
        box-shadow: 0 0 0 4px rgba(45,212,191,0.14);
        color: var(--dtr-text);
    }
    .create-coordinator-modal .coordinator-form.coordinator-create-form {
        margin-top: 0;
        gap: 1.12rem;
    }
    .create-coordinator-modal .coordinator-form .form-control,
    .create-coordinator-modal .coordinator-form .form-select {
        min-height: 46px;
        border-radius: 11px;
        box-shadow: none;
    }
    .create-coordinator-modal .coordinator-form .form-control:focus,
    .create-coordinator-modal .coordinator-form .form-select:focus {
        background: var(--dtr-input-bg);
        border-color: color-mix(in srgb, var(--dtr-primary) 52%, var(--dtr-input-border));
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--dtr-primary) 15%, transparent);
    }
    html[data-theme="dark"] .create-coordinator-modal .coordinator-form .form-control,
    html[data-theme="dark"] .create-coordinator-modal .coordinator-form .form-select {
        border-color: color-mix(in srgb, var(--dtr-input-border) 72%, #94a3b8 28%);
        background: var(--dtr-input-bg);
    }
    html[data-theme="dark"] .create-coordinator-modal .coordinator-form .form-control:focus,
    html[data-theme="dark"] .create-coordinator-modal .coordinator-form .form-select:focus {
        border-color: color-mix(in srgb, var(--dtr-primary) 48%, var(--dtr-input-border));
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--dtr-primary) 22%, transparent);
    }
    .assignment-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }
    .assignment-row-modal {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .assignment-row.assignment-row-create {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
        align-items: start;
        min-width: 0;
    }
    .assignment-row.assignment-row-create > .form-block {
        min-width: 0;
    }
    /*
     * Horizontal scroll only: overflow-y must not be `visible` with overflow-x auto — CSS computes y as `auto`,
     * which adds a vertical scrollbar track (gray strip on the right). `hidden` keeps one axis clean.
     */
    /* Match tbody/card fill so gaps beside Actions aren’t a lighter slate; same for .table-responsive in admin layout */
    .table-responsive.admin-coordinators-roster.coord-roster-scroll-container {
        background-color: var(--dtr-card-solid) !important;
    }
    /*
     * Must be a normal block filling the scrollport (not inline-block shrink-to-fit). Otherwise
     * width:100% on the <table> can resolve against the shrink-wrapped sizer (≈ min table width),
     * leaving empty space to the right — row borders only span the table, so lines “fall short”.
     */
    .admin-coordinators-roster.student-table-wrap .coord-roster-table-sizer {
        display: block;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        background-color: var(--dtr-card-solid);
    }
    /* Same outer rounding + scroll shell as admin students roster */
    .admin-coordinators-roster.student-table-wrap {
        border-radius: 16px;
        overflow-x: hidden !important;
        overflow-y: hidden;
        scrollbar-gutter: auto;
        max-width: 100%;
        -webkit-overflow-scrolling: touch;
        background: var(--dtr-card-solid) !important;
    }
    html[data-theme="dark"] .admin-coordinators-roster.student-table-wrap {
        background: var(--dtr-card-solid) !important;
        /* thumb | track — track matches navy so no wide gray rail; thumb uses theme teal */
        scrollbar-color: rgba(45, 212, 191, 0.42) var(--dtr-card-solid);
    }
    /* Firefox / Win: horizontal bar reads as teal-on-navy, not silver */
    .admin-coordinators-roster.student-table-wrap {
        scrollbar-width: thin;
        scrollbar-color: rgba(20, 184, 166, 0.38) var(--dtr-card-solid);
        -ms-overflow-style: auto;
    }
    .admin-coordinators-roster.student-table-wrap::-webkit-scrollbar {
        height: 10px;
        width: 10px;
    }
    .admin-coordinators-roster.student-table-wrap::-webkit-scrollbar:vertical {
        width: 0;
        display: none;
    }
    .admin-coordinators-roster.student-table-wrap::-webkit-scrollbar:horizontal {
        height: 10px;
    }
    .admin-coordinators-roster.student-table-wrap::-webkit-scrollbar-corner {
        background: transparent;
    }
    .admin-coordinators-roster.student-table-wrap::-webkit-scrollbar-track {
        background: var(--dtr-card-solid);
    }
    .admin-coordinators-roster.student-table-wrap::-webkit-scrollbar-thumb {
        background-color: rgba(20, 184, 166, 0.4);
        border-radius: 9999px;
        border: 3px solid transparent;
        background-clip: content-box;
    }
    .admin-coordinators-roster.student-table-wrap::-webkit-scrollbar-thumb:hover {
        background-color: rgba(20, 184, 166, 0.58);
    }
    html[data-theme="dark"] .admin-coordinators-roster.student-table-wrap::-webkit-scrollbar-thumb {
        background-color: rgba(45, 212, 191, 0.38);
    }
    html[data-theme="dark"] .admin-coordinators-roster.student-table-wrap::-webkit-scrollbar-thumb:hover {
        background-color: rgba(45, 212, 191, 0.55);
    }
    @supports (overflow: clip) {
        .admin-coordinators-roster.student-table-wrap {
            overflow-y: clip;
        }
    }
    /* Roster table — same structure as admin students: fixed grid, row borders, header band */
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table {
        table-layout: fixed;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background-color: var(--dtr-card-solid);
    }
    /* Rebalanced desktop widths: preserve readable assignment text while keeping actions visible */
    .admin-coordinators-roster.student-table-wrap col.col-coord-coordinator { width: 20%; }
    .admin-coordinators-roster.student-table-wrap col.col-coord-college { width: 20%; }
    .admin-coordinators-roster.student-table-wrap col.col-coord-assignments { width: 28%; }
    .admin-coordinators-roster.student-table-wrap col.col-coord-status { width: 10%; }
    .admin-coordinators-roster.student-table-wrap col.col-coord-actions { width: 22%; }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table thead th {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: var(--dtr-muted) !important;
        vertical-align: middle;
        padding-top: 0.95rem;
        padding-bottom: 0.95rem;
        border-bottom: none;
    }
    /* Silver/gray header band above body rows (matches admin roster chrome) */
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table thead,
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table thead th {
        background: var(--dtr-surface-soft) !important;
    }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody td {
        padding-top: 1.05rem;
        padding-bottom: 1.05rem;
        border-bottom: none;
        vertical-align: middle;
    }
    /* No striped overlay: one solid row color so columns (esp. Actions) don’t show alternating gray */
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table {
        --bs-table-accent-bg: transparent;
        --bs-table-striped-bg: transparent;
        --bs-table-bg-state: transparent;
    }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr > td {
        background-color: var(--dtr-card-solid) !important;
    }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr:hover > td {
        background-color: color-mix(in srgb, var(--dtr-card-solid) 93%, var(--dtr-primary-soft) 7%) !important;
    }
    /* Tight gutters: Coordinator | College | Assignments read as one block; extra space before Status */
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table thead th.coord-roster-col-coordinator,
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody td.coord-roster-col-coordinator {
        padding-right: 0.28rem;
    }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table thead th.coord-roster-col-org,
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody td.coord-roster-col-org {
        padding-left: 0.28rem;
        padding-right: 0.28rem;
    }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table thead th.coord-roster-col-assign,
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody td.coord-roster-col-assign {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        text-align: left !important;
        vertical-align: middle;
    }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody td.coord-roster-col-assign .assignment-stack {
        justify-content: flex-start;
    }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table thead th.coord-roster-col-status,
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody td.coord-roster-col-status {
        text-align: center !important;
        padding-left: 0.85rem;
        padding-right: 0.85rem;
        position: relative;
        min-width: 7.5rem;
    }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table thead th.coord-roster-col-actions {
        text-align: center !important;
        vertical-align: middle;
        padding-left: 0.85rem;
        padding-right: 0.65rem;
        min-width: 0;
        box-sizing: border-box;
        background-clip: border-box;
    }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody td.coord-roster-col-actions {
        text-align: center !important;
        vertical-align: middle;
        padding-left: 0.85rem;
        /* Tight right edge: extra padding showed as a gray strip beside the pill row (striped td bg) */
        padding-right: 0.35rem;
        min-width: 0;
        position: relative;
    }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table thead th.coord-roster-col-coordinator,
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody td.coord-roster-col-coordinator {
        min-width: 10.25rem;
    }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table thead th.coord-roster-col-org,
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody td.coord-roster-col-org {
        min-width: 10.5rem;
    }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table thead th.coord-roster-col-assign,
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody td.coord-roster-col-assign {
        min-width: 11rem;
    }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table .coord-org-primary,
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table .coord-org-secondary {
        max-width: 100%;
        overflow-wrap: break-word;
        word-break: normal;
        line-height: 1.35;
    }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table .coord-org-primary {
        color: var(--dtr-heading) !important;
    }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody td:nth-child(1) .identity-name {
        font-size: 1.05rem;
        letter-spacing: -0.02em;
        color: var(--dtr-heading) !important;
    }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table .admin-roster-status-badge {
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
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table .admin-roster-status-badge.bg-success {
        background: rgba(22, 101, 52, 0.92) !important;
        color: #ecfdf5 !important;
        border-color: rgba(52, 211, 153, 0.55);
    }
    html[data-theme="light"] .admin-coordinators-roster.student-table-wrap .admin-coordinators-table .admin-roster-status-badge.bg-success {
        background: rgba(22, 163, 74, 0.16) !important;
        color: #14532d !important;
        border-color: rgba(34, 197, 94, 0.42);
    }
    .admin-coordinators-roster.student-table-wrap .admin-coordinators-table .admin-roster-status-badge.badge-status-inactive {
        /* Contrast handled globally in admin layout (.badge.badge-status-inactive) */
        font-weight: 700;
    }
    /*
     * Actions toolbar: flex-start (not center) so a wide max-content row does not extend
     * left over the Status column (centering caused Assign section to overlap the badge).
     */
    .admin-coordinators-roster .coord-actions-toolbar-wrap {
        width: 100%;
        min-width: 0;
        max-width: 100%;
        display: flex;
        justify-content: flex-start;
        align-items: center;
        overflow-x: visible;
        overflow-y: visible;
        padding: 0.08rem 0;
        /* Same paint as the cell (striped / solid) so empty flex area isn’t a different gray */
        background-color: inherit;
    }
    .admin-coordinators-roster .coord-action-toolbar {
        display: inline-flex;
        flex-wrap: wrap;
        flex-shrink: 0;
        justify-content: flex-start;
        align-items: center;
        gap: 0.22rem;
        width: 100%;
        max-width: 100%;
        background-color: inherit;
    }
    .admin-coordinators-roster .coord-action-toolbar-desktop {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(7.2rem, 1fr));
        gap: 0.35rem;
        width: 100%;
        align-items: stretch;
    }
    .admin-coordinators-roster .coord-action-toolbar-desktop > .coord-action-form,
    .admin-coordinators-roster .coord-action-toolbar-desktop > .coord-roster-action-btn {
        width: 100%;
        min-width: 0;
        flex: 1 1 auto;
    }
    .admin-coordinators-roster .coord-action-toolbar-desktop > .coord-action-form .coord-roster-action-btn,
    .admin-coordinators-roster .coord-action-toolbar-desktop > .coord-roster-action-btn {
        width: 100%;
    }
    .admin-coordinators-roster .coord-action-form {
        display: flex;
        margin: 0;
        flex: 0 0 auto;
        background-color: inherit;
    }
    .admin-coordinators-roster .coord-action-form .coord-roster-action-btn {
        width: auto;
        flex: 0 0 auto;
    }
    .admin-coordinators-roster .coord-roster-action-btn,
    .admin-coordinators-roster .coord-roster-remove-btn {
        box-sizing: border-box;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: auto;
        flex: 0 0 auto;
        min-width: 0;
        min-height: 2.2rem;
        height: auto;
        margin: 0;
        padding: 0.38rem 0.62rem !important;
        font-size: 0.8125rem !important;
        font-weight: 600;
        line-height: 1.25;
        border-radius: 999px;
        white-space: nowrap;
        overflow: visible;
        text-align: center;
    }
    .admin-coordinators-roster .coord-roster-remove-btn .bi {
        flex-shrink: 0;
        margin-right: 0;
        color: inherit;
    }
    /*
     * Remove = pill outline (reference): transparent fill, coral-red border, same hue for icon + label.
     * Overrides global admin .btn / .btn-outline-secondary for this control only.
     */
    .admin-coordinators-roster .coord-roster-remove-btn.btn-outline-secondary,
    .admin-coordinators-roster .coord-roster-remove-btn.btn {
        background: transparent !important;
        background-image: none !important;
        border: 1px solid #f85149 !important;
        color: #f85149 !important;
        box-shadow: none !important;
        text-shadow: none;
        filter: none;
        transform: none;
    }
    .admin-coordinators-roster .coord-roster-remove-btn.btn-outline-secondary:hover,
    .admin-coordinators-roster .coord-roster-remove-btn.btn:hover {
        background: rgba(248, 81, 73, 0.1) !important;
        border-color: #ff6b5c !important;
        color: #ff4d4d !important;
        filter: none;
        transform: translateY(-1px);
    }
    .admin-coordinators-roster .coord-roster-remove-btn.btn-outline-secondary:focus-visible,
    .admin-coordinators-roster .coord-roster-remove-btn.btn:focus-visible {
        outline: 2px solid #f85149;
        outline-offset: 2px;
        box-shadow: 0 0 0 3px rgba(248, 81, 73, 0.28) !important;
    }
    html[data-theme="dark"] .admin-coordinators-roster .coord-roster-remove-btn.btn-outline-secondary,
    html[data-theme="dark"] .admin-coordinators-roster .coord-roster-remove-btn.btn {
        background: transparent !important;
        border: 1px solid #ff6568 !important;
        color: #ff6568 !important;
        box-shadow: none !important;
    }
    html[data-theme="dark"] .admin-coordinators-roster .coord-roster-remove-btn.btn-outline-secondary:hover,
    html[data-theme="dark"] .admin-coordinators-roster .coord-roster-remove-btn.btn:hover {
        background: rgba(255, 101, 104, 0.12) !important;
        border-color: #ff7b7e !important;
        color: #ff7b7e !important;
    }
    /*
     * Assign / Reset: neutral outline using theme tokens (table cells force --dtr-text otherwise).
     * Disable / Enable: danger/success tints that read in both themes.
     */
    .admin-coordinators-roster .coord-action-toolbar-desktop .coord-roster-action-btn.btn-outline-secondary:not(.coord-roster-remove-btn) {
        background: transparent !important;
        border: 1px solid var(--dtr-input-border) !important;
        color: var(--dtr-heading) !important;
        box-shadow: none !important;
    }
    .admin-coordinators-roster .coord-action-toolbar-desktop .coord-roster-action-btn.btn-outline-secondary:not(.coord-roster-remove-btn):hover {
        background: var(--dtr-hover-bg) !important;
        border-color: var(--dtr-border-strong) !important;
        color: var(--dtr-heading) !important;
    }
    .admin-coordinators-roster .coord-action-toolbar-desktop .coord-roster-action-btn.btn-outline-secondary:not(.coord-roster-remove-btn):focus-visible {
        outline: 2px solid var(--dtr-primary);
        outline-offset: 2px;
        box-shadow: 0 0 0 3px var(--dtr-primary-soft) !important;
    }
    .admin-coordinators-roster .coord-action-toolbar-desktop .coord-roster-action-btn.btn-outline-danger {
        background: transparent !important;
        border: 1px solid rgba(220, 38, 38, 0.45) !important;
        color: #b91c1c !important;
        box-shadow: none !important;
    }
    .admin-coordinators-roster .coord-action-toolbar-desktop .coord-roster-action-btn.btn-outline-danger:hover {
        background: rgba(220, 38, 38, 0.08) !important;
        border-color: rgba(185, 28, 28, 0.65) !important;
        color: #991b1b !important;
    }
    html[data-theme="dark"] .admin-coordinators-roster .coord-action-toolbar-desktop .coord-roster-action-btn.btn-outline-danger {
        border-color: rgba(248, 113, 113, 0.5) !important;
        color: #fecaca !important;
    }
    html[data-theme="dark"] .admin-coordinators-roster .coord-action-toolbar-desktop .coord-roster-action-btn.btn-outline-danger:hover {
        background: rgba(248, 113, 113, 0.12) !important;
        border-color: rgba(252, 165, 165, 0.65) !important;
        color: #fee2e2 !important;
    }
    .admin-coordinators-roster .coord-action-toolbar-desktop .coord-roster-action-btn.btn-outline-success {
        background: transparent !important;
        border: 1px solid rgba(22, 163, 74, 0.45) !important;
        color: #15803d !important;
        box-shadow: none !important;
    }
    .admin-coordinators-roster .coord-action-toolbar-desktop .coord-roster-action-btn.btn-outline-success:hover {
        background: rgba(22, 163, 74, 0.08) !important;
        border-color: rgba(21, 128, 61, 0.6) !important;
        color: #166534 !important;
    }
    html[data-theme="dark"] .admin-coordinators-roster .coord-action-toolbar-desktop .coord-roster-action-btn.btn-outline-success {
        border-color: rgba(74, 222, 128, 0.45) !important;
        color: #bbf7d0 !important;
    }
    html[data-theme="dark"] .admin-coordinators-roster .coord-action-toolbar-desktop .coord-roster-action-btn.btn-outline-success:hover {
        background: rgba(74, 222, 128, 0.1) !important;
        border-color: rgba(134, 239, 172, 0.55) !important;
        color: #dcfce7 !important;
    }
    .main-content .coord-mobile-more-menu .dropdown-item {
        color: var(--dtr-heading) !important;
    }
    .main-content .coord-mobile-more-menu .dropdown-item:hover,
    .main-content .coord-mobile-more-menu .dropdown-item:focus {
        background: var(--dtr-hover-bg);
        color: var(--dtr-heading) !important;
    }
    .main-content .coord-mobile-more-menu .dropdown-item.text-danger {
        color: #b91c1c !important;
    }
    html[data-theme="dark"] .main-content .coord-mobile-more-menu .dropdown-item.text-danger {
        color: #fecaca !important;
    }
    .admin-remove-coordinator-form {
        margin: 0;
    }
    .coordinator-identity {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        min-width: 0;
        max-width: 100%;
    }
    .admin-coordinators-roster .coordinator-identity {
        gap: 0.5rem;
    }
    .admin-coordinators-roster .identity-copy {
        min-width: 0;
        flex: 1 1 auto;
        overflow: hidden;
        max-width: 100%;
    }
    .admin-coordinators-roster .identity-name,
    .admin-coordinators-roster .identity-meta {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 100%;
    }
    .identity-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--dtr-primary-soft), rgba(103,232,249,0.12));
        color: var(--dtr-primary);
        font-weight: 700;
        border: 1px solid var(--dtr-border-soft);
        flex: 0 0 auto;
    }
    .identity-name {
        color: var(--dtr-heading);
        font-weight: 700;
        margin-bottom: 0.15rem;
    }
    .identity-meta,
    .cell-value {
        color: var(--dtr-muted);
        font-size: 0.92rem;
    }
    .assignment-stack {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        min-width: 0;
        max-width: 100%;
    }
    .assignment-chip-form {
        margin: 0;
    }
    .assignment-chip-wrap {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem;
        max-width: 100%;
    }
    .assignment-chip-remove {
        border-color: rgba(248, 113, 113, 0.45);
    }
    .assignment-chip-remove:hover {
        border-color: rgba(248, 113, 113, 0.7);
        background: rgba(248, 113, 113, 0.08);
    }
    .assignment-chip {
        display: inline-flex;
        align-items: flex-start;
        gap: 0.45rem;
        max-width: 100%;
        min-width: 0;
        padding: 0.38rem 0.65rem;
        border-radius: 999px;
        border: 1px solid var(--dtr-border-soft);
        background: var(--dtr-card-solid);
        color: var(--dtr-text);
        font-size: 0.78rem;
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
    }
    .assignment-chip-text {
        display: inline-block;
        min-width: 0;
        max-width: min(100%, 18.5rem);
        overflow-wrap: break-word;
        word-break: normal;
        white-space: normal;
        text-align: left;
        line-height: 1.35;
    }
    .assignment-chip:hover {
        border-color: rgba(45, 212, 191, 0.45);
        background: color-mix(in srgb, var(--dtr-card-solid) 90%, var(--dtr-primary-soft));
        box-shadow: 0 0 0 1px rgba(45, 212, 191, 0.12);
    }
    .assignment-chip:focus-visible {
        outline: 2px solid var(--dtr-primary);
        outline-offset: 2px;
    }
    .assignment-chip i {
        font-size: 0.7rem;
        color: var(--dtr-muted);
        flex-shrink: 0;
        margin-top: 0.1rem;
    }
    .admin-coordinators-roster .assignment-stack--bounded {
        max-height: 6.25rem;
        overflow-y: auto;
        overflow-x: hidden;
        align-content: flex-start;
        scrollbar-gutter: auto;
        padding-right: 0.15rem;
        scrollbar-width: thin;
        scrollbar-color: rgba(100, 116, 139, 0.4) transparent;
    }
    html[data-theme="dark"] .admin-coordinators-roster .assignment-stack--bounded {
        scrollbar-color: rgba(148, 163, 184, 0.45) transparent;
    }
    .admin-coordinators-roster .assignment-stack--bounded::-webkit-scrollbar {
        width: 6px;
    }
    .admin-coordinators-roster .assignment-stack--bounded::-webkit-scrollbar-track {
        background: transparent;
    }
    .admin-coordinators-roster .assignment-stack--bounded::-webkit-scrollbar-thumb {
        background-color: rgba(100, 116, 139, 0.38);
        border-radius: 9999px;
        border: 2px solid transparent;
        background-clip: content-box;
    }
    html[data-theme="dark"] .admin-coordinators-roster .assignment-stack--bounded::-webkit-scrollbar-thumb {
        background-color: rgba(148, 163, 184, 0.42);
    }
    .coord-roster-scroll-hint {
        line-height: 1.45;
    }
    .coord-roster-scroll-hint i {
        opacity: 0.85;
        margin-right: 0.15rem;
    }
    @media (min-width: 768px) {
        /* Match resources/views/admin/students.blade.php roster chrome */
        .coord-roster-scroll-container.admin-coordinators-roster.student-table-wrap {
            overflow-x: hidden !important;
            border-radius: 14px;
            border: 1px solid var(--dtr-border-soft);
        }
        /* Table always at least as wide as the scrollport so header/row rules span the inner box */
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table {
            min-width: 100%;
            width: 100%;
            /* Row borders span the full table width (per-cell borders/shadows do not) */
            border-collapse: collapse;
            border-spacing: 0;
        }
        /* One rule under the whole header row */
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table thead {
            border-bottom: 1px solid var(--dtr-border-soft);
        }
        /* Full-width row separators */
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr {
            border-bottom: 1px solid var(--dtr-border-soft);
        }
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr:last-child {
            border-bottom: none;
        }
        /*
         * Sticky on each <th> (same as admin students roster). Chromium-based Edge is reliable here;
         * sticky on <thead> is still uneven in some table + overflow combinations.
         */
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table thead th {
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            z-index: 4;
            background: var(--dtr-surface-soft) !important;
        }
    }
    .action-cluster {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        max-width: 100%;
    }
    .action-cluster .btn {
        white-space: nowrap;
    }
    .coord-action-toolbar-mobile {
        display: none;
    }
    .coord-mobile-details {
        width: 100%;
        margin-top: 0.45rem;
        padding: 0.7rem 0.8rem;
        border: 1px solid var(--dtr-border-soft);
        border-radius: 10px;
        background: var(--dtr-surface-soft);
    }
    .coord-mobile-details-block + .coord-mobile-details-block {
        margin-top: 0.6rem;
    }
    .coord-mobile-details-label {
        font-size: 0.68rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--dtr-muted);
        font-weight: 700;
        margin-bottom: 0.15rem;
    }
    .coord-mobile-details-value {
        font-size: 0.9rem;
        color: var(--dtr-text);
        font-weight: 600;
    }
    .coord-mobile-details-sub {
        font-size: 0.84rem;
        color: var(--dtr-muted);
    }
    .coord-mobile-details-list {
        margin: 0;
        padding-left: 1rem;
        color: var(--dtr-text);
        font-size: 0.84rem;
    }
    .empty-state-cell {
        padding: 2.5rem 1.25rem !important;
        text-align: center;
        vertical-align: middle !important;
    }
    .coord-empty-state {
        max-width: 22rem;
        margin: 0 auto;
    }
    .coord-empty-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 1rem;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: var(--dtr-primary);
        background: linear-gradient(145deg, var(--dtr-primary-soft), rgba(103,232,249,0.08));
        border: 1px solid var(--dtr-border-soft);
    }
    .coord-empty-title {
        font-weight: 700;
        color: var(--dtr-heading);
        font-size: 1rem;
        overflow-wrap: break-word;
        word-break: normal;
    }
    .coord-empty-copy {
        color: var(--dtr-muted);
        font-size: 0.9rem;
        line-height: 1.5;
        overflow-wrap: break-word;
        word-break: normal;
    }
    .coord-empty-copy a {
        font-weight: 600;
    }
    .coordinator-modal {
        background: var(--dtr-card-bg);
        border: 1px solid var(--dtr-border-soft);
        border-radius: 18px;
        box-shadow: var(--dtr-shadow-strong);
    }
    .coordinator-modal .modal-header,
    .coordinator-modal .modal-body {
        color: var(--dtr-text);
    }
    .coordinator-modal:not(.coordinator-modal--side) .modal-header {
        justify-content: center;
        text-align: center;
    }
    .coordinator-modal:not(.coordinator-modal--side) .modal-header > div {
        width: 100%;
    }
    .coordinator-modal--side .modal-header {
        justify-content: flex-start;
        text-align: left;
        align-items: flex-start;
        padding-right: 2.75rem;
    }
    .coord-modal-head {
        display: flex;
        gap: 0.85rem;
        align-items: flex-start;
        min-width: 0;
        text-align: left;
    }
    .coord-modal-head-icon {
        flex: 0 0 auto;
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: var(--dtr-primary);
        background: linear-gradient(145deg, var(--dtr-primary-soft), rgba(103,232,249,0.08));
        border: 1px solid rgba(45,212,191,0.2);
    }
    .coord-modal-head > div:last-child {
        min-width: 0;
        flex: 1 1 auto;
        overflow: hidden;
    }
    .coordinator-modal--side .coord-modal-head .modal-title,
    .coordinator-modal--side #assignmentModalHelp,
    .coordinator-modal--side #coordinatorPasswordHelp {
        overflow-wrap: break-word;
        word-break: normal;
    }
    .coordinator-modal .btn-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
    }
    .coordinator-modal--side .modal-body .coordinator-form .form-control,
    .coordinator-modal--side .modal-body .coordinator-form .form-select {
        min-height: 46px;
        border-radius: 12px;
    }
    .coord-modal-footer {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: flex-end;
        align-items: center;
        padding-bottom: 1rem !important;
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    .coord-modal-footer .btn.dtr-mbtn {
        min-height: 44px;
        border-radius: 12px;
    }
    .coord-modal-submit {
        min-width: 9.5rem;
        font-weight: 600;
        border-radius: 12px;
    }
    html[data-theme="dark"] #coordinatorPasswordModal .btn-close,
    html[data-theme="dark"] #assignmentModal .btn-close,
    html[data-theme="dark"] #createCoordinatorModal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    @media (max-width: 991px) {
        .coord-admin-toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        .coord-admin-toolbar-actions {
            justify-content: stretch;
            flex-direction: column;
            align-items: stretch;
        }
        .coord-admin-toolbar-actions .btn-add-coordinator {
            width: 100%;
            justify-content: center;
        }
        .coordinator-admin-card form.search-row.coordinator-search {
            flex-wrap: nowrap;
            max-width: none;
            width: 100%;
        }
        .coordinator-search .search-inner {
            flex: 1 1 0%;
            width: auto;
            min-width: 0;
        }
        .coordinator-search .btn-search,
        .coordinator-search .btn-outline-secondary {
            flex: 0 0 auto;
            justify-content: center;
        }
        .coordinator-search {
            max-width: none;
            width: 100%;
        }
        .create-coordinator-dialog {
            max-width: 98vw;
        }
        .create-coordinator-modal .modal-body {
            padding-left: 0.95rem;
            padding-right: 0.95rem;
        }
    }
    @media (min-width: 992px) and (max-width: 1199px) {
        .assignment-row.assignment-row-create {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 639px) {
        .assignment-row-modal {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 767.98px) {
        .assignment-row {
            grid-template-columns: minmax(0, 1fr);
        }
        .admin-coordinators-roster .assignment-stack--bounded {
            max-height: 9rem;
        }
        .coord-roster-scroll-container.admin-coordinators-roster.student-table-wrap {
            overflow-x: visible !important;
            overflow-y: visible !important;
            border: none;
        }
        .admin-coordinators-roster.student-table-wrap .coord-roster-table-sizer {
            display: block;
            width: 100%;
            min-width: 0;
            background: var(--dtr-card-solid) !important;
        }
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table {
            min-width: 0 !important;
            width: 100%;
            table-layout: auto;
            border-collapse: separate !important;
        }
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr {
            border-bottom: none !important;
        }
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table thead {
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
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr.coord-roster-data-row {
            display: block;
            margin-bottom: 1rem;
            padding: 1rem 1.1rem;
            border: 1px solid var(--dtr-border-soft);
            border-radius: 16px;
            background: var(--dtr-card-solid);
            box-shadow: var(--dtr-shadow-soft);
            overflow: hidden;
        }
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr > td {
            box-shadow: none !important;
        }
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr.coord-roster-data-row > td {
            background-color: transparent !important;
        }
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr.coord-roster-data-row td {
            display: grid;
            grid-template-columns: 7.2rem minmax(0, 1fr);
            gap: 0.35rem 0.85rem;
            align-items: start;
            text-align: left !important;
            border-bottom: 1px solid var(--dtr-border-soft);
            padding: 0.55rem 0 !important;
            vertical-align: top;
            min-width: 0;
        }
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr.coord-roster-data-row td[data-label="Coordinator"] {
            grid-template-columns: 1fr;
            gap: 0.4rem;
        }
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr.coord-roster-data-row td[data-label="Coordinator"]::before {
            margin-bottom: 0.05rem;
        }
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr.coord-roster-data-row td:last-child {
            border-bottom: none;
            grid-column: 1 / -1;
            padding-top: 0.85rem !important;
            margin-top: 0.15rem;
            border-top: 1px dashed var(--dtr-border-soft);
            min-width: 0;
        }
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr.coord-roster-data-row td.coord-roster-col-actions .coord-actions-toolbar-wrap {
            justify-content: flex-start;
            width: 100%;
        }
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr.coord-roster-data-row td::before {
            content: attr(data-label);
            font-weight: 700;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--dtr-muted);
            padding-top: 0.2rem;
            min-width: 0;
        }
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr.coord-roster-data-row td.coord-roster-col-status {
            align-items: center;
        }
        /* Priority columns only on mobile: Coordinator + Status + Actions */
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr.coord-roster-data-row td.coord-roster-col-org,
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr.coord-roster-data-row td.coord-roster-col-assign {
            display: none !important;
        }
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr.coord-roster-empty-row {
            display: table-row;
        }
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr.coord-roster-empty-row td {
            display: table-cell;
            padding: 2rem 1rem !important;
        }
        .admin-coordinators-roster .identity-name,
        .admin-coordinators-roster .identity-meta {
            white-space: normal;
            overflow: visible;
            text-overflow: unset;
        }
        .coordinator-identity,
        .assignment-stack {
            min-width: 0;
        }
        .coordinator-identity {
            display: grid;
            grid-template-columns: 2rem minmax(0, 1fr);
            align-items: start;
            column-gap: 0.55rem;
        }
        .coordinator-identity .identity-avatar {
            width: 2rem;
            height: 2rem;
            font-size: 0.82rem;
        }
        .assignment-chip-text {
            max-width: 100%;
        }
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr.coord-roster-data-row td > * {
            min-width: 0;
            max-width: 100%;
        }
        .coord-action-toolbar-desktop {
            display: none !important;
        }
        .coord-action-toolbar-mobile {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            width: 100%;
            justify-content: flex-start;
        }
        .coord-action-toolbar-mobile .btn {
            min-height: 34px;
        }
        .coord-mobile-more-menu {
            min-width: 9.5rem;
            font-size: 0.86rem;
        }
        .coord-mobile-details-toggle[aria-expanded="true"] {
            background: var(--dtr-hover-bg);
            border-color: var(--dtr-input-border);
        }
    }
    @media (max-width: 460px) {
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr.coord-roster-data-row td {
            grid-template-columns: 1fr;
            gap: 0.2rem;
        }
        .admin-coordinators-roster.student-table-wrap .admin-coordinators-table tbody tr.coord-roster-data-row td::before {
            margin-bottom: 0.05rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalFlagEl = document.getElementById('createCoordinatorOpenFlag');
    var shouldOpenCreateModal = modalFlagEl && modalFlagEl.getAttribute('data-open') === '1';
    if (!shouldOpenCreateModal) return;
    var el = document.getElementById('createCoordinatorModal');
    if (el && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(el).show();
    }
});
document.querySelectorAll('.password-reveal-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-password-target');
        var input = id ? document.getElementById(id) : null;
        var icon = btn.querySelector('i');
        if (!input || !icon) return;
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
            btn.setAttribute('aria-label', 'Hide password');
            btn.setAttribute('title', 'Hide password');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
            btn.setAttribute('aria-label', 'Show password');
            btn.setAttribute('title', 'Show password');
        }
    });
});

(function () {
    function attachPasswordMatchIndicator(passwordId, confirmId, indicatorId, opts) {
        var passwordInput = document.getElementById(passwordId);
        var confirmInput = document.getElementById(confirmId);
        var indicator = document.getElementById(indicatorId);
        if (!passwordInput || !confirmInput || !indicator) return;

        var labels = Object.assign({
            empty: '',
            match: 'Passwords matched',
            mismatch: 'Passwords do not match',
        }, opts || {});

        function updateIndicator() {
            var pass = passwordInput.value || '';
            var confirm = confirmInput.value || '';
            indicator.classList.remove('is-match', 'is-mismatch');

            if (!pass && !confirm) {
                indicator.textContent = labels.empty;
                return;
            }

            if (pass !== '' && confirm !== '' && pass === confirm) {
                indicator.textContent = labels.match;
                indicator.classList.add('is-match');
                return;
            }

            if (confirm !== '') {
                indicator.textContent = labels.mismatch;
                indicator.classList.add('is-mismatch');
                return;
            }

            indicator.textContent = labels.empty;
        }

        ['input', 'change', 'keyup'].forEach(function (evt) {
            passwordInput.addEventListener(evt, updateIndicator);
            confirmInput.addEventListener(evt, updateIndicator);
        });

        updateIndicator();
    }

    attachPasswordMatchIndicator('coord_password', 'coord_password_confirmation', 'coord_password_match_indicator', {
        empty: 'Waiting for password confirmation',
        match: 'Passwords matched',
        mismatch: 'Passwords do not match',
    });

    attachPasswordMatchIndicator('coord_pwd_new', 'coord_pwd_confirm', 'coord_reset_password_match_indicator', {
        empty: 'Waiting for password confirmation',
        match: 'Passwords matched',
        mismatch: 'Passwords do not match',
    });
})();

(function () {
    var programSectionMapEl = document.getElementById('programSectionMapData');
    var programSectionMap = {};
    if (programSectionMapEl) {
        try {
            programSectionMap = JSON.parse(programSectionMapEl.getAttribute('data-map') || '{}') || {};
        } catch (e) {
            programSectionMap = {};
        }
    }

    function getSectionsForPrograms(programs) {
        var defaultSections = Array.isArray(programSectionMap.__default__) ? programSectionMap.__default__ : [];
        var list = Array.isArray(programs) ? programs : [programs];
        var unique = [];
        var seen = {};

        list.forEach(function (program) {
            var key = (program || '').trim();
            if (!key || !Array.isArray(programSectionMap[key])) return;
            programSectionMap[key].forEach(function (section) {
                if (!section || seen[section]) return;
                seen[section] = true;
                unique.push(section);
            });
        });

        if (!unique.length) {
            if (defaultSections.indexOf('All') === -1) {
                return ['All'].concat(defaultSections);
            }
            return defaultSections;
        }

        defaultSections.forEach(function (section) {
            if (!section || seen[section]) return;
            seen[section] = true;
            unique.push(section);
        });

        if (unique.indexOf('All') === -1) {
            unique.unshift('All');
        }

        return unique;
    }

    function repopulateSectionSelect(sectionSelectEl, sectionInputEl, programValue, preferredValue) {
        if (!sectionSelectEl) return;
        function normalizeSectionValue(raw) {
            var value = (raw || '').trim();
            return value.replace(/^section\s+/i, '').trim();
        }

        function canonicalSectionValue(raw) {
            return normalizeSectionValue(raw).toUpperCase();
        }

        var selectedValue = normalizeSectionValue(preferredValue || sectionSelectEl.value || '');
        var options = getSectionsForPrograms(programValue);
        var hasCustomOption = Array.prototype.some.call(sectionSelectEl.options, function (opt) {
            return opt.value === '__custom__';
        });

        while (sectionSelectEl.options.length) {
            sectionSelectEl.remove(0);
        }

        var baseOption = document.createElement('option');
        baseOption.value = '';
        baseOption.textContent = sectionSelectEl.id === 'coord_section' ? '— Later —' : 'Select';
        sectionSelectEl.appendChild(baseOption);

        var seen = {};
        var normalizedOptions = [];
        options.forEach(function (value) {
            if (!value || value === '__custom__') return;
            var normalized = normalizeSectionValue(value);
            if (!normalized) return;
            var canonical = canonicalSectionValue(normalized);
            if (seen[canonical]) return;
            seen[canonical] = true;
            normalizedOptions.push({
                value: normalized,
                canonical: canonical
            });
        });

        normalizedOptions.sort(function (a, b) {
            if (a.canonical === 'ALL' && b.canonical !== 'ALL') return -1;
            if (b.canonical === 'ALL' && a.canonical !== 'ALL') return 1;
            return a.canonical.localeCompare(b.canonical);
        });

        normalizedOptions.forEach(function (item) {
            var option = document.createElement('option');
            option.value = item.value;
            option.textContent = item.value;
            sectionSelectEl.appendChild(option);
        });

        if (!normalizedOptions.some(function (item) { return item.canonical === 'ALL'; })) {
            var allOption = document.createElement('option');
            allOption.value = 'All';
            allOption.textContent = 'All';
            sectionSelectEl.insertBefore(allOption, sectionSelectEl.options[1] || null);
        }

        if (hasCustomOption) {
            var custom = document.createElement('option');
            custom.value = '__custom__';
            custom.textContent = 'Other (type section)';
            sectionSelectEl.appendChild(custom);
        }

        var exists = Array.prototype.some.call(sectionSelectEl.options, function (opt) {
            return canonicalSectionValue(opt.value) === canonicalSectionValue(selectedValue);
        });

        if (exists) {
            sectionSelectEl.value = selectedValue;
        } else if (selectedValue !== '' && hasCustomOption) {
            sectionSelectEl.value = '__custom__';
            if (sectionInputEl) {
                sectionInputEl.classList.remove('d-none');
                sectionInputEl.required = true;
                sectionInputEl.value = selectedValue;
            }
        } else {
            sectionSelectEl.value = '';
        }
    }

    function toggleCustomInput(selectEl, inputEl) {
        if (!selectEl || !inputEl) return;
        var show = selectEl.value === '__custom__';
        inputEl.classList.toggle('d-none', !show);
        inputEl.required = show;
        if (!show) {
            inputEl.value = '';
        }
    }

    function getSelectedValues(selectEl) {
        if (!selectEl) return [];
        return Array.prototype.map.call(selectEl.selectedOptions || [], function (opt) {
            return (opt.value || '').trim();
        }).filter(function (value) {
            return value !== '' && value !== '__custom__';
        });
    }

    var coordMajor = document.getElementById('coord_major');
    var coordCustomMajor = document.getElementById('coord_custom_major');
    var coordSection = document.getElementById('coord_section');
    var coordCustomSection = document.getElementById('coord_custom_section');
    var coordNameField = document.getElementById('coord_name');
    var coordFirstName = document.getElementById('coord_first_name');
    var coordMiddleName = document.getElementById('coord_middle_name');
    var coordLastName = document.getElementById('coord_last_name');
    var coordSuffix = document.getElementById('coord_suffix');
    var coordDegree = document.getElementById('coord_degree');
    var coordNamePreview = document.getElementById('coord_name_preview');

    if (coordMajor && coordCustomMajor) {
        coordMajor.addEventListener('change', function () {
            toggleCustomInput(coordMajor, coordCustomMajor);
            repopulateSectionSelect(coordSection, coordCustomSection, coordMajor.value, '');
        });
        toggleCustomInput(coordMajor, coordCustomMajor);
        repopulateSectionSelect(coordSection, coordCustomSection, coordMajor.value, coordSection ? coordSection.value : '');
    }

    if (coordSection && coordCustomSection) {
        coordSection.addEventListener('change', function () {
            toggleCustomInput(coordSection, coordCustomSection);
        });
        toggleCustomInput(coordSection, coordCustomSection);
    }

    function composeCoordinatorName() {
        var firstName = (coordFirstName && coordFirstName.value || '').trim();
        var middleName = (coordMiddleName && coordMiddleName.value || '').trim();
        var lastName = (coordLastName && coordLastName.value || '').trim();
        var suffix = (coordSuffix && coordSuffix.value || '').trim();
        var degree = (coordDegree && coordDegree.value || '').trim();

        var chunks = [firstName, middleName, lastName, suffix].filter(function (part) {
            return part !== '';
        });
        var built = chunks.join(' ');
        if (built && degree) {
            built += ', ' + degree;
        }

        if (coordNameField) {
            coordNameField.value = built;
        }
        if (coordNamePreview) {
            coordNamePreview.textContent = built || '—';
        }
    }

    [coordFirstName, coordMiddleName, coordLastName, coordSuffix, coordDegree].forEach(function (el) {
        if (!el) return;
        el.addEventListener('input', composeCoordinatorName);
        el.addEventListener('change', composeCoordinatorName);
    });
    composeCoordinatorName();
})();

(function () {
    var currentYear = new Date().getFullYear();

    function parseSchoolYear(rawSchoolYear) {
        var matches = String(rawSchoolYear || '').match(/(\d{4})/g) || [];
        var startYear = matches[0] ? parseInt(matches[0], 10) : NaN;
        var endYear = matches[1] ? parseInt(matches[1], 10) : NaN;
        if (Number.isFinite(startYear) && !Number.isFinite(endYear)) {
            endYear = startYear + 1;
        }
        if (Number.isFinite(startYear) && startYear < currentYear) {
            startYear = currentYear;
        }
        if (Number.isFinite(endYear) && endYear < currentYear) {
            endYear = currentYear;
        }
        return {
            start: Number.isFinite(startYear) ? startYear : null,
            end: Number.isFinite(endYear) ? endYear : null,
        };
    }

    function dateFromYear(year) {
        return Number.isFinite(year) ? (year + '-01-01') : '';
    }

    function yearFromDate(rawDate) {
        return rawDate ? parseInt(String(rawDate).slice(0, 4), 10) : NaN;
    }

    function syncSchoolYearValue(startInput) {
        if (!startInput) return;
        var targetId = startInput.getAttribute('data-school-year-target') || '';
        var hiddenInput = targetId ? document.getElementById(targetId) : null;
        if (!hiddenInput) return;
        var endInputId = startInput.getAttribute('data-school-year-end') || '';
        var endInput = endInputId ? document.getElementById(endInputId) : null;
        var startYear = yearFromDate(startInput.value);
        var endYear = endInput ? yearFromDate(endInput.value) : NaN;

        if (Number.isFinite(startYear) && startYear < currentYear) {
            startYear = currentYear;
            startInput.value = dateFromYear(startYear);
        }
        if (endInput && Number.isFinite(endYear) && endYear < currentYear) {
            endYear = currentYear;
            endInput.value = dateFromYear(endYear);
        }

        if (endInput && Number.isFinite(endYear) && endYear < startYear) {
            endYear = startYear;
            endInput.value = dateFromYear(endYear);
        }

        if (endInput && Number.isFinite(startYear)) {
            endInput.min = dateFromYear(Math.max(startYear, currentYear));
        }

        endYear = endInput ? yearFromDate(endInput.value) : NaN;
        if (Number.isFinite(startYear) && Number.isFinite(endYear)) {
            hiddenInput.value = startYear + '-' + endYear;
            return;
        }
        hiddenInput.value = '';
    }

    function hydrateSchoolYearDateInputs(startInput, rawSchoolYear) {
        if (!startInput) return;
        var parsed = parseSchoolYear(rawSchoolYear);
        var endInputId = startInput.getAttribute('data-school-year-end') || '';
        var endInput = endInputId ? document.getElementById(endInputId) : null;
        startInput.value = dateFromYear(parsed.start);
        if (endInput) {
            endInput.value = dateFromYear(parsed.end);
        }
        syncSchoolYearValue(startInput);
    }

    function enableDatePickerInteraction(inputEl) {
        if (!inputEl) return;
        inputEl.addEventListener('pointerdown', function () {
            if (typeof inputEl.showPicker === 'function') {
                try { inputEl.showPicker(); } catch (e) {}
            }
        });
    }

    window.norsuSetSchoolYearFromValue = function (inputId, rawSchoolYear) {
        var startInput = document.getElementById(inputId);
        if (!startInput) return;
        hydrateSchoolYearDateInputs(startInput, rawSchoolYear);
    };

    document.querySelectorAll('[data-school-year-start]').forEach(function (startInput) {
        startInput.min = dateFromYear(currentYear);
        enableDatePickerInteraction(startInput);
        hydrateSchoolYearDateInputs(startInput, startInput.getAttribute('data-school-year-preset') || '');
        startInput.addEventListener('change', function () {
            syncSchoolYearValue(startInput);
        });
        var endInputId = startInput.getAttribute('data-school-year-end') || '';
        var endInput = endInputId ? document.getElementById(endInputId) : null;
        if (endInput) {
            endInput.min = dateFromYear(currentYear);
            enableDatePickerInteraction(endInput);
            endInput.addEventListener('change', function () {
                syncSchoolYearValue(startInput);
            });
        }
    });

    document.querySelectorAll('[data-school-year-end]').forEach(function (endInput) {
        var startInputId = endInput.getAttribute('data-school-year-start') || '';
        var startInput = startInputId ? document.getElementById(startInputId) : null;
        if (!startInput) return;
        endInput.addEventListener('change', function () {
            syncSchoolYearValue(startInput);
        });
    });
})();

(function () {
    var passwordModal = document.getElementById('coordinatorPasswordModal');
    var passwordForm = document.getElementById('coordinatorPasswordForm');
    var passwordHelp = document.getElementById('coordinatorPasswordHelp');
    if (passwordModal && passwordForm && passwordHelp) {
        passwordModal.addEventListener('show.bs.modal', function (event) {
            passwordForm.reset();
            var trigger = event.relatedTarget;
            if (!trigger) return;
            passwordForm.setAttribute('action', trigger.getAttribute('data-action') || '');
            passwordHelp.textContent = 'Set a new password for ' + (trigger.getAttribute('data-name') || 'this coordinator') + '.';
        });
    }

    var assignmentModal = document.getElementById('assignmentModal');
    var assignmentForm = document.getElementById('assignmentForm');
    var assignmentCourse = document.getElementById('assignmentCourse');
    var assignmentSection = document.getElementById('assignmentSection');
    var assignmentCustomSection = document.getElementById('assignmentCustomSection');
    var assignmentSemester = document.getElementById('assignmentSemester');
    var assignmentSchoolYear = document.getElementById('assignmentSchoolYear');
    var assignmentSchoolYearStart = document.getElementById('assignmentSchoolYearStart');
    var assignmentSchoolYearEnd = document.getElementById('assignmentSchoolYearEnd');
    var assignmentHelp = document.getElementById('assignmentModalHelp');
    if (assignmentModal && assignmentForm && assignmentCourse && assignmentHelp && assignmentSection) {
        function setSingleSelectValue(selectEl, rawValue) {
            var value = (rawValue || '').trim();
            selectEl.value = '';
            if (!value) return;

            var exists = Array.prototype.some.call(selectEl.options, function (opt) {
                return opt.value === value;
            });

            if (!exists) {
                var dynamicOption = document.createElement('option');
                dynamicOption.value = value;
                dynamicOption.textContent = value;
                dynamicOption.setAttribute('data-temp', '1');
                selectEl.appendChild(dynamicOption);
            }

            selectEl.value = value;
        }

        function clearTempOptions(selectEl) {
            Array.prototype.slice.call(selectEl.querySelectorAll('option[data-temp="1"]')).forEach(function (opt) {
                opt.remove();
            });
        }

        assignmentCourse.addEventListener('change', function () {
            var selectedPrograms = assignmentCourse.multiple
                ? getSelectedValues(assignmentCourse)
                : assignmentCourse.value;
            repopulateSectionSelect(assignmentSection, assignmentCustomSection, selectedPrograms, '');
        });

        assignmentModal.addEventListener('show.bs.modal', function (event) {
            assignmentForm.reset();
            clearTempOptions(assignmentCourse);
            var trigger = event.relatedTarget;
            if (!trigger) return;
            assignmentForm.setAttribute('action', trigger.getAttribute('data-action') || '');
            var mode = trigger.getAttribute('data-mode') || 'create';
            var coordinatorName = trigger.getAttribute('data-name') || 'this coordinator';

            if (mode === 'edit') {
                assignmentCourse.multiple = false;
                assignmentCourse.size = 1;
                assignmentCourse.name = 'course';
                assignmentCourse.required = true;
                setSingleSelectValue(assignmentCourse, trigger.getAttribute('data-course') || '');
            } else {
                assignmentCourse.multiple = true;
                assignmentCourse.size = 6;
                assignmentCourse.name = 'courses[]';
                assignmentCourse.required = true;
                Array.prototype.forEach.call(assignmentCourse.options, function (opt) {
                    opt.selected = false;
                });
            }

            repopulateSectionSelect(
                assignmentSection,
                assignmentCustomSection,
                assignmentCourse.multiple ? getSelectedValues(assignmentCourse) : assignmentCourse.value,
                trigger.getAttribute('data-section') || ''
            );
            assignmentSemester.value = trigger.getAttribute('data-semester') || '';
            if (typeof window.norsuSetSchoolYearFromValue === 'function') {
                window.norsuSetSchoolYearFromValue('assignmentSchoolYearStart', trigger.getAttribute('data-school-year') || '');
            } else if (assignmentSchoolYearStart) {
                assignmentSchoolYearStart.value = '';
                if (assignmentSchoolYearEnd) assignmentSchoolYearEnd.value = '';
                assignmentSchoolYear.value = '';
            }
            assignmentHelp.textContent = mode === 'edit'
                ? 'Update assignment for ' + coordinatorName + '.'
                : 'Link ' + coordinatorName + ' to one or more programs for one term and section.';
        });
    }
})();

(function () {
    document.querySelectorAll('[data-coord-details-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var wrap = btn.closest('.coord-action-toolbar-mobile');
            if (!wrap) return;
            var details = wrap.querySelector('[data-coord-details]');
            if (!details) return;
            var open = details.hasAttribute('hidden');
            if (open) {
                details.removeAttribute('hidden');
                btn.setAttribute('aria-expanded', 'true');
                btn.textContent = 'Hide details';
            } else {
                details.setAttribute('hidden', '');
                btn.setAttribute('aria-expanded', 'false');
                btn.textContent = 'View details';
            }
        });
    });
})();
</script>
@endpush


