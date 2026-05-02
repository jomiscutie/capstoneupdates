@extends('layouts.admin')

@section('title', 'Invalidation Queue')

@section('content')
<h1 class="page-title">Attendance Invalidation Queue</h1>
<p class="page-sub text-center">Review coordinator fraud reports before records are excluded from official totals.</p>

<div class="card mb-3 inv-queue-filter-card">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.invalidations') }}" class="inv-queue-filter-form" role="search">
            <div class="inv-queue-filter-field inv-queue-filter-field--status">
                <label class="inv-queue-filter-label" for="inv-filter-status">Status</label>
                <select id="inv-filter-status" name="status" class="form-select form-select-sm inv-queue-filter-control">
                    <option value="requested" {{ $status === 'requested' ? 'selected' : '' }}>Requested</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="inv-queue-filter-field inv-queue-filter-field--search">
                <label class="inv-queue-filter-label" for="inv-filter-q">Search</label>
                <input type="search" id="inv-filter-q" name="q" value="{{ $q }}" class="form-control form-control-sm inv-queue-filter-control" placeholder="Name, student no, or course" autocomplete="off" enterkeyhint="search">
            </div>
            <div class="inv-queue-filter-field inv-queue-filter-field--action">
                <button type="submit" class="btn btn-sm inv-queue-filter-submit">Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Requested by</th>
                        <th>Reason</th>
                        <th>Status</th>
                            <th class="inv-col-actions text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $row->student->name ?? 'Unknown' }}</div>
                                <div class="small text-muted">{{ $row->student->student_no ?? '-' }}</div>
                            </td>
                            <td>{{ $row->invalidatedByCoordinator->name ?? '-' }}</td>
                            <td style="max-width: 320px;">{{ $row->invalidation_reason }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($row->invalidation_status) }}</span></td>
                            <td class="inv-col-actions">
                                @if($row->invalidation_status === 'requested')
                                    <div class="inv-action-stack">
                                        <button
                                            type="button"
                                            class="btn btn-sm inv-action-btn inv-action-btn-approve js-open-admin-invalidation-modal"
                                            data-bs-toggle="modal"
                                            data-bs-target="#adminInvalidationActionModal"
                                            data-action="{{ route('admin.invalidations.review', $row) }}"
                                            data-field="review_note"
                                            data-decision="approve"
                                            data-title="Approve invalidation request"
                                            data-target="{{ $row->student->name ?? 'Student' }} — {{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}"
                                            data-placeholder="Optional note"
                                        >Approve</button>
                                        <button
                                            type="button"
                                            class="btn btn-sm inv-action-btn inv-action-btn-reject js-open-admin-invalidation-modal"
                                            data-bs-toggle="modal"
                                            data-bs-target="#adminInvalidationActionModal"
                                            data-action="{{ route('admin.invalidations.review', $row) }}"
                                            data-field="review_note"
                                            data-decision="reject"
                                            data-title="Reject invalidation request"
                                            data-target="{{ $row->student->name ?? 'Student' }} — {{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}"
                                            data-placeholder="Reason for rejection"
                                        >Reject</button>
                                    </div>
                                @elseif($row->is_invalid)
                                    <button
                                        type="button"
                                        class="btn btn-sm inv-action-btn inv-action-btn-restore js-open-admin-invalidation-modal"
                                        data-bs-toggle="modal"
                                        data-bs-target="#adminInvalidationActionModal"
                                        data-action="{{ route('admin.invalidations.restore', $row) }}"
                                        data-field="reason"
                                        data-title="Restore attendance record"
                                        data-target="{{ $row->student->name ?? 'Student' }} — {{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}"
                                        data-placeholder="Restore note"
                                    >Restore</button>
                                @else
                                    <span class="text-muted small">No action available</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No invalidation records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $records->links() }}</div>
    </div>
</div>

<div class="modal fade" id="adminInvalidationActionModal" tabindex="-1" aria-labelledby="adminInvalidationActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-invalidation-modal">
            <form method="POST" id="adminInvalidationActionForm">
                @csrf
                <input type="hidden" id="adminInvalidationDecisionInput" name="decision" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminInvalidationActionModalLabel">Invalidation action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2" id="adminInvalidationTargetText">Selected attendance record</p>
                    <label for="adminInvalidationNoteInput" class="form-label">Note</label>
                    <textarea id="adminInvalidationNoteInput" class="form-control" rows="3" maxlength="1000"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .inv-queue-filter-card .card-body {
        padding: 0.65rem 0.85rem;
    }
    .inv-queue-filter-form {
        --inv-filter-h: 2.125rem;
        display: grid;
        gap: 0.5rem 0.75rem;
        align-items: end;
        grid-template-columns: minmax(0, 10rem) minmax(0, 1fr) auto;
    }
    @media (max-width: 575.98px) {
        .inv-queue-filter-form {
            grid-template-columns: 1fr;
        }
    }
    .inv-queue-filter-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 650;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: var(--dtr-muted);
        margin-bottom: 0.28rem;
        line-height: 1.2;
    }
    .inv-queue-filter-control,
    .inv-queue-filter-submit {
        min-height: var(--inv-filter-h);
        height: var(--inv-filter-h);
        font-size: 0.8125rem;
        border-radius: 8px;
        box-sizing: border-box;
    }
    .inv-queue-filter-form .inv-queue-filter-control {
        padding-top: 0.28rem;
        padding-bottom: 0.28rem;
        border: 1px solid var(--dtr-input-border);
        background: var(--dtr-input-bg) !important;
        color: var(--dtr-text) !important;
    }
    .inv-queue-filter-form .inv-queue-filter-control:focus {
        border-color: color-mix(in srgb, var(--dtr-primary) 45%, var(--dtr-input-border));
        box-shadow: 0 0 0 2px var(--dtr-primary-soft);
        outline: none;
    }
    .inv-queue-filter-form select.inv-queue-filter-control {
        min-width: min(100%, 9.5rem);
        padding-right: 2rem;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.55rem center;
        background-size: 10px 10px;
        appearance: none;
        -webkit-appearance: none;
    }
    html[data-theme="dark"] .inv-queue-filter-form select.inv-queue-filter-control {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
    }
    .inv-queue-filter-form select.inv-queue-filter-control option {
        background: var(--dtr-card-solid);
        color: var(--dtr-text);
    }
    .inv-queue-filter-submit {
        padding: 0 0.95rem;
        font-weight: 600;
        border: 1px solid color-mix(in srgb, var(--dtr-primary) 42%, var(--dtr-border-soft));
        background: transparent;
        color: var(--dtr-primary);
        white-space: nowrap;
        line-height: 1.2;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }
    .inv-queue-filter-submit:hover {
        background: var(--dtr-primary-soft);
        border-color: color-mix(in srgb, var(--dtr-primary) 55%, var(--dtr-border-soft));
        color: var(--dtr-heading);
    }
    .inv-queue-filter-submit:focus-visible {
        outline: none;
        box-shadow: 0 0 0 2px var(--dtr-primary-soft);
    }
    .admin-invalidation-modal {
        border: 1px solid var(--dtr-border-soft);
        border-radius: 16px;
        background: linear-gradient(180deg, color-mix(in srgb, var(--dtr-card-solid) 94%, white 6%), var(--dtr-card-solid));
        box-shadow: var(--dtr-shadow-strong);
    }
    .admin-invalidation-modal .modal-title {
        font-weight: 800;
        color: var(--dtr-heading);
    }
    .admin-invalidation-modal .modal-header {
        border-bottom-color: var(--dtr-border-soft);
    }
    .admin-invalidation-modal .form-label {
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--dtr-muted);
        font-weight: 700;
    }
    .admin-invalidation-modal .form-control {
        border-radius: 12px;
        border: 1px solid var(--dtr-input-border);
        background: var(--dtr-input-bg);
        color: var(--dtr-text);
    }
    .admin-invalidation-modal .form-control:focus {
        border-color: rgba(45,212,191,0.72);
        box-shadow: 0 0 0 4px rgba(45,212,191,0.14);
    }
    .inv-col-actions {
        width: 9.25rem;
        min-width: 9.25rem;
        text-align: center;
        vertical-align: middle;
    }
    .inv-action-stack {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.38rem;
        width: 100%;
    }
    .inv-action-btn {
        width: 100%;
        min-height: 2.05rem;
        border-radius: 999px;
        padding: 0.36rem 0.8rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-width: 1px;
        transition: filter 0.15s ease, transform 0.15s ease;
    }
    .inv-action-btn:hover {
        transform: translateY(-1px);
        filter: brightness(1.05);
    }
    .inv-action-btn-approve {
        background: #059669;
        border-color: #059669;
        color: #fff;
    }
    .inv-action-btn-approve:hover,
    .inv-action-btn-approve:focus {
        background: #047857;
        border-color: #047857;
        color: #fff;
    }
    .inv-action-btn-reject {
        background: #dc2626;
        border-color: #dc2626;
        color: #fff;
    }
    .inv-action-btn-reject:hover,
    .inv-action-btn-reject:focus {
        background: #b91c1c;
        border-color: #b91c1c;
        color: #fff;
    }
    .inv-action-btn-restore {
        background: #059669;
        border-color: #059669;
        color: #fff;
    }
    .inv-action-btn-restore:hover,
    .inv-action-btn-restore:focus {
        background: #047857;
        border-color: #047857;
        color: #fff;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var form = document.getElementById('adminInvalidationActionForm');
    var title = document.getElementById('adminInvalidationActionModalLabel');
    var targetText = document.getElementById('adminInvalidationTargetText');
    var noteInput = document.getElementById('adminInvalidationNoteInput');
    var decisionInput = document.getElementById('adminInvalidationDecisionInput');
    if (!form || !noteInput || !decisionInput) return;

    document.querySelectorAll('.js-open-admin-invalidation-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            form.setAttribute('action', btn.getAttribute('data-action') || '');
            var fieldName = btn.getAttribute('data-field') || 'review_note';
            noteInput.setAttribute('name', fieldName);
            noteInput.setAttribute('placeholder', btn.getAttribute('data-placeholder') || 'Add note');
            noteInput.value = '';

            var modalTitle = btn.getAttribute('data-title') || 'Invalidation action';
            title.textContent = modalTitle;
            targetText.textContent = btn.getAttribute('data-target') || 'Selected attendance record';

            var decision = btn.getAttribute('data-decision') || '';
            decisionInput.value = decision;
            decisionInput.disabled = decision === '';
        });
    });
})();
</script>
@endpush
