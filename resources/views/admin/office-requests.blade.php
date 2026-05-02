@extends('layouts.admin')

@section('title', 'Office Requests')

@section('content')
<h1 class="page-title">Office Assignment Requests</h1>
<p class="page-sub text-center">Review student office assign/re-assign requests with clear remarks and status tracking.</p>

<div class="card office-requests-card">
    <div class="card-body">
        <div class="toolbar mb-3">
            <div>
                <h2 class="h5 mb-1">Requests Queue</h2>
                <div class="text-muted small">Approve to apply the office immediately in coordinator filters.</div>
            </div>
            <form action="{{ route('admin.office-requests') }}" method="GET" class="office-filter-form">
                <div class="search-inner">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" name="q" class="search-input" placeholder="Search student, office, or reason" value="{{ $search ?? '' }}">
                </div>
                <select name="status" class="form-select filter-select" aria-label="Filter status">
                    <option value="all" {{ ($status ?? 'pending') === 'all' ? 'selected' : '' }}>All statuses</option>
                    <option value="pending" {{ ($status ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ ($status ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ ($status ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <button type="submit" class="btn btn-primary btn-search"><i class="bi bi-funnel me-1"></i> Apply</button>
                @if(!empty($search) || (($status ?? 'pending') !== 'pending'))
                    <a href="{{ route('admin.office-requests') }}" class="btn btn-outline-secondary btn-search">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0 office-requests-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Current Office</th>
                        <th>Requested Office</th>
                        <th>Student Remarks</th>
                        <th>Status</th>
                        <th>Admin Remarks</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $officeRequest)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $officeRequest->student?->name ?: 'Unknown student' }}</div>
                                <div class="small text-muted">{{ $officeRequest->student?->student_no ?: '-' }}</div>
                            </td>
                            <td>{{ $officeRequest->old_office ?: 'Not assigned' }}</td>
                            <td><span class="office-pill">{{ $officeRequest->requested_office }}</span></td>
                            <td class="small">{{ \Illuminate\Support\Str::limit($officeRequest->student_remarks ?: '-', 140) }}</td>
                            <td>
                                <span class="badge office-status-badge status-{{ $officeRequest->status }}">
                                    {{ ucfirst($officeRequest->status) }}
                                </span>
                            </td>
                            <td class="small">
                                @if($officeRequest->admin_remarks)
                                    {{ \Illuminate\Support\Str::limit($officeRequest->admin_remarks, 120) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($officeRequest->status === \App\Models\OfficeAssignmentRequest::STATUS_PENDING)
                                    <button type="button"
                                            class="btn btn-sm btn-primary office-review-btn"
                                            data-office-request-id="{{ $officeRequest->id }}"
                                            data-office-student="{{ $officeRequest->student?->name ?: 'Student' }}"
                                            data-office-current="{{ $officeRequest->old_office ?: 'Not assigned' }}"
                                            data-office-requested="{{ $officeRequest->requested_office }}"
                                            data-office-reason="{{ $officeRequest->student_remarks ?: '-' }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#officeRequestReviewModal">
                                        Review
                                    </button>
                                @else
                                    <span class="small text-muted">Reviewed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No office requests found for the selected filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $requests->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="officeRequestReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content office-review-modal">
            <form method="POST" id="officeReviewForm" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-building-check me-2"></i>Review office request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1"><strong id="officeReviewStudent">Student</strong></p>
                    <p class="small text-muted mb-3">Current: <span id="officeReviewCurrent">-</span> &rarr; Requested: <span id="officeReviewRequested">-</span></p>
                    <div class="review-reason-box mb-3">
                        <div class="small fw-semibold mb-1">Student reason</div>
                        <div id="officeReviewReason" class="small mb-0"></div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Admin remarks <span class="text-muted">(optional)</span></label>
                        <textarea name="admin_remarks" class="form-control" rows="3" maxlength="1000" placeholder="Optional notes for the student/request."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-danger" name="decision" value="reject">Reject</button>
                    <button type="submit" class="btn btn-primary" name="decision" value="approve">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.office-requests-card .card-body { padding: 1.2rem 1.2rem 1rem; }
.office-filter-form { display: flex; flex-wrap: wrap; gap: 0.65rem 0.75rem; align-items: flex-end; justify-content: flex-start; width: 100%; max-width: 100%; min-width: 0; }
.office-filter-form .search-inner { flex: 1 1 260px; min-width: 220px; }
.office-filter-form .filter-select { min-width: 150px; max-width: 190px; }
.office-pill {
    display: inline-flex;
    padding: 0.22rem 0.62rem;
    border-radius: 999px;
    border: 1px solid var(--dtr-border-soft);
    background: color-mix(in srgb, var(--dtr-card-bg) 80%, var(--dtr-primary) 20%);
    font-size: 0.78rem;
    font-weight: 600;
}
.office-status-badge {
    border: 1px solid transparent;
    border-radius: 999px;
    font-size: 0.68rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    font-weight: 700;
    padding: 0.34rem 0.62rem;
}
.office-status-badge.status-pending {
    background: rgba(245, 158, 11, 0.18);
    color: #92400e;
    border-color: rgba(245, 158, 11, 0.36);
}
.office-status-badge.status-approved {
    background: rgba(34, 197, 94, 0.18);
    color: #166534;
    border-color: rgba(34, 197, 94, 0.34);
}
.office-status-badge.status-rejected {
    background: rgba(239, 68, 68, 0.16);
    color: #991b1b;
    border-color: rgba(239, 68, 68, 0.35);
}
html[data-theme="dark"] .office-status-badge.status-pending { color: #fde68a; }
html[data-theme="dark"] .office-status-badge.status-approved { color: #bbf7d0; }
html[data-theme="dark"] .office-status-badge.status-rejected { color: #fecaca; }
.office-review-modal {
    border-radius: 14px;
    border: 1px solid var(--dtr-border-soft);
}
.review-reason-box {
    padding: 0.7rem 0.78rem;
    border-radius: 10px;
    border: 1px solid rgba(34, 197, 94, 0.45);
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.16), rgba(16, 185, 129, 0.08));
    color: #14532d;
}
.review-reason-box .small,
.review-reason-box .fw-semibold {
    color: inherit !important;
}
html[data-theme="dark"] .review-reason-box {
    border-color: rgba(74, 222, 128, 0.5);
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.25), rgba(16, 185, 129, 0.14));
    color: #dcfce7;
}
@media (max-width: 768px) {
    .office-filter-form { width: 100%; }
    .office-filter-form .search-inner,
    .office-filter-form .filter-select,
    .office-filter-form .btn-search {
        width: 100%;
        max-width: none;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    var officeReviewForm = document.getElementById('officeReviewForm');
    var officeReviewRouteTemplate = "{{ route('admin.office-requests.review', ['officeRequest' => '__ID__']) }}";
    if (!officeReviewForm) return;

    document.querySelectorAll('.office-review-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var requestId = btn.getAttribute('data-office-request-id') || '';
            officeReviewForm.action = officeReviewRouteTemplate.replace('__ID__', encodeURIComponent(requestId));
            var studentEl = document.getElementById('officeReviewStudent');
            var currentEl = document.getElementById('officeReviewCurrent');
            var requestedEl = document.getElementById('officeReviewRequested');
            var reasonEl = document.getElementById('officeReviewReason');
            if (studentEl) studentEl.textContent = btn.getAttribute('data-office-student') || 'Student';
            if (currentEl) currentEl.textContent = btn.getAttribute('data-office-current') || '-';
            if (requestedEl) requestedEl.textContent = btn.getAttribute('data-office-requested') || '-';
            if (reasonEl) reasonEl.textContent = btn.getAttribute('data-office-reason') || '-';
        });
    });
})();
</script>
@endpush
