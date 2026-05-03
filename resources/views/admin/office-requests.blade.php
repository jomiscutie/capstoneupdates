@extends('layouts.admin')

@section('title', 'Office Requests')

@section('content')
<div class="office-requests-page">
    <h1 class="page-title">Office Assignment Requests</h1>
    <p class="page-sub text-center">Review assign / re-assign requests; approval applies the office immediately in coordinator filters.</p>

    <div class="orc-card card">
        <div class="card-body">
            <div class="orc-toolbar mb-3">
                <div>
                    <h2 class="orc-toolbar-title mb-1">Requests queue</h2>
                    <p class="orc-toolbar-sub mb-0">Approve to apply the office immediately in coordinator filters.</p>
                </div>
                <form action="{{ route('admin.office-requests') }}" method="GET" class="orc-filter-form" role="search">
                    <div class="orc-search-wrap">
                        <i class="bi bi-search orc-search-ic" aria-hidden="true"></i>
                        <input type="text" name="q" class="orc-search-input" placeholder="Student, office, or reason" value="{{ $search ?? '' }}" aria-label="Search office requests">
                    </div>
                    <div class="orc-filter-field">
                        <label class="orc-filter-label" for="orc-status">Status</label>
                        <select id="orc-status" name="status" class="form-select form-select-sm orc-filter-select">
                            <option value="all" {{ ($status ?? 'pending') === 'all' ? 'selected' : '' }}>All</option>
                            <option value="pending" {{ ($status ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ ($status ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ ($status ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="orc-filter-actions">
                        <button type="submit" class="orc-btn-filter">Apply</button>
                        @if(!empty($search) || (($status ?? 'pending') !== 'pending'))
                            <a href="{{ route('admin.office-requests') }}" class="orc-btn-reset">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="d-none d-lg-block office-table-wrap">
                <div class="table-responsive office-requests-scroll">
                    <table class="table align-middle mb-0 office-requests-table">
                        <colgroup>
                            <col style="width: 18%">
                            <col style="width: 14%">
                            <col style="width: 16%">
                            <col style="width: auto">
                            <col style="width: 11%">
                            <col style="width: 16%">
                            <col class="orc-col-action" style="width: 5.25rem;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th scope="col">Student</th>
                                <th scope="col">Current</th>
                                <th scope="col">Requested</th>
                                <th scope="col">Student remarks</th>
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col">Admin remarks</th>
                                <th scope="col" class="text-center orc-th-review">Review</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $officeRequest)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-truncate-alt">{{ $officeRequest->student?->name ?: 'Unknown student' }}</div>
                                        <div class="small text-muted">{{ $officeRequest->student?->student_no ?: '-' }}</div>
                                    </td>
                                    <td class="small text-muted text-truncate-alt">{{ \Illuminate\Support\Str::limit($officeRequest->old_office ?: 'Not assigned', 42) }}</td>
                                    <td><span class="office-pill text-truncate-alt d-inline-flex max-w-100">{{ $officeRequest->requested_office }}</span></td>
                                    <td class="orc-cell-remark small">{{ \Illuminate\Support\Str::limit($officeRequest->student_remarks ?: '—', 180) }}</td>
                                    <td class="text-center">
                                        <span class="badge office-status-badge status-{{ $officeRequest->status }}">
                                            {{ ucfirst($officeRequest->status) }}
                                        </span>
                                    </td>
                                    <td class="small orc-admin-rm">
                                        @if($officeRequest->admin_remarks)
                                            {{ \Illuminate\Support\Str::limit($officeRequest->admin_remarks, 120) }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($officeRequest->status === \App\Models\OfficeAssignmentRequest::STATUS_PENDING)
                                            <button type="button"
                                                    class="orc-review-trigger"
                                                    data-office-request-id="{{ $officeRequest->id }}"
                                                    data-office-student="{{ $officeRequest->student?->name ?: 'Student' }}"
                                                    data-office-current="{{ $officeRequest->old_office ?: 'Not assigned' }}"
                                                    data-office-requested="{{ $officeRequest->requested_office }}"
                                                    data-office-reason="{{ $officeRequest->student_remarks ?: '-' }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#officeRequestReviewModal"
                                                    title="Review office request"
                                                    aria-label="Review office request for {{ $officeRequest->student?->name ?: 'student' }}">
                                                <i class="bi bi-clipboard-check" aria-hidden="true"></i>
                                            </button>
                                        @else
                                            <span class="orc-reviewed-dash">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">No office requests match this filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Compact cards below lg — avoid horizontal scroll --}}
            <div class="d-lg-none office-req-card-list">
                @forelse($requests as $officeRequest)
                    <article class="office-req-card">
                        <div class="orc-card-head">
                            <div>
                                <div class="fw-semibold">{{ $officeRequest->student?->name ?: 'Unknown student' }}</div>
                                <div class="small text-muted">{{ $officeRequest->student?->student_no ?: '-' }}</div>
                            </div>
                            <span class="badge office-status-badge status-{{ $officeRequest->status }}">{{ ucfirst($officeRequest->status) }}</span>
                        </div>
                        <dl class="orc-card-dl">
                            <div><dt>Current</dt><dd>{{ $officeRequest->old_office ?: 'Not assigned' }}</dd></div>
                            <div><dt>Requested</dt><dd><span class="office-pill">{{ $officeRequest->requested_office }}</span></dd></div>
                            <div class="orc-card-span"><dt>Student remarks</dt><dd class="small">{{ $officeRequest->student_remarks ?: '—' }}</dd></div>
                            <div class="orc-card-span"><dt>Admin remarks</dt><dd class="small">{{ $officeRequest->admin_remarks ?: '—' }}</dd></div>
                        </dl>
                        <div class="orc-card-foot">
                            @if($officeRequest->status === \App\Models\OfficeAssignmentRequest::STATUS_PENDING)
                                <button type="button"
                                        class="orc-review-trigger orc-review-trigger--wide"
                                        data-office-request-id="{{ $officeRequest->id }}"
                                        data-office-student="{{ $officeRequest->student?->name ?: 'Student' }}"
                                        data-office-current="{{ $officeRequest->old_office ?: 'Not assigned' }}"
                                        data-office-requested="{{ $officeRequest->requested_office }}"
                                        data-office-reason="{{ $officeRequest->student_remarks ?: '-' }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#officeRequestReviewModal"
                                        aria-label="Review office request">
                                    <i class="bi bi-clipboard-check me-2" aria-hidden="true"></i>Review
                                </button>
                            @else
                                <span class="small text-muted">Reviewed</span>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="text-muted text-center py-4 mb-0">No office requests match this filter.</p>
                @endforelse
            </div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</div>

@include('partials.office-request-review-modal')
@endsection

@push('styles')
<style>
    .office-requests-page .page-sub { max-width: 36rem; margin-left: auto; margin-right: auto; color: var(--dtr-muted); }
    .office-requests-page .orc-card.card {
        border: 1px solid var(--dtr-border-soft);
        border-radius: 16px;
        background: var(--dtr-card-bg);
        overflow: clip;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .office-requests-page .orc-card .card-body {
        padding: 1rem 1.05rem;
    }
    @media (min-width: 992px) {
        .office-requests-page .orc-card .card-body { padding: 1.15rem 1.25rem 1rem; }
    }
    .office-requests-page .orc-toolbar-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--dtr-heading);
        letter-spacing: -0.02em;
    }
    .office-requests-page .orc-toolbar-sub {
        font-size: 0.8rem;
        color: var(--dtr-muted);
        line-height: 1.35;
        max-width: 28rem;
    }
    .office-requests-page .orc-toolbar {
        display: flex;
        flex-flow: row wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 0.75rem 1rem;
    }
    .office-requests-page .orc-filter-form {
        --orc-fh: 2.125rem;
        display: grid;
        gap: 0.5rem 0.65rem;
        align-items: end;
        grid-template-columns: minmax(0, 1fr) minmax(0, 8.75rem) auto;
        width: 100%;
        max-width: 52rem;
        min-width: 0;
    }
    @media (max-width: 639.98px) {
        .office-requests-page .orc-filter-form { grid-template-columns: 1fr; }
    }
    .office-requests-page .orc-filter-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: var(--dtr-muted);
        margin-bottom: 0.25rem;
    }
    .office-requests-page .orc-search-wrap {
        position: relative;
        min-width: 0;
    }
    .office-requests-page .orc-search-ic {
        position: absolute;
        left: 0.65rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.92rem;
        color: var(--dtr-muted);
        pointer-events: none;
    }
    .office-requests-page .orc-search-input {
        width: 100%;
        min-height: var(--orc-fh);
        height: var(--orc-fh);
        padding: 0 0.65rem 0 2.25rem;
        font-size: 0.828rem;
        border-radius: 10px;
        border: 1px solid var(--dtr-input-border);
        background: var(--dtr-input-bg);
        color: var(--dtr-text);
        box-sizing: border-box;
        box-shadow: none;
    }
    .office-requests-page .orc-search-input:focus {
        outline: none;
        border-color: color-mix(in srgb, var(--dtr-primary) 48%, var(--dtr-input-border));
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--dtr-primary) 18%, transparent);
    }
    .office-requests-page .orc-filter-select {
        width: 100%;
        min-height: var(--orc-fh);
        height: var(--orc-fh);
        padding-top: 0.28rem;
        padding-bottom: 0.28rem;
        font-size: 0.815rem;
        border-radius: 10px;
        border: 1px solid var(--dtr-input-border);
        background: var(--dtr-input-bg) !important;
        color: var(--dtr-text);
        box-shadow: none !important;
    }
    .office-requests-page .orc-filter-select:focus {
        border-color: color-mix(in srgb, var(--dtr-primary) 48%, var(--dtr-input-border));
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--dtr-primary) 18%, transparent);
        outline: none;
    }
    .office-requests-page .orc-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        align-items: center;
        min-height: var(--orc-fh);
        box-sizing: border-box;
        padding-bottom: 0;
    }
    @media (max-width: 639.98px) {
        .office-requests-page .orc-filter-actions { padding-top: 0.15rem; }
    }
    .office-requests-page .orc-btn-filter {
        height: var(--orc-fh);
        padding: 0 1rem;
        font-size: 0.815rem;
        font-weight: 600;
        border-radius: 10px;
        border: 1px solid color-mix(in srgb, var(--dtr-primary) 38%, var(--dtr-border-soft));
        background: transparent;
        color: var(--dtr-primary);
        cursor: pointer;
        white-space: nowrap;
        transition: background 0.14s ease, border-color 0.14s ease, color 0.14s ease;
        box-shadow: none !important;
    }
    .office-requests-page .orc-btn-filter:hover {
        background: color-mix(in srgb, var(--dtr-primary) 10%, transparent);
        border-color: color-mix(in srgb, var(--dtr-primary) 52%, var(--dtr-border-soft));
        color: var(--dtr-heading);
    }
    .office-requests-page .orc-btn-reset {
        height: var(--orc-fh);
        display: inline-flex;
        align-items: center;
        padding: 0 0.75rem;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 10px;
        border: 1px solid color-mix(in srgb, var(--dtr-input-border) 90%, transparent);
        color: var(--dtr-muted);
        text-decoration: none;
        transition: background 0.14s ease, border-color 0.14s ease, color 0.14s ease;
    }
    .office-requests-page .orc-btn-reset:hover {
        background: var(--dtr-hover-bg);
        border-color: var(--dtr-border-soft);
        color: var(--dtr-text);
    }
    /* Table */
    .office-requests-page .office-table-wrap {
        border-radius: 12px;
        border: 1px solid var(--dtr-border-soft);
        overflow: hidden;
        background: color-mix(in srgb, var(--dtr-card-bg) 94%, transparent);
    }
    .office-requests-page .office-requests-scroll { margin: 0; }
    .office-requests-page .office-requests-table {
        table-layout: fixed;
        width: 100%;
        margin-bottom: 0 !important;
    }
    .office-requests-page .office-requests-table thead th {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: var(--dtr-muted) !important;
        border-bottom: 1px solid var(--dtr-border-soft);
        background: color-mix(in srgb, var(--dtr-surface-soft, var(--dtr-card-bg)) 88%, transparent);
        padding: 0.62rem 0.72rem;
        vertical-align: middle;
    }
    /* Keep “Review” one word — narrow col + letter-spacing was breaking the label */
    .office-requests-page .office-requests-table thead th.orc-th-review {
        white-space: nowrap;
        letter-spacing: 0.04em;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        min-width: 5rem;
    }
    .office-requests-page .office-requests-table tbody td {
        padding: 0.62rem 0.72rem;
        font-size: 0.83rem;
        border-bottom-color: var(--dtr-border-soft);
        vertical-align: middle;
    }
    .office-requests-page .office-requests-table tbody tr:hover td {
        background: var(--dtr-hover-bg) !important;
    }
    .office-requests-page .office-requests-table tbody tr:last-child td { border-bottom: none; }
    .office-requests-page .text-truncate-alt {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .office-requests-page .max-w-100 { max-width: 100%; overflow: hidden; text-overflow: ellipsis; }
    .office-requests-page .orc-cell-remark {
        color: var(--dtr-muted);
        word-wrap: break-word;
        overflow-wrap: break-word;
        line-height: 1.38;
        min-width: 0;
    }
    /* Ghost square Review (aligned with manual-request row actions) */
    .office-requests-page .orc-review-trigger {
        width: 2rem;
        height: 2rem;
        padding: 0;
        margin: 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid color-mix(in srgb, var(--dtr-primary) 42%, var(--dtr-input-border));
        background: transparent !important;
        color: color-mix(in srgb, var(--dtr-primary) 88%, transparent);
        cursor: pointer;
        transition: opacity 0.15s ease, background 0.15s ease, border-color 0.15s ease;
        box-shadow: none !important;
    }
    .office-requests-page .orc-review-trigger .bi {
        font-size: 0.92rem;
        line-height: 1;
        opacity: 1;
    }
    .office-requests-page .orc-review-trigger:hover {
        background: color-mix(in srgb, var(--dtr-primary) 9%, transparent) !important;
    }
    .office-requests-page .orc-review-trigger:active { opacity: 0.87; }
    .office-requests-page .orc-review-trigger:focus-visible {
        outline: 2px solid color-mix(in srgb, var(--dtr-primary) 40%, transparent);
        outline-offset: 2px;
    }
    .office-requests-page .orc-reviewed-dash { color: var(--dtr-muted); font-size: 0.92rem; }
    .office-requests-page .orc-review-trigger--wide {
        width: auto;
        min-height: 2.25rem;
        height: auto;
        padding: 0.42rem 0.95rem;
        font-size: 0.82rem;
        font-weight: 600;
    }
    .office-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.52rem;
        border-radius: 999px;
        border: 1px solid color-mix(in srgb, #10b981 28%, var(--dtr-border-soft));
        background: color-mix(in srgb, #10b981 8%, transparent);
        color: #065f46;
        font-size: 0.75rem;
        font-weight: 600;
        vertical-align: middle;
    }
    html[data-theme="dark"] .office-pill {
        color: #a7f3d0;
        border-color: rgba(52, 211, 153, 0.28);
        background: rgba(16, 185, 129, 0.09);
    }
    .office-status-badge {
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: 0.625rem;
        letter-spacing: 0.065em;
        text-transform: uppercase;
        font-weight: 750;
        padding: 0.32rem 0.55rem;
    }
    .office-status-badge.status-pending {
        background: color-mix(in srgb, #f59e0b 14%, transparent);
        color: #b45309;
        border-color: color-mix(in srgb, #f59e0b 35%, transparent);
    }
    .office-status-badge.status-approved {
        background: color-mix(in srgb, #22c55e 12%, transparent);
        color: #166534;
        border-color: color-mix(in srgb, #22c55e 32%, transparent);
    }
    .office-status-badge.status-rejected {
        background: color-mix(in srgb, #ef4444 11%, transparent);
        color: #991b1b;
        border-color: color-mix(in srgb, #ef4444 30%, transparent);
    }
    html[data-theme="dark"] .office-status-badge.status-pending { color: #fde68a; }
    html[data-theme="dark"] .office-status-badge.status-approved { color: #bbf7d0; }
    html[data-theme="dark"] .office-status-badge.status-rejected { color: #fecaca; }
    /* Mobile cards */
    .office-requests-page .office-req-card-list { display: flex; flex-direction: column; gap: 0.75rem; }
    .office-requests-page .office-req-card {
        border-radius: 12px;
        border: 1px solid var(--dtr-border-soft);
        padding: 0.85rem 1rem;
        background: color-mix(in srgb, var(--dtr-card-bg) 96%, transparent);
    }
    .office-requests-page .orc-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.65rem;
    }
    .office-requests-page .orc-card-dl {
        margin: 0;
        display: grid;
        gap: 0.5rem;
    }
    .office-requests-page .orc-card-dl > div {
        display: grid;
        grid-template-columns: 92px minmax(0, 1fr);
        gap: 0.4rem;
        align-items: start;
    }
    .office-requests-page .orc-card-dl .orc-card-span {
        grid-template-columns: 1fr;
        gap: 0.28rem;
    }
    .office-requests-page .orc-card-dl .orc-card-span dt { margin-bottom: 0; }
    .office-requests-page .orc-card-dl dt {
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--dtr-muted);
        margin: 0;
    }
    .office-requests-page .orc-card-dl dd { margin: 0; word-break: break-word; font-size: 0.842rem; color: var(--dtr-text); }
    .office-requests-page .orc-card-foot {
        margin-top: 0.85rem;
        padding-top: 0.72rem;
        border-top: 1px solid var(--dtr-border-soft);
        display: flex;
        justify-content: flex-end;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var officeReviewForm = document.getElementById('officeReviewForm');
    var officeReviewRouteTemplate = "{{ route('admin.office-requests.review', ['officeRequest' => '__ID__']) }}";
    if (!officeReviewForm) return;

    document.querySelectorAll('.orc-review-trigger').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var requestId = btn.getAttribute('data-office-request-id') || '';
            officeReviewForm.action = officeReviewRouteTemplate.replace('__ID__', encodeURIComponent(requestId));
            var studentEl = document.getElementById('officeReviewStudent');
            var currentEl = document.getElementById('officeReviewCurrent');
            var requestedEl = document.getElementById('officeReviewRequested');
            var reasonEl = document.getElementById('officeReviewReason');
            var remarksEl = document.getElementById('officeReviewAdminRemarks');
            if (studentEl) studentEl.textContent = btn.getAttribute('data-office-student') || 'Student';
            if (currentEl) currentEl.textContent = btn.getAttribute('data-office-current') || '-';
            if (requestedEl) requestedEl.textContent = btn.getAttribute('data-office-requested') || '-';
            if (reasonEl) reasonEl.textContent = (btn.getAttribute('data-office-reason') || '').trim() || '—';
            if (remarksEl) remarksEl.value = '';
        });
    });
})();
</script>
@endpush
