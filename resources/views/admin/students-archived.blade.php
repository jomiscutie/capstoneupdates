@extends('layouts.admin')

@section('title', 'Archived students')

@section('content')
<div class="archived-students-page">
    <h1 class="page-title text-center">Archived students</h1>
    <p class="page-sub text-center">Soft-deleted records. Restoring returns a student to coordinators, reports, and login using the same student number.</p>

    <div class="card mb-4">
        <div class="card-body">
            @php($archBulkActive = $students->count() > 0)
            <div class="toolbar mb-3">
                <div>
                    <h2 class="h5 mb-1">Archived roster</h2>
                    <div class="text-muted small arch-intro-copy">Search by student number, name, or course. Use Restore to move someone back to the active list.</div>
                </div>
                <form action="{{ route('admin.students.archived') }}" method="GET" class="student-filter-form" role="search">
                    <div class="search-inner">
                        <i class="bi bi-search search-icon" aria-hidden="true"></i>
                        <input type="text" name="q" class="search-input" placeholder="Name, student no, course…" value="{{ $search ?? '' }}" aria-label="Search archived students">
                        @if(!empty($search))
                            <a href="{{ route('admin.students.archived') }}" class="search-clear" aria-label="Clear search"><i class="bi bi-x-lg"></i></a>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary btn-search"><i class="bi bi-search" aria-hidden="true"></i> Search</button>
                    @if(!empty($search))
                        <a href="{{ route('admin.students.archived') }}" class="btn btn-outline-secondary btn-search">Clear</a>
                    @endif
                </form>
            </div>

            @if($archBulkActive)
                <form id="archBulkRestoreForm" method="POST" action="{{ route('admin.students.archived.bulk.restore') }}" class="d-none" aria-hidden="true">
                    @csrf
                </form>
                <form id="archBulkRemoveForm" method="POST" action="{{ route('admin.students.archived.bulk.remove') }}" class="d-none" aria-hidden="true">
                    @csrf
                </form>
                <div class="arch-bulk-toolbar mb-3">
                    <p class="arch-bulk-hint mb-0">Select archived students, then bulk restore or permanently remove. Optional remarks apply to every removal in one batch.</p>
                    <div class="arch-bulk-note-block">
                        <label for="archBulkRemoveRemarks" class="arch-bulk-remarks-label">Remarks <span class="text-muted fw-normal">(bulk remove only)</span></label>
                        <textarea name="remarks" form="archBulkRemoveForm" id="archBulkRemoveRemarks" class="form-control form-control-sm arch-bulk-note" rows="2" maxlength="1000" placeholder="Optional context for permanent removals…"></textarea>
                    </div>
                    <div class="arch-bulk-actions-inline" role="group" aria-label="Bulk archive actions">
                        <button type="button" class="adm-q-bulk-btn adm-q-bulk-btn--approve" data-arch-bulk="restore" title="Restore selected" aria-label="Bulk restore selected students to the active list">
                            <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="adm-q-bulk-btn adm-q-bulk-btn--reject" data-arch-bulk="remove" title="Permanently remove selected" aria-label="Bulk permanently remove selected archived students">
                            <i class="bi bi-trash3" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            @endif

            <div class="arch-queue-table-frame">
                <div class="table-responsive student-table-wrap archived-table-wrap">
                    <table class="table align-middle mb-0 archived-students-table">
                        <colgroup>
                            @if($archBulkActive)
                                <col class="arch-col-select" style="width: 2.75rem">
                            @endif
                            <col class="arch-col-student">
                            <col class="arch-col-archived-at">
                            <col class="arch-col-actions" style="width: 5.25rem">
                        </colgroup>
                        <thead>
                            <tr>
                                @if($archBulkActive)
                                    <th scope="col" class="arch-queue-select-col text-center">
                                        <span class="arch-queue-checkbox-wrap">
                                            <input type="checkbox" id="archSelectAllShown" aria-label="Select all archived students on this page">
                                        </span>
                                    </th>
                                @endif
                                <th scope="col">Student</th>
                                <th scope="col" class="arch-th-archived">Archived at</th>
                                <th scope="col" class="text-center arch-th-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php($archEmptyCols = $archBulkActive ? 4 : 3)
                            @forelse($students as $student)
                                <tr>
                                    @if($archBulkActive)
                                        <td class="arch-queue-select-col text-center align-middle">
                                            <span class="arch-queue-checkbox-wrap">
                                                <input type="checkbox"
                                                       class="arch-row-select"
                                                       value="{{ $student->id }}"
                                                       aria-label="Select archived student {{ e($student->student_no) }}">
                                            </span>
                                        </td>
                                    @endif
                                    <td class="arch-queue-student-cell">
                                        <div class="arch-queue-cell-primary">{{ $student->name }}</div>
                                        <div class="arch-queue-cell-sub small text-muted">
                                            {{ $student->student_no }}@if(!empty($student->course)) — {{ $student->course }} @endif
                                        </div>
                                    </td>
                                    <td class="arch-queue-date-cell">
                                        @if($student->deleted_at)
                                            <div class="arch-queue-cell-primary">{{ $student->deleted_at->timezone(config('app.timezone'))->format('M d, Y') }}</div>
                                            <div class="arch-queue-cell-sub small text-muted">{{ $student->deleted_at->timezone(config('app.timezone'))->format('g:i A') }}</div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="arch-col-actions text-center align-middle">
                                        <div class="arch-action-stack">
                                            <form method="POST" action="{{ route('admin.students.restore', ['id' => $student->id]) }}" class="arch-restore-form m-0" data-norsu-confirm="Restore student {{ e($student->student_no) }} to the active list?">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="arch-btn-restore arch-action-btn"
                                                    title="Restore to active list"
                                                    aria-label="Restore student {{ e($student->student_no) }}, {{ e($student->name) }}, to the active list"
                                                >
                                                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                            <button
                                                type="button"
                                                class="arch-btn-remove arch-action-btn js-open-archive-remove-modal"
                                                data-remove-url="{{ route('admin.students.archived.remove', ['id' => $student->id]) }}"
                                                data-student-no="{{ $student->student_no }}"
                                                data-student-name="{{ $student->name }}"
                                                title="Permanently remove from archive"
                                                aria-label="Permanently remove archived student {{ e($student->student_no) }}, {{ e($student->name) }}"
                                            >
                                                <i class="bi bi-trash3" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $archEmptyCols }}" class="text-muted text-center py-5 arch-empty-cell">No archived students match this search.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($students->hasPages())
                <div class="d-flex justify-content-center mt-4 arch-pagination">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade arch-remove-modal" id="archiveRemoveModal" tabindex="-1" aria-labelledby="archiveRemoveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="archiveRemoveModalForm">
                @csrf
                <div class="modal-header border-0 pb-1">
                    <h5 class="modal-title" id="archiveRemoveModalLabel">
                        <i class="bi bi-trash3 me-1 text-danger" aria-hidden="true"></i> Permanently remove archived student
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-1">
                    <p class="text-muted small mb-2">This action cannot be undone. Please confirm the student you want to remove:</p>
                    <div class="arch-remove-student-meta mb-3">
                        <div class="meta-row">
                            <span class="meta-label">Student No</span>
                            <span class="meta-value" id="archiveRemoveStudentNo">-</span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Name</span>
                            <span class="meta-value" id="archiveRemoveStudentName">-</span>
                        </div>
                    </div>
                    <label for="archiveRemoveRemarks" class="form-label mb-1 fw-semibold">Remarks <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea
                        name="remarks"
                        id="archiveRemoveRemarks"
                        class="form-control arch-remove-remarks-field"
                        rows="3"
                        placeholder="Add context for this permanent removal..."
                    ></textarea>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary arch-modal-cancel dtr-mbtn dtr-mbtn--cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn arch-btn-remove arch-modal-submit dtr-mbtn dtr-mbtn--danger">
                        <i class="bi bi-trash3 me-1" aria-hidden="true"></i>Remove permanently
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade arch-confirm-modal" id="archiveRemoveConfirmModal" tabindex="-1" aria-labelledby="archiveRemoveConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-1">
                <h5 class="modal-title" id="archiveRemoveConfirmLabel">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> Confirm removal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
                <p class="small text-muted mb-2">This action is permanent and cannot be undone.</p>
                <div class="arch-remove-student-meta mb-0">
                    <div class="meta-row">
                        <span class="meta-label">Student No</span>
                        <span class="meta-value" id="archiveRemoveConfirmStudentNo">-</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-outline-secondary arch-modal-cancel dtr-mbtn dtr-mbtn--cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn arch-btn-remove arch-modal-submit dtr-mbtn dtr-mbtn--danger" id="archiveRemoveConfirmSubmit">
                    <i class="bi bi-trash3 me-1" aria-hidden="true"></i>Yes, remove
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .archived-students-page .arch-intro-copy {
        max-width: 28rem;
    }
    /* Archived page does not load students.blade.php styles — mirror Student Management filter row */
    .archived-students-page .toolbar .student-filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        align-items: center;
        justify-content: flex-end;
        width: min(100%, 880px);
    }
    .archived-students-page .toolbar .student-filter-form .search-inner {
        flex: 1 1 240px;
        min-width: 220px;
    }
    .archived-students-page .toolbar .student-filter-form .btn-search {
        white-space: nowrap;
    }
    @media (max-width: 991px) {
        .archived-students-page .toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        .archived-students-page .toolbar .student-filter-form {
            width: 100%;
        }
    }
    .archived-students-page .arch-queue-table-frame {
        border-radius: 12px;
        border: 1px solid var(--dtr-border-soft);
        overflow: hidden;
        background: color-mix(in srgb, var(--dtr-card-bg) 98%, var(--dtr-surface-soft) 2%);
        margin-bottom: 0;
    }
    .archived-students-page .student-table-wrap.archived-table-wrap {
        border: none;
        border-radius: 0;
        background: transparent;
    }
    .archived-students-page .archived-table-wrap {
        margin-bottom: 0;
    }
    .archived-students-page .archived-students-table {
        table-layout: fixed;
        width: 100%;
        margin-bottom: 0 !important;
    }
    .archived-students-page .arch-col-student {
        width: auto;
    }
    .archived-students-page .archived-students-table thead th {
        font-size: 0.6425rem;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: 0.065em;
        color: var(--dtr-muted) !important;
        background: color-mix(in srgb, var(--dtr-surface-soft) 92%, transparent) !important;
        border-bottom: 1px solid var(--dtr-border-soft);
        vertical-align: middle;
        padding: 0.85rem 0.72rem;
    }
    .archived-students-page .arch-th-actions,
    .archived-students-page .arch-th-archived {
        white-space: nowrap;
    }
    .archived-students-page .archived-students-table tbody td {
        padding: 0.92rem 0.72rem;
        vertical-align: middle !important;
        border-bottom: 1px solid var(--dtr-border-soft);
    }
    .archived-students-page .archived-students-table tbody tr:last-child td {
        border-bottom: none;
    }
    .archived-students-page .archived-students-table tbody tr:hover > td {
        background: var(--dtr-hover-bg) !important;
    }
    .archived-students-page .arch-queue-cell-primary {
        font-weight: 600;
        letter-spacing: -0.015em;
        color: var(--dtr-heading) !important;
        font-size: 0.92rem;
        line-height: 1.3;
    }
    .archived-students-page .arch-queue-cell-sub {
        margin-top: 0.15rem;
    }
    .archived-students-page .arch-queue-student-cell,
    .archived-students-page .arch-queue-date-cell {
        vertical-align: middle !important;
    }
    .archived-students-page .arch-col-actions {
        white-space: nowrap;
        padding-left: 0.35rem !important;
        padding-right: 0.35rem !important;
        text-align: center !important;
    }
    /* Bulk toolbar */
    .archived-students-page .arch-bulk-toolbar {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 0.65rem 0.85rem;
        align-items: end;
        padding: 0.75rem 0.85rem;
        border-radius: 12px;
        border: 1px solid var(--dtr-border-soft);
        background: color-mix(in srgb, var(--dtr-surface-soft) 94%, transparent);
    }
    @media (max-width: 719.98px) {
        .archived-students-page .arch-bulk-toolbar { grid-template-columns: 1fr; }
    }
    .archived-students-page .arch-bulk-hint {
        grid-column: 1 / -1;
        font-size: 0.78rem;
        color: var(--dtr-muted);
        line-height: 1.4;
        max-width: 42rem;
    }
    .archived-students-page .arch-bulk-note-block {
        grid-column: 1 / 2;
        min-width: 0;
    }
    .archived-students-page .arch-bulk-actions-inline {
        grid-column: 2;
        display: inline-flex;
        gap: 0.45rem;
        flex-shrink: 0;
        align-self: end;
    }
    @media (max-width: 719.98px) {
        .archived-students-page .arch-bulk-note-block { grid-column: 1 / -1; }
        .archived-students-page .arch-bulk-actions-inline {
            grid-column: 1 / -1;
            justify-content: flex-end;
        }
    }
    .archived-students-page .arch-bulk-remarks-label {
        display: block;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--dtr-muted);
        margin-bottom: 0.3rem;
    }
    .archived-students-page .arch-bulk-note {
        border-radius: 10px !important;
        font-size: 0.8375rem;
        resize: vertical;
        min-height: 2.5rem;
        border-color: var(--dtr-input-border) !important;
        background: var(--dtr-input-bg) !important;
        color: var(--dtr-text) !important;
    }
    .archived-students-page .adm-q-bulk-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.35rem;
        height: 2.35rem;
        padding: 0;
        margin: 0;
        border-radius: 10px;
        border-width: 1px;
        border-style: solid;
        background: transparent !important;
        cursor: pointer;
        transition: background 0.14s ease, border-color 0.14s ease, color 0.14s ease;
    }
    .archived-students-page .adm-q-bulk-btn .bi {
        font-size: 1.125rem;
        line-height: 1;
    }
    .archived-students-page .adm-q-bulk-btn--approve {
        border-color: color-mix(in srgb, #059669 55%, var(--dtr-input-border));
        color: #059669;
    }
    .archived-students-page .adm-q-bulk-btn--approve:hover {
        background: color-mix(in srgb, #059669 10%, transparent) !important;
    }
    .archived-students-page .adm-q-bulk-btn--reject {
        border-color: color-mix(in srgb, #e11d48 48%, var(--dtr-input-border));
        color: #e11d48;
    }
    .archived-students-page .adm-q-bulk-btn--reject:hover {
        background: color-mix(in srgb, #f43f5e 10%, transparent) !important;
    }
    html[data-theme="dark"] .archived-students-page .adm-q-bulk-btn--approve {
        color: #6ee7b7;
        border-color: rgba(52, 211, 153, 0.5);
    }
    html[data-theme="dark"] .archived-students-page .adm-q-bulk-btn--reject {
        color: #fda4af;
        border-color: rgba(251, 113, 133, 0.5);
    }
    .archived-students-page .arch-queue-select-col {
        width: 2.85rem;
        min-width: 2.85rem;
        text-align: center !important;
        padding-left: 0.35rem !important;
        padding-right: 0.35rem !important;
    }
    .archived-students-page .arch-queue-checkbox-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .archived-students-page .arch-queue-checkbox-wrap input[type="checkbox"] {
        appearance: none;
        -webkit-appearance: none;
        margin: 0;
        cursor: pointer;
        width: 1.0625rem;
        height: 1.0625rem;
        border: 1.5px solid var(--dtr-input-border);
        border-radius: 4px;
        background: var(--dtr-card-bg);
    }
    .archived-students-page .arch-queue-checkbox-wrap input[type="checkbox"]:checked {
        background-color: color-mix(in srgb, var(--dtr-primary) 78%, #059669);
        border-color: color-mix(in srgb, var(--dtr-primary) 65%, transparent);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23ffffff' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round' d='M3.8 8.2l2.9 2.9 5.6-6.3'/%3E%3C/svg%3E");
        background-size: 0.68rem auto;
        background-position: center;
        background-repeat: no-repeat;
    }
    .archived-students-page .arch-action-stack {
        display: inline-flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
        flex-wrap: nowrap;
        gap: 0.42rem;
        width: 100%;
    }
    .archived-students-page .arch-restore-form {
        display: inline-flex;
        margin: 0;
        align-items: center;
    }
    /* Row icon triggers: outline style (matches manual-request / invalidation row actions) */
    .archived-students-page .arch-action-btn.arch-btn-restore {
        border-radius: 8px;
        font-weight: 600;
        background: transparent !important;
        border: 1px solid color-mix(in srgb, #059669 52%, var(--dtr-input-border)) !important;
        color: #059669 !important;
        box-shadow: none !important;
    }
    .archived-students-page .arch-action-btn.arch-btn-restore:hover,
    .archived-students-page .arch-action-btn.arch-btn-restore:focus {
        background: color-mix(in srgb, #059669 9%, transparent) !important;
        border-color: #059669 !important;
        color: #047857 !important;
    }
    .archived-students-page .arch-action-btn.arch-btn-remove {
        border-radius: 8px;
        font-weight: 600;
        background: transparent !important;
        border: 1px solid color-mix(in srgb, #f43f5e 48%, var(--dtr-input-border)) !important;
        color: #e11d48 !important;
        box-shadow: none !important;
    }
    .archived-students-page .arch-action-btn.arch-btn-remove:hover,
    .archived-students-page .arch-action-btn.arch-btn-remove:focus {
        background: color-mix(in srgb, #f43f5e 10%, transparent) !important;
        border-color: #f43f5e !important;
        color: #be123c !important;
    }
    .archived-students-page .arch-action-btn:focus-visible {
        outline: 2px solid color-mix(in srgb, var(--dtr-primary) 55%, transparent);
        outline-offset: 2px;
    }
    html[data-theme="dark"] .archived-students-page .arch-action-btn.arch-btn-restore {
        color: #6ee7b7 !important;
        border-color: rgba(52, 211, 153, 0.55) !important;
        background: transparent !important;
    }
    html[data-theme="dark"] .archived-students-page .arch-action-btn.arch-btn-restore:hover,
    html[data-theme="dark"] .archived-students-page .arch-action-btn.arch-btn-restore:focus {
        background: rgba(52, 211, 153, 0.1) !important;
        border-color: rgba(52, 211, 153, 0.75) !important;
        color: #a7f3d0 !important;
    }
    html[data-theme="dark"] .archived-students-page .arch-action-btn.arch-btn-remove {
        color: #fda4af !important;
        border-color: rgba(251, 113, 133, 0.55) !important;
        background: transparent !important;
    }
    html[data-theme="dark"] .archived-students-page .arch-action-btn.arch-btn-remove:hover,
    html[data-theme="dark"] .archived-students-page .arch-action-btn.arch-btn-remove:focus {
        background: rgba(251, 113, 133, 0.1) !important;
        border-color: rgba(252, 165, 165, 0.65) !important;
        color: #fecaca !important;
    }
    .archived-students-page .arch-remove-modal .modal-content {
        border-radius: 16px;
        border: 1px solid var(--dtr-border-soft);
        background: var(--dtr-card-solid, var(--dtr-card-bg));
        box-shadow: var(--dtr-shadow-strong);
    }
    .archived-students-page .arch-confirm-modal .modal-content {
        border-radius: 16px;
        border: 1px solid rgba(220, 38, 38, 0.28);
        background: var(--dtr-card-solid, var(--dtr-card-bg));
        box-shadow: 0 18px 42px rgba(2, 6, 23, 0.42);
    }
    .archived-students-page .arch-confirm-modal .modal-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--dtr-heading);
    }
    .archived-students-page .arch-remove-modal .modal-title {
        font-size: 1.02rem;
        font-weight: 700;
        color: var(--dtr-heading);
    }
    .archived-students-page .arch-remove-student-meta {
        border: 1px solid var(--dtr-border-soft);
        border-radius: 12px;
        background: var(--dtr-surface-soft);
        padding: 0.75rem 0.85rem;
        display: grid;
        gap: 0.5rem;
    }
    .archived-students-page .arch-remove-student-meta .meta-row {
        display: flex;
        justify-content: space-between;
        gap: 0.85rem;
        align-items: baseline;
    }
    .archived-students-page .arch-remove-student-meta .meta-label {
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--dtr-muted);
    }
    .archived-students-page .arch-remove-student-meta .meta-value {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--dtr-text);
        text-align: right;
        overflow-wrap: anywhere;
    }
    .archived-students-page .arch-remove-remarks-field {
        border-radius: 10px;
        min-height: 90px;
        border: 1px solid var(--dtr-input-border);
        background: var(--dtr-input-bg);
        color: var(--dtr-text);
    }
    .archived-students-page .arch-remove-remarks-field:focus {
        border-color: rgba(220, 38, 38, 0.45);
        box-shadow: 0 0 0 0.2rem rgba(220, 38, 38, 0.12);
    }
    .archived-students-page .arch-action-btn {
        flex: 0 0 auto;
        box-sizing: border-box;
        width: 2.25rem;
        height: 2.25rem;
        min-width: 2.25rem;
        min-height: 2.25rem;
        margin: 0;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        line-height: 1;
        cursor: pointer;
        -webkit-appearance: none;
        appearance: none;
        vertical-align: middle;
    }
    .archived-students-page .arch-action-btn i {
        font-size: 1rem;
        line-height: 1;
        font-weight: 400;
    }
    .archived-students-page .arch-empty-cell {
        border: none;
    }
    .archived-students-page .arch-pagination .pagination {
        margin-bottom: 0;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    if (typeof bootstrap === 'undefined') return;
    var modalEl = document.getElementById('archiveRemoveModal');
    var confirmModalEl = document.getElementById('archiveRemoveConfirmModal');
    var formEl = document.getElementById('archiveRemoveModalForm');
    if (!modalEl || !confirmModalEl || !formEl) return;
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    var confirmModal = bootstrap.Modal.getOrCreateInstance(confirmModalEl);
    var studentNoEl = document.getElementById('archiveRemoveStudentNo');
    var studentNameEl = document.getElementById('archiveRemoveStudentName');
    var confirmStudentNoEl = document.getElementById('archiveRemoveConfirmStudentNo');
    var remarksEl = document.getElementById('archiveRemoveRemarks');
    var confirmSubmitEl = document.getElementById('archiveRemoveConfirmSubmit');
    var allowSubmit = false;

    document.querySelectorAll('.js-open-archive-remove-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            formEl.action = btn.getAttribute('data-remove-url') || '';
            if (studentNoEl) studentNoEl.textContent = btn.getAttribute('data-student-no') || '-';
            if (studentNameEl) studentNameEl.textContent = btn.getAttribute('data-student-name') || '-';
            if (remarksEl) remarksEl.value = '';
            modal.show();
        });
    });

    formEl.addEventListener('submit', function (e) {
        if (allowSubmit) return;
        e.preventDefault();
        if (confirmStudentNoEl && studentNoEl) {
            confirmStudentNoEl.textContent = studentNoEl.textContent || '-';
        }
        confirmModal.show();
    });

    if (confirmSubmitEl) {
        confirmSubmitEl.addEventListener('click', function () {
            allowSubmit = true;
            confirmModal.hide();
            formEl.submit();
        });
    }

    confirmModalEl.addEventListener('hidden.bs.modal', function () {
        allowSubmit = false;
    });
})();

function stripArchBulkDynamicInputs(form) {
    if (!form) return;
    Array.prototype.slice.call(form.querySelectorAll('input[data-arch-bulk-dynamic="1"]')).forEach(function (el) {
        el.parentNode.removeChild(el);
    });
}

function attachArchBulkStudentIds(form, checkboxes) {
    checkboxes.forEach(function (cb) {
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'student_ids[]';
        inp.value = cb.value;
        inp.setAttribute('data-arch-bulk-dynamic', '1');
        form.appendChild(inp);
    });
}

async function submitArchBulk(action) {
    var restoreForm = document.getElementById('archBulkRestoreForm');
    var removeForm = document.getElementById('archBulkRemoveForm');
    var checked = Array.prototype.slice.call(document.querySelectorAll('.arch-row-select:checked'));
    if (checked.length === 0) {
        if (window.norsuPrompt && window.norsuPrompt.alert) {
            await window.norsuPrompt.alert('Select at least one archived student first.', { variant: 'warning', title: 'Nothing selected' });
        }
        return;
    }
    if (action === 'restore') {
        var okRestore = window.norsuPrompt && window.norsuPrompt.confirm
            ? await window.norsuPrompt.confirm(
                'Restore ' + checked.length + ' archived student(s) to the active list?',
                { variant: 'warning', title: 'Bulk restore', confirmText: 'Yes, restore selected' }
            )
            : window.confirm('Restore selected students to the active list?');
        if (!okRestore) return;
        if (!restoreForm) return;
        stripArchBulkDynamicInputs(restoreForm);
        attachArchBulkStudentIds(restoreForm, checked);
        restoreForm.submit();
        return;
    }
    var okRm = window.norsuPrompt && window.norsuPrompt.confirm
        ? await window.norsuPrompt.confirm(
            'PERMANENTLY remove ' + checked.length + ' student record(s)? This cannot be undone.',
            { variant: 'danger', title: 'Bulk permanent removal', confirmText: 'Yes, permanently remove' }
        )
        : window.confirm('Permanently remove selected students?');
    if (!okRm || !removeForm) return;
    stripArchBulkDynamicInputs(removeForm);
    attachArchBulkStudentIds(removeForm, checked);
    removeForm.submit();
}

document.querySelectorAll('[data-arch-bulk]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        submitArchBulk(btn.getAttribute('data-arch-bulk'));
    });
});

(function setupArchBulkSelectAll() {
    var selectAll = document.getElementById('archSelectAllShown');
    var rowBoxes = Array.prototype.slice.call(document.querySelectorAll('.arch-row-select'));
    if (!selectAll || rowBoxes.length === 0) return;
    selectAll.addEventListener('change', function () {
        rowBoxes.forEach(function (cb) {
            cb.checked = selectAll.checked;
        });
    });
    rowBoxes.forEach(function (cb) {
        cb.addEventListener('change', function refreshArchSelectAllIndeterminate() {
            var sel = rowBoxes.filter(function (x) { return x.checked; }).length;
            selectAll.checked = sel > 0 && sel === rowBoxes.length;
            selectAll.indeterminate = sel > 0 && sel < rowBoxes.length;
        });
    });
})();
</script>
@endpush
