@extends('layouts.coordinator')

@section('title', 'Manual Attendance Requests')

@push('styles')
<style>
    .manual-requests .page-title { font-size: 1.5rem; font-weight: 700; text-align: center; margin-bottom: 0.35rem; }
    .manual-requests .page-sub { text-align: center; color: var(--dtr-muted); margin-bottom: 1.2rem; }
    .manual-requests .filter-row { display: flex; justify-content: space-between; align-items: center; gap: 0.8rem; margin-bottom: 1rem; flex-wrap: wrap; }
    .manual-requests .search-wrap { margin-bottom: 0.9rem; }
    .manual-requests .search-form { position: relative; max-width: 340px; }
    .manual-requests .search-input {
        width: 100%;
        padding: 0.4rem 2rem 0.4rem 2rem;
        font-size: 0.86rem;
        border: none;
        border-bottom: 2px solid var(--dtr-input-border);
        border-radius: 0;
        background: transparent;
        color: var(--dtr-text);
    }
    .manual-requests .search-input:focus { outline: none; border-color: var(--dtr-primary); background: rgba(37, 99, 235, 0.04); }
    .manual-requests .search-icon { position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); color: var(--dtr-muted); }
    .manual-requests .search-clear { position: absolute; right: 0.2rem; top: 50%; transform: translateY(-50%); border: none; background: transparent; color: var(--dtr-muted); }
    .manual-requests .filter-box { min-width: 150px; }
    .manual-requests .filter-box .form-label {
        color: #8ca3b8;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: none;
        margin-bottom: 0.25rem;
    }
    .manual-requests .filter-box .form-select {
        border-radius: 8px;
        border: 1px solid rgba(37, 99, 235, 0.55);
        background: linear-gradient(180deg, rgba(10, 17, 32, 0.92), rgba(8, 14, 28, 0.96));
        color: #f8fbff;
        font-size: 0.88rem;
        font-weight: 600;
        padding: 0.34rem 1.9rem 0.34rem 0.62rem;
        box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.2), 0 0 0 2px rgba(37, 99, 235, 0.12);
        border-color: rgba(37, 99, 235, 0.62);
    }
    .manual-requests .filter-box .form-select:focus {
        border-color: rgba(59, 130, 246, 0.95);
        box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.26), 0 0 0 3px rgba(59, 130, 246, 0.24);
    }
    .manual-requests .filter-box .form-select option {
        background: #0b1220;
        color: #f8fbff;
    }
    .manual-requests .table thead th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--dtr-muted);
        background: var(--dtr-surface-soft);
    }
    .manual-requests .table.table-hover > tbody > tr:hover > td,
    .manual-requests .table.table-hover > tbody > tr:hover > th,
    .manual-requests .table.table-hover > tbody > tr:hover > * {
        background: var(--dtr-hover-bg) !important;
        color: var(--dtr-text) !important;
    }
    .manual-requests .table.table-hover > tbody > tr:hover .times-list,
    .manual-requests .table.table-hover > tbody > tr:hover .small,
    .manual-requests .table.table-hover > tbody > tr:hover .text-muted {
        color: var(--dtr-muted) !important;
    }
    .manual-requests .table.table-hover > tbody > tr:hover .fw-semibold,
    .manual-requests .table.table-hover > tbody > tr:hover strong {
        color: var(--dtr-text) !important;
    }
    html[data-theme="dark"] .manual-requests .table.table-hover > tbody > tr:hover > td,
    html[data-theme="dark"] .manual-requests .table.table-hover > tbody > tr:hover > th,
    html[data-theme="dark"] .manual-requests .table.table-hover > tbody > tr:hover > * {
        background: #1f2937 !important;
        color: #e2e8f0 !important;
    }
    html[data-theme="dark"] .manual-requests .table.table-hover > tbody > tr:hover .times-list,
    html[data-theme="dark"] .manual-requests .table.table-hover > tbody > tr:hover .small,
    html[data-theme="dark"] .manual-requests .table.table-hover > tbody > tr:hover .text-muted {
        color: #cbd5e1 !important;
    }
    .manual-requests .table td, .manual-requests .table th { vertical-align: middle; }
    .manual-requests .reason-cell {
        max-width: 300px;
        white-space: normal;
        line-height: 1.35;
        color: var(--dtr-text);
    }
    .manual-requests .times-list { font-size: 0.82rem; line-height: 1.35; margin: 0; color: var(--dtr-muted); }
    .manual-requests .times-list strong { color: var(--dtr-text); }
    .manual-requests .review-note { min-width: 210px; }
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
    .manual-requests .empty-state { text-align: center; padding: 2.2rem 1rem; color: var(--dtr-muted); }
    .manual-requests .empty-state i { font-size: 2rem; display: block; margin-bottom: 0.6rem; opacity: 0.6; }
</style>
@endpush

@section('content')
<div class="manual-requests">
    <h1 class="page-title">Manual Attendance Requests</h1>
    <p class="page-sub">Review student requests for power interruption or exceptional logbook-based attendance.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="GET" class="filter-row">
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
                <form action="{{ route('coordinator.manual.requests') }}" method="GET" class="search-form" role="search">
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
                <form method="POST" action="{{ route('coordinator.manual.requests.bulk.review') }}" id="bulkReviewForm">
                    @csrf
                    <input type="hidden" name="decision" id="bulkDecisionInput">
                    <div id="bulkRequestIds"></div>
                    <div class="bulk-panel">
                        <p class="bulk-help">Select pending rows, then bulk approve or reject. Use one coordinator/admin note for all selected items.</p>
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
                                        @if($row->status === \App\Models\ManualAttendanceRequest::STATUS_PENDING)
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($row->status === \App\Models\ManualAttendanceRequest::STATUS_APPROVED)
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                        @if($row->reviewed_at)
                                            <div class="small text-muted mt-1">Reviewed {{ $row->reviewed_at->format('M d, g:i A') }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->status === \App\Models\ManualAttendanceRequest::STATUS_PENDING)
                                            <form method="POST" action="{{ route('coordinator.manual.requests.review', $row) }}" class="review-note">
                                                @csrf
                                                <textarea name="coordinator_note" class="form-control form-control-sm mb-2" rows="2" placeholder="Coordinator/Admin note (optional)"></textarea>
                                                <div class="d-flex gap-2">
                                                    <button type="submit" name="decision" value="approve" class="btn btn-sm btn-success" data-norsu-confirm="Approve this request and post attendance for this date?">
                                                        <i class="bi bi-check-circle"></i> Approve
                                                    </button>
                                                    <button type="submit" name="decision" value="reject" class="btn btn-sm btn-danger" data-norsu-confirm="Reject this request? A note is recommended." data-norsu-variant="danger">
                                                        <i class="bi bi-x-circle"></i> Reject
                                                    </button>
                                                </div>
                                            </form>
                                        @else
                                            <span class="review-note-label">Coordinator/Admin Note</span>
                                            <div class="small text-muted mb-1">
                                                {{ $row->reviewer?->name ? 'By '.$row->reviewer->name : 'Reviewed by admin' }}
                                            </div>
                                            <div class="review-note-text">{{ $row->coordinator_note ?: 'No note provided.' }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $requests->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    @if(!empty($search))
                        <p class="fw-semibold mb-1">No manual requests match "{{ e($search) }}".</p>
                        <p class="mb-0 small">Try wildcard search such as <code>John*</code> or <code>*IT*</code>.</p>
                    @else
                        <p class="fw-semibold mb-1">No manual requests found.</p>
                        <p class="mb-0 small">When students submit requests for exceptional attendance days, they will appear here.</p>
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
