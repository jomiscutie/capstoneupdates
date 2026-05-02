@extends('layouts.admin')

@section('title', 'Archived students')

@section('content')
<div class="archived-students-page">
    <h1 class="page-title text-center">Archived students</h1>
    <p class="page-sub text-center">Soft-deleted records. Restoring returns a student to coordinators, reports, and login using the same student number.</p>

    <div class="card mb-4">
        <div class="card-body">
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
                    <button type="submit" class="btn btn-primary btn-search"><i class="bi bi-search me-1"></i>Search</button>
                    @if(!empty($search))
                        <a href="{{ route('admin.students.archived') }}" class="btn btn-outline-secondary btn-search">Clear</a>
                    @endif
                </form>
            </div>

            <div class="table-responsive student-table-wrap archived-table-wrap">
                <table class="table align-middle mb-0 archived-students-table">
                    <colgroup>
                        <col class="arch-col-student-no">
                        <col class="arch-col-name">
                        <col class="arch-col-course">
                        <col class="arch-col-archived-at">
                        <col class="arch-col-actions">
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col" class="text-center text-nowrap">Student No</th>
                            <th scope="col" class="text-center text-nowrap">Name</th>
                            <th scope="col" class="text-center text-nowrap">Course</th>
                            <th scope="col" class="arch-col-archived-at text-center text-nowrap">Archived at</th>
                            <th scope="col" class="arch-col-actions text-center text-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td class="fw-semibold text-center">{{ $student->student_no }}</td>
                                <td class="text-center">{{ $student->name }}</td>
                                <td class="text-center"><span class="text-muted">{{ $student->course ?: '—' }}</span></td>
                                <td class="arch-col-archived-at text-center">
                                    @if($student->deleted_at)
                                        <span class="text-muted arch-archived-meta">{{ $student->deleted_at->timezone(config('app.timezone'))->format('M j, Y') }}</span>
                                        <span class="text-muted arch-archived-time d-block small">{{ $student->deleted_at->timezone(config('app.timezone'))->format('g:i A') }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="arch-col-actions text-center">
                                    <div class="arch-action-stack">
                                    <form method="POST" action="{{ route('admin.students.restore', ['id' => $student->id]) }}" class="arch-restore-form m-0" data-norsu-confirm="Restore student {{ e($student->student_no) }} to the active list?">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-success arch-btn-restore arch-action-btn"
                                            title="Restore"
                                            aria-label="Restore student"
                                        >
                                            <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger arch-btn-remove arch-action-btn js-open-archive-remove-modal"
                                        data-remove-url="{{ route('admin.students.archived.remove', ['id' => $student->id]) }}"
                                        data-student-no="{{ $student->student_no }}"
                                        data-student-name="{{ $student->name }}"
                                        title="Remove"
                                        aria-label="Remove student"
                                    >
                                        <i class="bi bi-trash3" aria-hidden="true"></i>
                                    </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted text-center py-5 arch-empty-cell">No archived students match this search.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
                    <button type="button" class="btn btn-outline-secondary arch-modal-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn arch-btn-remove arch-modal-submit">
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
                <button type="button" class="btn btn-outline-secondary arch-modal-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn arch-btn-remove arch-modal-submit" id="archiveRemoveConfirmSubmit">
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
    .archived-students-page .archived-table-wrap {
        border-radius: 16px;
    }
    .archived-students-page .archived-students-table {
        table-layout: fixed;
        width: 100%;
    }
    .archived-students-page col.arch-col-student-no { width: 9rem; }
    .archived-students-page col.arch-col-name { width: 22%; }
    .archived-students-page col.arch-col-course { width: 20%; }
    .archived-students-page col.arch-col-archived-at { width: 9.75rem; }
    .archived-students-page col.arch-col-actions { width: 7.25rem; }
    .archived-students-page .archived-students-table thead th,
    .archived-students-page .archived-students-table tbody td {
        text-align: center !important;
        vertical-align: middle;
        padding-left: 0.65rem;
        padding-right: 0.65rem;
    }
    .archived-students-page .archived-students-table thead th {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--dtr-muted) !important;
        border-bottom: 1px solid var(--dtr-border-soft);
    }
    .archived-students-page .arch-col-actions {
        white-space: nowrap;
        padding-left: 0.35rem !important;
        padding-right: 0.35rem !important;
    }
    .archived-students-page .arch-action-stack {
        display: inline-flex;
        flex-direction: column;
        align-items: stretch;
        justify-content: center;
        gap: 0.45rem;
        width: 100%;
    }
    .archived-students-page .arch-archived-meta {
        font-weight: 600;
        color: var(--dtr-text);
    }
    .archived-students-page .arch-archived-time {
        margin-top: 0.15rem;
    }
    .archived-students-page .arch-restore-form {
        display: block;
        text-align: center;
    }
    .archived-students-page .arch-btn-restore {
        border-radius: 999px;
        font-weight: 600;
        background: #059669;
        border-color: #059669;
        color: #fff;
    }
    .archived-students-page .arch-btn-restore:hover,
    .archived-students-page .arch-btn-restore:focus {
        background: #047857;
        border-color: #047857;
        color: #fff;
    }
    .archived-students-page .arch-btn-remove {
        border-radius: 999px;
        font-weight: 600;
        background: #dc2626;
        border-color: #dc2626;
        color: #fff;
    }
    .archived-students-page .arch-btn-remove:hover,
    .archived-students-page .arch-btn-remove:focus {
        background: #b91c1c;
        border-color: #b91c1c;
        color: #fff;
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
    .archived-students-page .arch-modal-cancel {
        border-radius: 999px;
        padding: 0.42rem 1rem;
        font-weight: 600;
    }
    .archived-students-page .arch-modal-submit {
        border-radius: 999px;
        font-weight: 700;
        padding: 0.42rem 1rem;
    }
    .archived-students-page .arch-action-btn {
        width: 68%;
        margin-inline: auto;
        min-height: 2rem;
        padding: 0.3rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1.1;
    }
    .archived-students-page .arch-action-btn i {
        font-size: 0.9rem;
        line-height: 1;
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
</script>
@endpush
