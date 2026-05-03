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
                <button type="submit" class="btn btn-sm dtr-apply-ghost"><span class="dtr-apply-ghost__text">Apply</span></button>
            </div>
        </form>
    </div>
</div>

<div class="card inv-review-queue-card">
    <div class="card-body">
        @if($status === 'requested' && $records->count() > 0)
            <form method="POST" action="{{ route('admin.invalidations.bulk.review') }}" id="invBulkReviewForm" class="inv-bulk-form">
                @csrf
                <input type="hidden" name="decision" id="invBulkDecisionInput" value="">
                <div class="inv-bulk-toolbar">
                    <p class="inv-bulk-hint mb-0">Select rows, then approve or reject. One note applies to all selected requests.</p>
                    <textarea name="review_note" id="invBulkReviewNote" class="form-control form-control-sm inv-bulk-note" rows="2" maxlength="1000" placeholder="Review note (optional)"></textarea>
                    <div class="inv-bulk-actions-inline" role="group" aria-label="Bulk invalidation actions">
                        <button type="button" class="adm-q-bulk-btn adm-q-bulk-btn--approve" data-inv-bulk="approve" title="Approve selected" aria-label="Bulk approve invalidation requests">
                            <i class="bi bi-check2" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="adm-q-bulk-btn adm-q-bulk-btn--reject" data-inv-bulk="reject" title="Reject selected" aria-label="Bulk reject invalidation requests">
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </form>
        @endif

        <div class="inv-queue-table-frame">
        <div class="table-responsive inv-queue-scroll">
            <table class="table align-middle mb-0 inv-queue-table">
                <thead>
                    <tr>
                        @if($status === 'requested')
                            <th scope="col" class="inv-queue-select-col">
                                <span class="inv-queue-checkbox-wrap">
                                    <input type="checkbox" id="invSelectAllShown" aria-label="Select all shown pending requests">
                                </span>
                            </th>
                        @endif
                        <th scope="col" class="inv-th-data">Date</th>
                        <th scope="col" class="inv-th-data">Student</th>
                        <th scope="col" class="inv-th-data">Requested by</th>
                        <th scope="col" class="inv-th-data inv-queue-reason-th">Reason</th>
                        <th scope="col" class="inv-th-data inv-th-status">Status</th>
                        <th scope="col" class="inv-col-actions text-center">Review</th>
                    </tr>
                </thead>
                <tbody>
                    @php($invEmptyCols = $status === 'requested' ? 7 : 6)
                    @forelse($records as $row)
                        <tr>
                            @if($status === 'requested')
                                <td class="inv-queue-select-col">
                                    @if($row->invalidation_status === 'requested')
                                        <span class="inv-queue-checkbox-wrap">
                                            <input type="checkbox"
                                                   class="inv-row-select"
                                                   name="attendance_ids[]"
                                                   value="{{ $row->id }}"
                                                   form="invBulkReviewForm"
                                                   aria-label="Select invalidation {{ $row->id }}">
                                        </span>
                                    @else
                                        <span class="inv-queue-checkbox-placeholder" aria-hidden="true"></span>
                                    @endif
                                </td>
                            @endif
                            <td class="inv-queue-date-cell">
                                <div class="inv-queue-cell-primary">{{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}</div>
                                @if($row->invalidation_requested_at)
                                    <div class="inv-queue-cell-sub small text-muted">Filed {{ $row->invalidation_requested_at->timezone(config('app.timezone'))->format('M d, g:i A') }}</div>
                                @endif
                            </td>
                            <td class="inv-queue-student-cell">
                                <div class="inv-queue-cell-primary">{{ $row->student->name ?? 'Unknown' }}</div>
                                <div class="inv-queue-cell-sub small text-muted">
                                    {{ $row->student->student_no ?? '-' }}@if(!empty($row->student?->course)) — {{ $row->student->course }} @endif
                                </div>
                            </td>
                            <td class="inv-queue-requested-by-cell small">
                                @php($invReqBy = trim((string) ($row->invalidatedByCoordinator->name ?? '')))
                                @if($invReqBy !== '')
                                    <span class="inv-requested-by-text" title="{{ e($invReqBy) }}">{{ $invReqBy }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="inv-queue-reason-cell">
                                @php($invReasonRaw = trim((string) ($row->invalidation_reason ?: '')))
                                @if($invReasonRaw !== '')
                                    <span class="inv-reason-pill" title="{{ e($invReasonRaw) }}">{{ $invReasonRaw }}</span>
                                @else
                                    <span class="inv-reason-pill inv-reason-pill--empty">—</span>
                                @endif
                            </td>
                            <td class="inv-queue-status-cell">
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
                        <tr><td colspan="{{ $invEmptyCols }}" class="text-muted text-center py-5">No invalidation records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
    .inv-queue-filter-control {
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

    .inv-review-queue-card .inv-bulk-form {
        margin-bottom: 1rem;
    }
    .inv-bulk-toolbar {
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
        .inv-bulk-toolbar { grid-template-columns: 1fr; }
    }
    .inv-bulk-hint {
        grid-column: 1 / -1;
        font-size: 0.78rem;
        color: var(--dtr-muted);
        line-height: 1.4;
        max-width: 42rem;
    }
    .inv-bulk-note {
        border-radius: 10px !important;
        font-size: 0.8375rem;
        resize: vertical;
        min-height: 2.5rem;
        border-color: var(--dtr-input-border) !important;
        background: var(--dtr-input-bg) !important;
        color: var(--dtr-text) !important;
    }
    .inv-bulk-actions-inline {
        display: inline-flex;
        gap: 0.45rem;
        flex-shrink: 0;
    }
    .adm-q-bulk-btn {
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
    .adm-q-bulk-btn .bi {
        font-size: 1.125rem;
        line-height: 1;
    }
    .adm-q-bulk-btn--approve {
        border-color: color-mix(in srgb, #059669 55%, var(--dtr-input-border));
        color: #059669;
    }
    .adm-q-bulk-btn--approve:hover {
        background: color-mix(in srgb, #059669 10%, transparent) !important;
    }
    .adm-q-bulk-btn--reject {
        border-color: color-mix(in srgb, #e11d48 48%, var(--dtr-input-border));
        color: #e11d48;
    }
    .adm-q-bulk-btn--reject:hover {
        background: color-mix(in srgb, #f43f5e 10%, transparent) !important;
    }
    html[data-theme="dark"] .adm-q-bulk-btn--approve {
        color: #6ee7b7;
        border-color: rgba(52, 211, 153, 0.5);
    }
    html[data-theme="dark"] .adm-q-bulk-btn--reject {
        color: #fda4af;
        border-color: rgba(251, 113, 133, 0.5);
    }

    .inv-queue-table-frame {
        border-radius: 12px;
        border: 1px solid var(--dtr-border-soft);
        overflow: hidden;
        background: color-mix(in srgb, var(--dtr-card-bg) 98%, var(--dtr-surface-soft) 2%);
    }
    /*
     * UA styles center <th>; body cells stay start-aligned — force headers to match column content edges.
     */
    .inv-queue-scroll .inv-queue-table thead th {
        font-size: 0.6425rem;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: 0.065em;
        color: var(--dtr-muted) !important;
        background: color-mix(in srgb, var(--dtr-surface-soft) 92%, transparent) !important;
        border-bottom: 1px solid var(--dtr-border-soft);
        vertical-align: middle;
        padding: 0.85rem 0.72rem;
        text-align: start;
    }
    .inv-queue-scroll .inv-queue-table thead th.inv-queue-select-col,
    .inv-queue-scroll .inv-queue-table thead th.inv-col-actions {
        text-align: center !important;
    }
    .inv-queue-select-col {
        width: 2.85rem;
        min-width: 2.85rem;
        text-align: center !important;
        padding-left: 0.35rem !important;
        padding-right: 0.35rem !important;
    }
    .inv-queue-checkbox-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .inv-queue-checkbox-wrap input[type="checkbox"] {
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
    .inv-queue-checkbox-wrap input[type="checkbox"]:checked {
        background-color: color-mix(in srgb, var(--dtr-primary) 78%, #059669);
        border-color: color-mix(in srgb, var(--dtr-primary) 65%, transparent);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23ffffff' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round' d='M3.8 8.2l2.9 2.9 5.6-6.3'/%3E%3C/svg%3E");
        background-size: 0.68rem auto;
        background-position: center;
        background-repeat: no-repeat;
    }
    .inv-queue-checkbox-placeholder {
        display: inline-block;
        width: 1.0625rem;
        height: 1.0625rem;
    }
    .inv-queue-table tbody td {
        padding: 0.92rem 0.72rem;
        vertical-align: middle !important;
        border-bottom: 1px solid var(--dtr-border-soft);
    }
    /* Match header edge to body edge for typed columns */
    .inv-queue-table tbody td.inv-queue-date-cell,
    .inv-queue-table tbody td.inv-queue-student-cell,
    .inv-queue-table tbody td.inv-queue-requested-by-cell,
    .inv-queue-table tbody td.inv-queue-reason-cell,
    .inv-queue-table tbody td.inv-queue-status-cell {
        text-align: start;
        vertical-align: middle !important;
    }
    .inv-queue-table tbody tr:hover > td {
        background: var(--dtr-hover-bg) !important;
    }
    .inv-queue-cell-primary {
        font-weight: 600;
        letter-spacing: -0.015em;
        color: var(--dtr-heading) !important;
        font-size: 0.92rem;
        line-height: 1.3;
    }
    .inv-queue-date-cell .inv-queue-cell-sub,
    .inv-queue-student-cell .inv-queue-cell-sub {
        margin-top: 0.15rem;
    }
    /* Header label lines up with text inside bordered pills/chips below (same inline inset). */
    .inv-queue-scroll .inv-queue-table thead th.inv-queue-reason-th {
        padding: 0.85rem 0.72rem 0.85rem calc(0.72rem + 1px + 0.95rem);
    }
    .inv-queue-scroll .inv-queue-table thead th.inv-th-status {
        padding: 0.85rem 0.72rem 0.85rem calc(0.72rem + 1px + 0.72rem);
    }
    .inv-queue-reason-th { min-width: 7rem; }
    .inv-queue-requested-by-cell {
        max-width: 12rem;
        min-width: 0;
    }
    .inv-requested-by-text {
        display: block;
        color: var(--dtr-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .inv-queue-reason-cell {
        max-width: 18rem;
        min-width: 0;
    }
    .inv-reason-pill {
        display: inline-flex;
        align-items: center;
        justify-content: flex-start;
        max-width: 100%;
        box-sizing: border-box;
        padding: 0.45rem 0.95rem;
        min-height: 2rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        line-height: 1.25;
        letter-spacing: -0.01em;
        text-align: start;
        color: #064e3b;
        background: color-mix(in srgb, #ecfdf5 100%, transparent);
        border: 1px solid color-mix(in srgb, #34d399 52%, transparent);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
    }
    .inv-reason-pill--empty {
        font-weight: 500;
        color: var(--dtr-muted);
        background: color-mix(in srgb, var(--dtr-surface-soft) 94%, transparent);
        border-color: var(--dtr-border-soft);
        opacity: 0.92;
    }
    html[data-theme="dark"] .inv-reason-pill {
        color: #d1fae5;
        background: rgba(16, 185, 129, 0.12);
        border-color: rgba(52, 211, 153, 0.38);
    }
    html[data-theme="dark"] .inv-reason-pill--empty {
        color: var(--dtr-muted);
        background: color-mix(in srgb, var(--dtr-card-bg) 88%, transparent);
        border-color: var(--dtr-border-soft);
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

async function submitInvBulkInvalidations(decision) {
    var form = document.getElementById('invBulkReviewForm');
    if (!form) return;
    var checked = Array.prototype.slice.call(document.querySelectorAll('.inv-row-select:checked'));
    if (checked.length === 0) {
        await window.norsuPrompt.alert('Select at least one pending request first.', { variant: 'warning', title: 'Nothing selected' });
        return;
    }
    var ok = await window.norsuPrompt.confirm(
        decision === 'approve'
            ? 'Approve all selected invalidation requests? Attendance rows will be marked invalid where approved.'
            : 'Reject all selected invalidation requests?',
        {
            variant: decision === 'approve' ? 'warning' : 'danger',
            title: 'Bulk invalidation review',
            confirmText: decision === 'approve' ? 'Yes, approve all' : 'Yes, reject all'
        }
    );
    if (!ok) return;
    var decisionInput = document.getElementById('invBulkDecisionInput');
    if (decisionInput) decisionInput.value = decision;
    form.submit();
}

(function setupInvBulkSelectAll() {
    var selectAll = document.getElementById('invSelectAllShown');
    var rowBoxes = Array.prototype.slice.call(document.querySelectorAll('.inv-row-select'));
    if (!selectAll || rowBoxes.length === 0) return;
    selectAll.addEventListener('change', function () {
        rowBoxes.forEach(function (cb) {
            cb.checked = selectAll.checked;
        });
    });
    rowBoxes.forEach(function (cb) {
        cb.addEventListener('change', function refreshInvSelectAllIndeterminate() {
            var sel = rowBoxes.filter(function (x) { return x.checked; }).length;
            selectAll.checked = sel > 0 && sel === rowBoxes.length;
            selectAll.indeterminate = sel > 0 && sel < rowBoxes.length;
        });
    });
})();

document.querySelectorAll('[data-inv-bulk]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        submitInvBulkInvalidations(btn.getAttribute('data-inv-bulk'));
    });
});
</script>
@endpush
