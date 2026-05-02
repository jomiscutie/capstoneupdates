@extends('layouts.admin')

@section('title', 'Manual Attendance Requests')

@push('styles')
<style>
    .manual-requests .page-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 0.35rem; }
    .manual-requests .page-sub { color: var(--dtr-muted); margin-bottom: 1rem; }
    .manual-requests .toolbar { margin-bottom: 1rem; }
    .manual-requests .search-wrap { margin-bottom: 0.9rem; width: 100%; max-width: min(520px, 100%); }
    .manual-requests .search-form { position: relative; width: 100%; max-width: 100%; }
    .manual-requests .search-input {
        width: 100%;
        min-height: 40px;
        padding: 0.5rem 2.5rem 0.5rem 2.75rem;
        font-size: 0.9rem;
        border: 1.5px solid var(--dtr-input-border);
        border-radius: 10px;
        background: var(--dtr-input-bg);
        color: var(--dtr-text);
    }
    .manual-requests .search-input:focus {
        outline: none;
        border-color: var(--dtr-primary);
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--dtr-primary) 22%, transparent);
    }
    .manual-requests .search-icon { position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); color: var(--dtr-muted); }
    .manual-requests .search-clear { position: absolute; right: 0.2rem; top: 50%; transform: translateY(-50%); border: none; background: transparent; color: var(--dtr-muted); }
    .manual-requests .toolbar .filter-box { min-width: 150px; }
    .manual-requests .toolbar .form-label {
        color: var(--dtr-muted);
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        margin-bottom: 0.25rem;
    }
    .manual-requests .toolbar .form-select {
        border-radius: 10px;
        border: 1px solid var(--dtr-input-border);
        background: var(--dtr-input-bg);
        color: var(--dtr-text);
        font-size: 0.9rem;
        font-weight: 500;
        min-height: 40px;
        padding: 0.4rem 2rem 0.4rem 0.65rem;
        box-shadow: none;
    }
    .manual-requests .toolbar .form-select:focus {
        border-color: var(--dtr-primary);
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--dtr-primary) 22%, transparent);
        outline: none;
    }
    .manual-requests .toolbar .form-select option {
        background: var(--dtr-card-bg);
        color: var(--dtr-text);
    }
    .manual-requests .table thead th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--dtr-muted);
    }
    .manual-requests .table tbody tr:hover > td,
    .manual-requests .table tbody tr:hover > th {
        background: var(--dtr-hover-bg) !important;
        color: var(--dtr-text) !important;
    }
    .manual-requests .table tbody tr:hover .times-list,
    .manual-requests .table tbody tr:hover .small,
    .manual-requests .table tbody tr:hover .text-muted {
        color: var(--dtr-muted) !important;
    }
    .manual-requests .table tbody tr:hover .fw-semibold,
    .manual-requests .table tbody tr:hover strong {
        color: var(--dtr-heading) !important;
    }
    .manual-requests .reason-cell {
        max-width: 340px;
        white-space: normal;
        line-height: 1.35;
    }
    .manual-requests .times-list { font-size: 0.83rem; line-height: 1.35; margin: 0; color: var(--dtr-muted); }
    .manual-requests .times-list strong { color: var(--dtr-heading); }
    .manual-requests .review-box { min-width: 230px; }
    .manual-requests .review-note-label {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--dtr-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.2rem;
    }
    .manual-requests .review-note-text {
        font-size: 0.84rem;
        color: var(--dtr-text);
        line-height: 1.35;
    }
    .manual-requests .bulk-panel {
        margin-bottom: 0.75rem;
        padding: 0.7rem;
        border: 1px solid var(--dtr-border-soft);
        border-radius: 10px;
        background: var(--dtr-surface-soft);
    }
    .manual-requests .bulk-panel .bulk-help {
        font-size: 0.8rem;
        color: var(--dtr-muted);
        margin: 0 0 0.5rem;
    }
    .manual-requests .status-chip {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.24rem 0.62rem;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .manual-requests .status-chip.status-pending { background: rgba(245, 158, 11, 0.16); color: #b45309; }
    .manual-requests .status-chip.status-approved { background: rgba(34, 197, 94, 0.16); color: #15803d; }
    .manual-requests .status-chip.status-rejected { background: rgba(239, 68, 68, 0.14); color: #b91c1c; }
    .manual-requests .btn.btn-danger {
        background: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #fff !important;
        box-shadow: none;
    }
    .manual-requests .btn.btn-danger:hover,
    .manual-requests .btn.btn-danger:focus {
        background: #b91c1c !important;
        border-color: #b91c1c !important;
        color: #fff !important;
    }
    .manual-requests .empty-state { text-align: center; padding: 2.2rem 1rem; color: var(--dtr-muted); }
    .manual-requests .empty-state i { font-size: 2.1rem; display: block; margin-bottom: 0.6rem; opacity: 0.6; }
</style>
@endpush

@section('content')
<div class="manual-requests">
    <h1 class="page-title">Manual Attendance Requests</h1>
    <p class="page-sub">Admin oversight for power interruption/logbook-based attendance requests.</p>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="toolbar">
                <div class="filter-box">
                    <label for="status" class="form-label small mb-1">Filter status</label>
                    <select class="form-select form-select-sm" id="status" name="status" onchange="this.form.submit()">
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                    </select>
                </div>
            </form>
            <div class="search-wrap">
                <form action="{{ route('admin.manual.requests') }}" method="GET" class="search-form" role="search">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <i class="bi bi-search search-icon" aria-hidden="true"></i>
                    <input type="text"
                           name="q"
                           id="manualSearchInput"
                           class="search-input"
                           placeholder="Search name, student no, course, wildcard (*, ?)"
                           value="{{ old('q', $search ?? '') }}"
                           autocomplete="off"
                           aria-label="Search manual requests">
                    <button type="button" class="search-clear" id="manualSearchClear" title="Clear search" aria-label="Clear search" style="display: none;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </form>
            </div>

            @if($requests->count() > 0)
                <form method="POST" action="{{ route('admin.manual.requests.bulk.review') }}" id="bulkReviewForm">
                    @csrf
                    <input type="hidden" name="decision" id="bulkDecisionInput">
                    <div id="bulkRequestIds"></div>
                    <div class="bulk-panel">
                        <p class="bulk-help">Select pending rows, then bulk approve or reject. One coordinator/admin note is applied to all selected rows.</p>
                        <textarea name="coordinator_note" id="bulkCoordinatorNote" class="form-control form-control-sm mb-2" rows="2" placeholder="Coordinator/Admin note (optional)"></textarea>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-success" onclick="submitBulkReview('approve')">
                                <i class="bi bi-check2-square"></i> Bulk Approve Selected
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="submitBulkReview('reject')">
                                <i class="bi bi-x-square"></i> Bulk Reject Selected
                            </button>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 38px;">
                                    <input type="checkbox" id="selectAllRows" aria-label="Select all pending rows">
                                </th>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Requested Times</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Coordinator/Admin Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $row)
                                @php
                                    $timeIn = $row->time_in ? \Carbon\Carbon::parse($row->time_in)->format('g:i A') : '-';
                                    $lunchOut = $row->lunch_break_out ? \Carbon\Carbon::parse($row->lunch_break_out)->format('g:i A') : '-';
                                    $afternoonIn = $row->afternoon_time_in ? \Carbon\Carbon::parse($row->afternoon_time_in)->format('g:i A') : '-';
                                    $timeOut = $row->time_out ? \Carbon\Carbon::parse($row->time_out)->format('g:i A') : '-';
                                @endphp
                                <tr>
                                    <td>
                                        @if($row->status === \App\Models\ManualAttendanceRequest::STATUS_PENDING)
                                            <input type="checkbox" class="row-select" name="request_ids[]" value="{{ $row->id }}" aria-label="Select request {{ $row->id }}">
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $row->attendance_date?->format('M d, Y') }}</div>
                                        <div class="small text-muted">Filed {{ $row->created_at?->format('M d, g:i A') }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $row->student?->name ?? 'Unknown student' }}</div>
                                        <div class="small text-muted">{{ $row->student?->student_no }}{{ !empty($row->student?->course) ? ' - '.$row->student?->course : '' }}</div>
                                    </td>
                                    <td>
                                        <p class="times-list"><strong>AM In:</strong> {{ $timeIn }}</p>
                                        <p class="times-list"><strong>Lunch Out:</strong> {{ $lunchOut }}</p>
                                        <p class="times-list"><strong>PM In:</strong> {{ $afternoonIn }}</p>
                                        <p class="times-list"><strong>Out:</strong> {{ $timeOut }}</p>
                                    </td>
                                    <td class="reason-cell">{{ $row->reason }}</td>
                                    <td>
                                        <span class="status-chip status-{{ $row->status }}">{{ ucfirst($row->status) }}</span>
                                        @if($row->reviewed_at)
                                            <div class="small text-muted mt-1">Reviewed {{ $row->reviewed_at->format('M d, g:i A') }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->status === \App\Models\ManualAttendanceRequest::STATUS_PENDING)
                                            <form method="POST" action="{{ route('admin.manual.requests.review', $row) }}" class="review-box">
                                                @csrf
                                                <textarea name="coordinator_note" class="form-control form-control-sm mb-2" rows="2" placeholder="Coordinator/Admin note (optional)"></textarea>
                                                <div class="d-flex gap-2">
                                                    <button type="submit" name="decision" value="approve" class="btn btn-sm btn-success" data-norsu-confirm="Approve this request and post attendance record?">
                                                        <i class="bi bi-check-circle"></i> Approve
                                                    </button>
                                                    <button type="submit" name="decision" value="reject" class="btn btn-sm btn-danger" data-norsu-confirm="Reject this request?" data-norsu-variant="danger">
                                                        <i class="bi bi-x-circle"></i> Reject
                                                    </button>
                                                </div>
                                            </form>
                                        @else
                                            <span class="review-note-label">Coordinator/Admin Note</span>
                                            <div class="small text-muted mb-1">{{ $row->reviewed_by ? 'Reviewed by coordinator' : 'Reviewed by admin' }}</div>
                                            <div class="review-note-text">{{ $row->coordinator_note ?: 'No note provided.' }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $requests->links() }}</div>
            @else
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    @if(!empty($search))
                        <p class="fw-semibold mb-1">No manual requests match "{{ e($search) }}".</p>
                        <p class="small mb-0">Try wildcard search such as <code>John*</code> or <code>*IT*</code>.</p>
                    @else
                        <p class="fw-semibold mb-1">No manual requests found.</p>
                        <p class="small mb-0">New manual attendance requests will appear here for admin oversight.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@push('scripts')
<script>
(function () {
    var searchInput = document.getElementById('manualSearchInput');
    var searchClear = document.getElementById('manualSearchClear');
    if (searchInput && searchClear) {
        function syncSearchClear() {
            searchClear.style.display = searchInput.value.trim() ? 'block' : 'none';
        }
        syncSearchClear();
        searchInput.addEventListener('input', syncSearchClear);
        searchClear.addEventListener('click', function () {
            searchInput.value = '';
            searchInput.form.submit();
        });
    }

    var selectAll = document.getElementById('selectAllRows');
    var rows = Array.prototype.slice.call(document.querySelectorAll('.row-select'));
    if (!selectAll || rows.length === 0) return;
    selectAll.addEventListener('change', function () {
        rows.forEach(function (cb) { cb.checked = selectAll.checked; });
    });
})();

async function submitBulkReview(decision) {
    var checkboxes = Array.prototype.slice.call(document.querySelectorAll('.row-select:checked'));
    if (checkboxes.length === 0) {
        await window.norsuPrompt.alert('Select at least one pending request first.', { variant: 'warning', title: 'Nothing selected' });
        return;
    }
    var confirmed = await window.norsuPrompt.confirm(
        decision === 'approve'
            ? 'Approve all selected pending requests?'
            : 'Reject all selected pending requests?',
        {
            variant: decision === 'approve' ? 'warning' : 'danger',
            title: 'Bulk review',
            confirmText: decision === 'approve' ? 'Yes, approve all' : 'Yes, reject all'
        }
    );
    if (!confirmed) {
        return;
    }
    var form = document.getElementById('bulkReviewForm');
    var idsWrap = document.getElementById('bulkRequestIds');
    var decisionInput = document.getElementById('bulkDecisionInput');
    idsWrap.innerHTML = '';
    checkboxes.forEach(function (cb) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'request_ids[]';
        input.value = cb.value;
        idsWrap.appendChild(input);
    });
    decisionInput.value = decision;
    form.submit();
}
</script>
@endpush
@endsection
