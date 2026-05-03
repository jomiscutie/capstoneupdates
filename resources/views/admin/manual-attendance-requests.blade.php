@extends('layouts.admin')

@section('title', 'Manual Attendance Requests')

@push('styles')
<style>
    .manual-requests .page-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--dtr-heading); }
    .manual-requests .page-sub { color: var(--dtr-muted); margin-bottom: 1.05rem; line-height: 1.45; max-width: 40rem; }
    .manual-requests .card {
        border: 1px solid var(--dtr-border-soft);
        border-radius: 16px;
        background: var(--dtr-card-bg);
        overflow: clip;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .manual-requests .card .card-body { padding: 1.1rem 1.2rem 1.15rem; }
    .manual-requests .mrr-controls {
        display: grid;
        grid-template-columns: minmax(0, 11rem) minmax(0, 1fr);
        gap: 0.65rem 1.1rem;
        align-items: end;
        margin-bottom: 1rem;
    }
    @media (max-width: 680px) {
        .manual-requests .mrr-controls { grid-template-columns: 1fr; }
    }
    .manual-requests .mrr-controls .filter-box { min-width: 0; margin: 0; }
    .manual-requests .search-wrap { margin-bottom: 0; width: 100%; max-width: none; }
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
    .manual-requests .mrr-controls .form-label {
        color: var(--dtr-muted);
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 0.28rem;
    }
    .manual-requests .mrr-controls .form-select {
        border-radius: 10px;
        border: 1px solid var(--dtr-input-border);
        background: var(--dtr-input-bg);
        color: var(--dtr-text);
        font-size: 0.8875rem;
        font-weight: 500;
        min-height: 40px;
        padding: 0.4rem 2rem 0.4rem 0.65rem;
        box-shadow: none !important;
    }
    .manual-requests .mrr-controls .form-select:focus {
        border-color: color-mix(in srgb, var(--dtr-primary) 48%, var(--dtr-input-border));
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--dtr-primary) 18%, transparent);
        outline: none;
    }
    .manual-requests .mrr-controls .form-select option {
        background: var(--dtr-card-bg);
        color: var(--dtr-text);
    }
    .manual-requests .mrr-table-frame {
        border-radius: 12px;
        border: 1px solid var(--dtr-border-soft);
        overflow: hidden;
        background: color-mix(in srgb, var(--dtr-card-bg) 96%, transparent);
    }
    .manual-requests .mrr-table-frame .manual-requests-scroll { margin: 0; }
    .manual-requests .mrr-select-col {
        width: 44px;
        min-width: 44px;
        max-width: 52px;
        text-align: center !important;
        vertical-align: middle !important;
        padding-left: 0.35rem !important;
        padding-right: 0.35rem !important;
    }
    .manual-requests .mrr-checkbox-holder {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 1.25rem;
    }
    /* Custom box: native checkbox size/border differs by layout/OS; matches coordinator chrome */
    .manual-requests .mrr-checkbox-holder input[type="checkbox"] {
        appearance: none;
        -webkit-appearance: none;
        margin: 0;
        cursor: pointer;
        width: 1.0625rem;
        height: 1.0625rem;
        flex-shrink: 0;
        box-sizing: border-box;
        border: 1.5px solid var(--dtr-input-border);
        border-radius: 3px;
        background-color: var(--dtr-card-bg);
        vertical-align: middle;
    }
    .manual-requests .mrr-checkbox-holder input[type="checkbox"]:checked {
        background-color: color-mix(in srgb, #2563eb 85%, #059669);
        border-color: color-mix(in srgb, #2563eb 78%, #059669);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23ffffff' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round' d='M3.8 8.2l2.9 2.9 5.6-6.3'/%3E%3C/svg%3E");
        background-size: 0.68rem auto;
        background-position: center;
        background-repeat: no-repeat;
    }
    html[data-theme="dark"] .manual-requests .mrr-checkbox-holder input[type="checkbox"]:checked {
        border-color: color-mix(in srgb, #2563eb 55%, rgba(226,232,240,0.35));
    }
    .manual-requests .mrr-checkbox-holder input[type="checkbox"]:focus-visible {
        outline: 2px solid color-mix(in srgb, #2563eb 42%, transparent);
        outline-offset: 2px;
    }
    .manual-requests .mrr-checkbox-placeholder {
        display: inline-block;
        width: 1.0625rem;
        height: 1.0625rem;
    }
    .manual-requests .mrr-table-frame .manual-requests-scroll .table-requests-compact thead th.mrr-select-col {
        font-size: 1rem !important;
        letter-spacing: 0 !important;
        text-transform: none !important;
        line-height: 1 !important;
        font-weight: 400 !important;
    }
    .manual-requests .mrr-table-frame .manual-requests-scroll .table-requests-compact thead th.mrr-select-col,
    .manual-requests .mrr-table-frame .manual-requests-scroll .table-requests-compact tbody td.mrr-select-col {
        vertical-align: middle !important;
    }
    .manual-requests .mrr-table-frame .table thead th {
        border-bottom: 1px solid var(--dtr-border-soft);
        vertical-align: middle;
    }
    .manual-requests .mrr-table-frame .table thead th:not(.mrr-select-col) {
        font-size: 0.6425rem;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: 0.065em;
        color: var(--dtr-muted) !important;
        background: color-mix(in srgb, var(--dtr-surface-soft, var(--dtr-card-bg)) 88%, transparent);
    }
    .manual-requests .mrr-table-frame .table thead th.mrr-select-col {
        color: var(--dtr-muted) !important;
        background: color-mix(in srgb, var(--dtr-surface-soft, var(--dtr-card-bg)) 88%, transparent);
    }
    .manual-requests thead th.mrr-th-review {
        white-space: nowrap;
        letter-spacing: 0.045em;
        min-width: 5.25rem;
    }
    .manual-requests .mrr-table-frame .table tbody tr:hover > td,
    .manual-requests .mrr-table-frame .table tbody tr:hover > th {
        background: var(--dtr-hover-bg) !important;
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
    .manual-requests .mrr-table-frame .table tbody tr:hover .reason-cell-inner {
        box-shadow: inset 0 0 0 1px #86efac;
    }
    html[data-theme="dark"] .manual-requests .mrr-table-frame .table tbody tr:hover .reason-cell-inner {
        box-shadow: inset 0 0 0 1px rgba(52, 211, 153, 0.42);
    }
    .manual-requests .table-requests-compact tbody td,
    .manual-requests .table-requests-compact thead th {
        vertical-align: middle !important;
    }
    .manual-requests .mrr-reason-col,
    .manual-requests td.reason-cell {
        width: 24%;
        min-width: 8.75rem;
        max-width: 18rem;
        white-space: normal;
        text-align: center;
    }
    .manual-requests .reason-cell-inner {
        display: block;
        box-sizing: border-box;
        width: fit-content;
        max-width: 100%;
        margin-inline: auto;
        padding: 0.48rem 0.75rem;
        border-radius: 10px;
        font-size: 0.8125rem;
        line-height: 1.45;
        font-weight: 500;
        color: #064e3b;
        background: #ecfdf5;
        box-shadow: inset 0 0 0 1px #6ee7b7;
        overflow-wrap: anywhere;
        word-break: break-word;
        text-align: left;
        hyphens: auto;
    }
    html[data-theme="dark"] .manual-requests .reason-cell-inner {
        color: #ecfdf5;
        background: rgba(16, 185, 129, 0.16);
        box-shadow: inset 0 0 0 1px rgba(52, 211, 153, 0.42);
    }
    .manual-requests .mrr-date-col { width: 12%; min-width: 7.5rem; }
    .manual-requests .mrr-student-col { width: 22%; min-width: 10rem; }
    .manual-requests .mrr-times-col { width: 17%; min-width: 9.25rem; }
    .manual-requests .mrr-times-inner {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.22rem;
    }
    .manual-requests .times-list { font-size: 0.83rem; line-height: 1.35; margin: 0; color: var(--dtr-muted); }
    .manual-requests .times-list strong { color: var(--dtr-heading); }
    .manual-requests .manual-request-actions {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: center;
        gap: 0.42rem;
        width: 100%;
    }
    /* Ghost square icon buttons: clear fill, icon color = border (match invalidations / archived) */
    .manual-requests .mrr-table-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        box-sizing: border-box;
        width: 2.25rem;
        height: 2.25rem;
        min-width: 2.25rem;
        min-height: 2.25rem;
        margin: 0;
        padding: 0 !important;
        font: inherit;
        font-weight: 600;
        line-height: 1;
        border-radius: 8px;
        border-width: 1px;
        border-style: solid;
        background: transparent !important;
        cursor: pointer;
        box-shadow: none !important;
        transition: opacity 0.15s ease, background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        -webkit-appearance: none;
        appearance: none;
        vertical-align: middle;
    }
    .manual-requests .mrr-table-btn .bi {
        font-size: 1rem;
        opacity: 1;
        line-height: 1;
        font-weight: 400;
    }
    .manual-requests .mrr-table-btn--approve {
        color: #059669;
        border-color: color-mix(in srgb, #059669 52%, var(--dtr-input-border));
    }
    .manual-requests .mrr-table-btn--approve:hover,
    .manual-requests .mrr-table-btn--approve:focus {
        background: color-mix(in srgb, #059669 9%, transparent) !important;
        border-color: #059669;
        color: #047857;
    }
    .manual-requests .mrr-table-btn--approve:active {
        opacity: 0.88;
    }
    .manual-requests .mrr-table-btn--reject {
        color: #e11d48;
        border-color: color-mix(in srgb, #f43f5e 48%, var(--dtr-input-border));
    }
    .manual-requests .mrr-table-btn--reject:hover,
    .manual-requests .mrr-table-btn--reject:focus {
        background: color-mix(in srgb, #f43f5e 10%, transparent) !important;
        border-color: #f43f5e;
        color: #be123c;
    }
    .manual-requests .mrr-table-btn--reject:active {
        opacity: 0.88;
    }
    .manual-requests .mrr-table-btn:focus-visible {
        outline: 2px solid color-mix(in srgb, var(--dtr-primary) 45%, transparent);
        outline-offset: 2px;
    }
    html[data-theme="dark"] .manual-requests .mrr-table-btn--approve {
        color: #6ee7b7;
        border-color: rgba(52, 211, 153, 0.55);
    }
    html[data-theme="dark"] .manual-requests .mrr-table-btn--approve:hover,
    html[data-theme="dark"] .manual-requests .mrr-table-btn--approve:focus {
        background: rgba(52, 211, 153, 0.1) !important;
        border-color: rgba(52, 211, 153, 0.75);
        color: #a7f3d0;
    }
    html[data-theme="dark"] .manual-requests .mrr-table-btn--reject {
        color: #fda4af;
        border-color: rgba(251, 113, 133, 0.55);
    }
    html[data-theme="dark"] .manual-requests .mrr-table-btn--reject:hover,
    html[data-theme="dark"] .manual-requests .mrr-table-btn--reject:focus {
        background: rgba(251, 113, 133, 0.1) !important;
        border-color: rgba(252, 165, 165, 0.65);
        color: #fecaca;
    }
    .manual-requests .manual-req-act-col {
        width: 6.25rem;
        max-width: 18%;
        white-space: normal;
        vertical-align: middle;
    }
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
        padding: 0.75rem 0.85rem;
        border: 1px solid var(--dtr-border-soft);
        border-radius: 14px;
        background: color-mix(in srgb, var(--dtr-surface-soft) 94%, transparent);
    }
    .manual-requests .bulk-panel .bulk-help {
        font-size: 0.8rem;
        color: var(--dtr-muted);
        margin: 0 0 0.52rem;
        line-height: 1.42;
    }
    .manual-requests .bulk-panel textarea.form-control {
        background: var(--dtr-input-bg);
        border: 1px solid var(--dtr-input-border);
        color: var(--dtr-text);
        border-radius: 10px;
        font-size: 0.875rem;
        line-height: 1.4;
        resize: vertical;
        min-height: 4.25rem;
        margin-bottom: 0.62rem !important;
    }
    .manual-requests .bulk-panel textarea.form-control::placeholder {
        color: var(--dtr-muted);
        opacity: 0.82;
    }
    .manual-requests .bulk-panel textarea.form-control:focus {
        border-color: color-mix(in srgb, var(--dtr-primary) 52%, var(--dtr-input-border));
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--dtr-primary) 16%, transparent);
        outline: none;
        background: var(--dtr-input-bg);
    }
    .manual-requests .bulk-actions-inline {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.55rem;
    }
    .manual-requests .bulk-icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 0;
        width: 2.4rem;
        height: 2.4rem;
        border-radius: 8px;
        border-width: 1px;
        border-style: solid;
        cursor: pointer;
        font: inherit;
        line-height: 1;
        box-sizing: border-box;
        box-shadow: none !important;
        background: transparent !important;
        transition: opacity 0.15s ease, background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        -webkit-appearance: none;
        appearance: none;
    }
    .manual-requests .bulk-icon-btn .bi {
        font-size: 1.05rem;
        line-height: 1;
        opacity: 1;
    }
    .manual-requests .bulk-icon-btn--approve {
        color: #059669;
        border-color: #059669;
    }
    .manual-requests .bulk-icon-btn--approve:hover {
        background: color-mix(in srgb, #059669 8%, transparent) !important;
    }
    .manual-requests .bulk-icon-btn--approve:active {
        opacity: 0.88;
    }
    .manual-requests .bulk-icon-btn--reject {
        color: #e11d48;
        border-color: #e11d48;
    }
    .manual-requests .bulk-icon-btn--reject:hover {
        background: color-mix(in srgb, #e11d48 8%, transparent) !important;
    }
    .manual-requests .bulk-icon-btn--reject:active {
        opacity: 0.88;
    }
    .manual-requests .bulk-icon-btn:focus-visible {
        outline: 2px solid color-mix(in srgb, var(--dtr-primary) 45%, transparent);
        outline-offset: 3px;
    }
    html[data-theme="dark"] .manual-requests .bulk-icon-btn--approve {
        color: #4ade80;
        border-color: #4ade80;
    }
    html[data-theme="dark"] .manual-requests .bulk-icon-btn--approve:hover {
        background: rgba(74, 222, 128, 0.08) !important;
    }
    html[data-theme="dark"] .manual-requests .bulk-icon-btn--reject {
        color: #fb7185;
        border-color: #fb7185;
    }
    html[data-theme="dark"] .manual-requests .bulk-icon-btn--reject:hover {
        background: rgba(251, 113, 133, 0.08) !important;
    }
    .manual-requests .mrr-status-col {
        width: 8.5rem;
        max-width: 12rem;
        text-align: center;
        vertical-align: middle !important;
    }
    .manual-requests .mrr-status-stack {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.32rem;
        max-width: 100%;
    }
    .manual-requests .mrr-reviewed-sub {
        margin: 0 !important;
        line-height: 1.35;
        text-align: center;
        max-width: 11rem;
        overflow-wrap: anywhere;
    }
    .manual-requests .status-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 0.34rem 0.72rem;
        font-size: 0.625rem;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        line-height: 1.15;
        max-width: 100%;
        box-sizing: border-box;
        text-align: center;
    }
    .manual-requests .status-chip.status-pending {
        background: color-mix(in srgb, #fbbf24 16%, transparent);
        color: #b45309;
        border: 1px solid color-mix(in srgb, #f59e0b 38%, transparent);
    }
    .manual-requests .status-chip.status-approved {
        background: color-mix(in srgb, #34d396 14%, transparent);
        color: #047857;
        border: 1px solid color-mix(in srgb, #10b981 36%, transparent);
    }
    .manual-requests .status-chip.status-rejected {
        background: color-mix(in srgb, #f87171 12%, transparent);
        color: #b91c1c;
        border: 1px solid color-mix(in srgb, #ef4444 34%, transparent);
    }
    html[data-theme="dark"] .manual-requests .status-chip.status-pending { color: #fde68a; }
    html[data-theme="dark"] .manual-requests .status-chip.status-approved { color: #a7f3d0; }
    html[data-theme="dark"] .manual-requests .status-chip.status-rejected { color: #fecaca; }
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
    .manual-requests .manual-requests-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .manual-requests .manual-requests-scroll .table-requests-compact {
        width: 100%;
        min-width: 0;
        max-width: 100%;
        margin-bottom: 0;
        table-layout: fixed;
    }
    .manual-requests .manual-requests-scroll .table-requests-compact th,
    .manual-requests .manual-requests-scroll .table-requests-compact td {
        padding: 0.62rem 0.5rem;
        font-size: 0.8125rem;
    }
    .manual-requests .mrr-table-frame .manual-requests-scroll .table-requests-compact thead th:not(.mrr-select-col) {
        font-size: 0.6425rem !important;
        font-weight: 750;
        letter-spacing: 0.065em;
        text-transform: uppercase;
        color: var(--dtr-muted) !important;
        background: color-mix(in srgb, var(--dtr-surface-soft, var(--dtr-card-bg)) 88%, transparent);
    }
    .manual-requests .manual-requests-scroll .reason-cell {
        max-width: 18rem;
    }
    .manual-requests .manual-requests-scroll .review-box {
        min-width: 0;
    }
</style>
@endpush

@section('content')
<div class="manual-requests">
    <h1 class="page-title">Manual Attendance Requests</h1>
    <p class="page-sub">Admin oversight for power interruption/logbook-based attendance requests.</p>

    <div class="card">
        <div class="card-body">
            <div class="mrr-controls">
            <form method="GET" class="filter-box mb-0">
                    <label for="status" class="form-label">Filter status</label>
                    <select class="form-select form-select-sm" id="status" name="status" onchange="this.form.submit()">
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                    </select>
            </form>
            <div class="search-wrap">
                <form action="{{ route('admin.manual.requests') }}" method="GET" class="search-form" role="search">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <label for="manualSearchInput" class="visually-hidden">Search manual requests</label>
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
            </div>

            @if($requests->count() > 0)
                <form method="POST" action="{{ route('admin.manual.requests.bulk.review') }}" id="bulkReviewForm">
                    @csrf
                    <input type="hidden" name="decision" id="bulkDecisionInput">
                    <div id="bulkRequestIds"></div>
                    <div class="bulk-panel">
                        <p class="bulk-help">Select pending rows, then bulk approve or reject. One coordinator/admin note is applied to all selected rows.</p>
                        <textarea name="coordinator_note" id="bulkCoordinatorNote" class="form-control form-control-sm mb-2" rows="2" placeholder="Coordinator/Admin note (optional)"></textarea>
                        <div class="bulk-actions-inline">
                            <button type="button"
                                    class="bulk-icon-btn bulk-icon-btn--approve"
                                    onclick="submitBulkReview('approve')"
                                    title="Bulk approve selected pending requests"
                                    aria-label="Bulk approve selected pending requests">
                                <i class="bi bi-check2" aria-hidden="true"></i>
                            </button>
                            <button type="button"
                                    class="bulk-icon-btn bulk-icon-btn--reject"
                                    onclick="submitBulkReview('reject')"
                                    title="Bulk reject selected pending requests"
                                    aria-label="Bulk reject selected pending requests">
                                <i class="bi bi-x-lg" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </form>
                <div class="mrr-table-frame">
                <div class="table-responsive manual-requests-scroll">
                    <table class="table table-hover align-middle mb-0 table-requests-compact">
                        <thead>
                            <tr>
                                <th scope="col" class="mrr-select-col">
                                    <span class="mrr-checkbox-holder"><input type="checkbox" id="selectAllRows" aria-label="Select all pending rows"></span>
                                </th>
                                <th class="mrr-date-col">Date</th>
                                <th class="mrr-student-col">Student</th>
                                <th class="mrr-times-col">Requested Times</th>
                                <th class="mrr-reason-col">Reason</th>
                                <th class="mrr-status-col">Status</th>
                                <th class="manual-req-act-col text-center mrr-th-review"><span title="Approve or reject; opens in modal">Review</span></th>
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
                                    <td class="mrr-select-col">
                                        @if($row->status === \App\Models\ManualAttendanceRequest::STATUS_PENDING)
                                            <span class="mrr-checkbox-holder"><input type="checkbox" class="row-select" name="request_ids[]" value="{{ $row->id }}" aria-label="Select request {{ $row->id }}"></span>
                                        @else
                                            <span class="mrr-checkbox-placeholder" aria-hidden="true"></span>
                                        @endif
                                    </td>
                                    <td class="mrr-date-col">
                                        <div class="fw-semibold">{{ $row->attendance_date?->format('M d, Y') }}</div>
                                        <div class="small text-muted">Filed {{ $row->created_at?->format('M d, g:i A') }}</div>
                                    </td>
                                    <td class="mrr-student-col">
                                        <div class="fw-semibold">{{ $row->student?->name ?? 'Unknown student' }}</div>
                                        <div class="small text-muted">{{ $row->student?->student_no }}{{ !empty($row->student?->course) ? ' - '.$row->student?->course : '' }}</div>
                                    </td>
                                    <td class="mrr-times-col">
                                        <div class="mrr-times-inner">
                                            <p class="times-list"><strong>AM In:</strong> {{ $timeIn }}</p>
                                            <p class="times-list"><strong>Lunch Out:</strong> {{ $lunchOut }}</p>
                                            <p class="times-list"><strong>PM In:</strong> {{ $afternoonIn }}</p>
                                            <p class="times-list"><strong>Out:</strong> {{ $timeOut }}</p>
                                        </div>
                                    </td>
                                    <td class="reason-cell"><span class="reason-cell-inner">{{ $row->reason }}</span></td>
                                    <td class="mrr-status-col">
                                        <div class="mrr-status-stack">
                                            <span class="status-chip status-{{ $row->status }}">{{ ucfirst($row->status) }}</span>
                                            @if($row->reviewed_at)
                                                <div class="small text-muted mrr-reviewed-sub">Reviewed {{ $row->reviewed_at->format('M d, g:i A') }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="manual-req-act-col">
                                        @if($row->status === \App\Models\ManualAttendanceRequest::STATUS_PENDING)
                                            <div class="manual-request-actions">
                                                <button type="button"
                                                        class="mrr-table-btn mrr-table-btn--approve js-open-manual-review-modal"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#manualRequestReviewModal"
                                                        data-action="{{ route('admin.manual.requests.review', $row) }}"
                                                        data-decision="approve"
                                                        data-modal-title="Approve manual request"
                                                        data-target-summary="{{ e(($row->student?->name ?? 'Unknown student').' — '.($row->attendance_date?->format('M j, Y') ?? '')) }}"
                                                        data-confirm-message="Approve this request and post attendance record?"
                                                        data-confirm-variant="warning"
                                                        title="Approve"
                                                        aria-label="Approve manual request for {{ e($row->student?->name ?? 'student') }}">
                                                    <i class="bi bi-check2" aria-hidden="true"></i>
                                                </button>
                                                <button type="button"
                                                        class="mrr-table-btn mrr-table-btn--reject js-open-manual-review-modal"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#manualRequestReviewModal"
                                                        data-action="{{ route('admin.manual.requests.review', $row) }}"
                                                        data-decision="reject"
                                                        data-modal-title="Reject manual request"
                                                        data-target-summary="{{ e(($row->student?->name ?? 'Unknown student').' — '.($row->attendance_date?->format('M j, Y') ?? '')) }}"
                                                        data-confirm-message="Reject this request?"
                                                        data-confirm-variant="danger"
                                                        title="Reject"
                                                        aria-label="Reject manual request for {{ e($row->student?->name ?? 'student') }}">
                                                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                                                </button>
                                            </div>
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
@include('partials.manual-request-review-modal')
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
