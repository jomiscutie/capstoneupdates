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
                            <td>
                                @php($invSlug = \Illuminate\Support\Str::slug((string) $row->invalidation_status, '-'))
                                <span class="inv-status-chip inv-status-chip--{{ $invSlug !== '' ? $invSlug : 'unknown' }}" title="{{ ucfirst((string) $row->invalidation_status) }}">{{ ucfirst((string) $row->invalidation_status) }}</span>
                            </td>
                            <td class="inv-col-actions">
                                @if($row->invalidation_status === 'requested')
                                    <div class="inv-action-stack">
                                        <button
                                            type="button"
                                            class="inv-action-btn inv-action-btn-approve js-open-admin-invalidation-modal"
                                            data-bs-toggle="modal"
                                            data-bs-target="#adminInvalidationActionModal"
                                            data-action="{{ route('admin.invalidations.review', $row) }}"
                                            data-field="review_note"
                                            data-decision="approve"
                                            data-title="Approve invalidation request"
                                            data-target="{{ $row->student->name ?? 'Student' }} — {{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}"
                                            data-placeholder="Optional note"
                                            title="Approve invalidation request"
                                            aria-label="Approve invalidation request for {{ e($row->student->name ?? 'student') }} on {{ \Carbon\Carbon::parse($row->date)->format('M j, Y') }}"
                                        ><i class="bi bi-check2" aria-hidden="true"></i></button>
                                        <button
                                            type="button"
                                            class="inv-action-btn inv-action-btn-reject js-open-admin-invalidation-modal"
                                            data-bs-toggle="modal"
                                            data-bs-target="#adminInvalidationActionModal"
                                            data-action="{{ route('admin.invalidations.review', $row) }}"
                                            data-field="review_note"
                                            data-decision="reject"
                                            data-title="Reject invalidation request"
                                            data-target="{{ $row->student->name ?? 'Student' }} — {{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}"
                                            data-placeholder="Reason for rejection"
                                            title="Reject invalidation request"
                                            aria-label="Reject invalidation request for {{ e($row->student->name ?? 'student') }} on {{ \Carbon\Carbon::parse($row->date)->format('M j, Y') }}"
                                        ><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                                    </div>
                                @elseif($row->is_invalid)
                                    <button
                                        type="button"
                                        class="inv-action-btn inv-action-btn-restore js-open-admin-invalidation-modal"
                                        data-bs-toggle="modal"
                                        data-bs-target="#adminInvalidationActionModal"
                                        data-action="{{ route('admin.invalidations.restore', $row) }}"
                                        data-field="reason"
                                        data-title="Restore attendance record"
                                        data-target="{{ $row->student->name ?? 'Student' }} — {{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}"
                                        data-placeholder="Restore note"
                                        title="Restore attendance record"
                                        aria-label="Restore attendance for {{ e($row->student->name ?? 'student') }} on {{ \Carbon\Carbon::parse($row->date)->format('M j, Y') }}"
                                    ><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i></button>
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
                    <p class="admin-inv-modal-target text-muted small mb-3" id="adminInvalidationTargetText">Selected attendance record</p>
                    <label for="adminInvalidationNoteInput" class="form-label admin-inv-modal-field-label">Note</label>
                    <div class="admin-inv-note-bubble">
                        <textarea id="adminInvalidationNoteInput" class="form-control admin-inv-note-textarea" rows="3" maxlength="1000"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 admin-inv-modal-footer">
                    <button type="button" class="btn dtr-mbtn dtr-mbtn--cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn dtr-mbtn dtr-mbtn--brand">
                        <i class="bi bi-send" aria-hidden="true"></i> Submit
                    </button>
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
        border-radius: 14px;
        background: var(--dtr-card-solid, var(--dtr-card-bg));
        box-shadow: var(--dtr-shadow-strong);
        overflow: hidden;
    }
    .admin-invalidation-modal .modal-title {
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--dtr-heading);
        font-size: 1.05rem;
    }
    .admin-invalidation-modal .modal-header {
        border-bottom-color: var(--dtr-border-soft);
        padding: 1rem 1.1rem;
    }
    .admin-inv-modal-field-label {
        font-size: 0.73rem !important;
        text-transform: uppercase;
        letter-spacing: 0.06em !important;
        color: var(--dtr-muted) !important;
        font-weight: 700 !important;
        margin-bottom: 0.3rem !important;
    }
    .admin-inv-modal-target {
        line-height: 1.45;
        padding: 0.52rem 0.65rem;
        border-radius: 10px;
        background: color-mix(in srgb, var(--dtr-surface-soft) 94%, transparent);
        border: 1px solid var(--dtr-border-soft);
        color: var(--dtr-text) !important;
    }
    .admin-inv-note-bubble {
        padding: 0.65rem 0.75rem;
        border-radius: 12px;
        background: color-mix(in srgb, var(--dtr-surface-soft) 94%, transparent);
        border: 1px solid var(--dtr-border-soft);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        margin-top: 0.2rem;
    }
    .admin-inv-note-bubble:focus-within {
        border-color: color-mix(in srgb, var(--dtr-primary) 42%, var(--dtr-input-border));
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--dtr-primary) 14%, transparent);
    }
    .admin-inv-note-textarea.form-control {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        border-radius: 0;
        padding: 0.15rem 0 !important;
        font-size: 0.9075rem;
        line-height: 1.5;
        resize: vertical;
        color: var(--dtr-text);
    }
    .admin-inv-note-textarea:focus {
        outline: none !important;
        box-shadow: none !important;
    }
    .admin-inv-modal-footer {
        padding-top: 0 !important;
        padding-bottom: 1rem !important;
        gap: 0.48rem !important;
    }
    .inv-status-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.34rem 0.72rem;
        border-radius: 999px;
        font-size: 0.625rem;
        font-weight: 750;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        line-height: 1.15;
        border: 1px solid transparent;
        max-width: 100%;
        box-sizing: border-box;
        text-align: center;
    }
    .inv-status-chip--requested {
        background: transparent;
        color: #b45309;
        border-color: color-mix(in srgb, #f59e0b 52%, transparent);
    }
    .inv-status-chip--approved {
        background: transparent;
        color: #047857;
        border-color: color-mix(in srgb, #10b981 52%, transparent);
    }
    .inv-status-chip--rejected {
        background: transparent;
        color: #b91c1c;
        border-color: color-mix(in srgb, #ef4444 52%, transparent);
    }
    html[data-theme="dark"] .inv-status-chip--requested { color: #fde68a; border-color: rgba(251, 191, 36, 0.45); }
    html[data-theme="dark"] .inv-status-chip--approved { color: #a7f3d0; border-color: rgba(52, 211, 153, 0.45); }
    html[data-theme="dark"] .inv-status-chip--rejected { color: #fecaca; border-color: rgba(248, 113, 113, 0.45); }
    .inv-status-chip--unknown {
        color: var(--dtr-muted);
        border-color: var(--dtr-border-soft);
        background: color-mix(in srgb, var(--dtr-surface-soft) 82%, transparent);
    }

    .inv-col-actions {
        width: 6.25rem;
        min-width: 6.25rem;
        text-align: center;
        vertical-align: middle;
    }
    .inv-action-stack {
        display: inline-flex;
        flex-direction: row;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: center;
        gap: 0.42rem;
    }
    .inv-action-btn {
        flex: 0 0 auto;
        box-sizing: border-box;
        width: 2.25rem;
        height: 2.25rem;
        min-width: 2.25rem;
        min-height: 2.25rem;
        margin: 0;
        padding: 0 !important;
        border-radius: 8px;
        font-weight: 600;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        border-width: 1px;
        border-style: solid;
        background: transparent !important;
        box-shadow: none !important;
        cursor: pointer;
        -webkit-appearance: none;
        appearance: none;
        vertical-align: middle;
        transition: background 0.14s ease, border-color 0.14s ease, color 0.14s ease;
    }
    .inv-action-btn .bi {
        font-size: 1rem;
        line-height: 1;
        font-weight: 400;
    }
    .inv-action-btn:hover {
        transform: none;
        filter: none;
    }
    .inv-action-btn-approve {
        border-color: color-mix(in srgb, #059669 52%, var(--dtr-input-border));
        color: #059669 !important;
    }
    .inv-action-btn-approve:hover,
    .inv-action-btn-approve:focus {
        background: color-mix(in srgb, #059669 9%, transparent) !important;
        border-color: #059669 !important;
        color: #047857 !important;
    }
    .inv-action-btn-reject {
        border-color: color-mix(in srgb, #f43f5e 48%, var(--dtr-input-border));
        color: #e11d48 !important;
    }
    .inv-action-btn-reject:hover,
    .inv-action-btn-reject:focus {
        background: color-mix(in srgb, #f43f5e 10%, transparent) !important;
        border-color: #f43f5e !important;
        color: #be123c !important;
    }
    .inv-action-btn-restore {
        border-color: color-mix(in srgb, #059669 52%, var(--dtr-input-border));
        color: #059669 !important;
    }
    .inv-action-btn-restore:hover,
    .inv-action-btn-restore:focus {
        background: color-mix(in srgb, #059669 9%, transparent) !important;
        border-color: #059669 !important;
        color: #047857 !important;
    }
    html[data-theme="dark"] .inv-action-btn-approve {
        color: #6ee7b7 !important;
        border-color: rgba(52, 211, 153, 0.55) !important;
    }
    html[data-theme="dark"] .inv-action-btn-approve:hover,
    html[data-theme="dark"] .inv-action-btn-approve:focus {
        background: rgba(52, 211, 153, 0.1) !important;
        border-color: rgba(52, 211, 153, 0.75) !important;
        color: #a7f3d0 !important;
    }
    html[data-theme="dark"] .inv-action-btn-reject {
        color: #fda4af !important;
        border-color: rgba(251, 113, 133, 0.55) !important;
    }
    html[data-theme="dark"] .inv-action-btn-reject:hover,
    html[data-theme="dark"] .inv-action-btn-reject:focus {
        background: rgba(251, 113, 133, 0.1) !important;
        border-color: rgba(252, 165, 165, 0.65) !important;
        color: #fecaca !important;
    }
    html[data-theme="dark"] .inv-action-btn-restore {
        color: #6ee7b7 !important;
        border-color: rgba(52, 211, 153, 0.55) !important;
    }
    html[data-theme="dark"] .inv-action-btn-restore:hover,
    html[data-theme="dark"] .inv-action-btn-restore:focus {
        background: rgba(52, 211, 153, 0.1) !important;
        border-color: rgba(52, 211, 153, 0.75) !important;
        color: #a7f3d0 !important;
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
