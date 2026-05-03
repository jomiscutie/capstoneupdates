@extends('layouts.student')

@section('title', 'Attendance Logs and Progress')

@push('styles')
<style>
    .progress-card {
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        border: 1px solid var(--dtr-border-soft);
        box-shadow: var(--dtr-shadow-soft);
        background: var(--dtr-card-bg);
        max-width: 280px;
        margin-bottom: 1.5rem;
    }
    .progress-card .card-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; }
    .progress-card .card-header i { color: var(--dtr-primary, #4f46e5); }
    .progress-card .card-header h4 { font-size: 1rem; font-weight: 600; color: var(--dtr-text); margin: 0; }
    .progress-stats { display: grid; grid-template-columns: auto 1fr; gap: 0.25rem 0.75rem; align-items: baseline; }
    .progress-stat-label { font-size: 0.8125rem; color: var(--dtr-muted); font-weight: 500; }
    .progress-stat-value { font-size: 1rem; font-weight: 600; color: var(--dtr-text); font-variant-numeric: tabular-nums; }
    .progress-bar-wrap { display: flex; flex-direction: column; gap: 0.35rem; margin-top: 0.75rem; }
    .progress-bar-track { width: 100%; max-width: 160px; height: 14px; background: var(--dtr-surface-soft); border: 1.5px solid var(--dtr-input-border); border-radius: 6px; overflow: hidden; box-sizing: border-box; }
    .progress-bar-fill { height: 100%; background: linear-gradient(90deg, #16a34a, #22c55e); border-radius: 5px; transition: width 0.3s ease; }
    .progress-bar-label { font-size: 0.8125rem; font-weight: 600; color: var(--dtr-muted); }
    .dtr-attendance-history {
        --attendance-text: var(--dtr-text);
        --attendance-muted: var(--dtr-muted);
        --attendance-border: var(--dtr-border-soft);
        --attendance-surface: var(--dtr-card-bg);
        --attendance-heading: var(--dtr-text);
        --attendance-surface-soft: var(--dtr-surface-soft);
        --attendance-hover: var(--dtr-hover-bg);
    }
    .dtr-attendance-history .page-title { text-align: center; }
    .dtr-attendance-history .page-sub { text-align: center; max-width: 720px; margin-left: auto; margin-right: auto; }
    .dtr-attendance-history .card {
        border-radius: 12px;
        padding: 1.5rem 1.75rem;
        border: 1px solid var(--attendance-border);
        box-shadow: var(--dtr-shadow-soft);
        background: var(--attendance-surface);
    }
    .dtr-attendance-history .card h4 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--attendance-heading);
        margin-bottom: 1rem;
        letter-spacing: -0.01em;
    }
    .dtr-attendance-history .filter-tabs {
        display: flex;
        gap: 0;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid var(--attendance-border);
        background: var(--attendance-surface-soft);
    }
    .dtr-attendance-history .filter-tabs label {
        margin: 0;
        cursor: pointer;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--attendance-muted);
        transition: color 0.2s, background 0.2s;
        letter-spacing: 0.02em;
    }
    .dtr-attendance-history .filter-tabs input { position: absolute; opacity: 0; }
    .dtr-attendance-history .filter-tabs input:checked + label {
        background: var(--attendance-surface);
        color: var(--dtr-primary);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
    }
    .dtr-attendance-history .filter-panel { display: none; }
    .dtr-attendance-history .filter-panel.active { display: block; }
    .dtr-attendance-history .filter-form .form-control {
        border: 1px solid var(--attendance-border);
        border-radius: 10px;
        font-size: 0.9375rem;
        padding: 0.5rem 0.75rem;
        background: var(--dtr-input-bg);
        color: var(--attendance-text);
    }
    .dtr-attendance-history .filter-form .form-control:focus {
        border-color: var(--dtr-primary);
        box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.2);
    }
    /* Download PDF — high visibility, theme-adaptive (not default outline) */
    .recent-logs-download-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.55rem 1.2rem;
        min-height: 2.5rem;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-decoration: none;
        white-space: nowrap;
        border: 1px solid color-mix(in srgb, var(--dtr-primary) 42%, var(--dtr-border-soft) 58%);
        color: color-mix(in srgb, var(--dtr-heading) 88%, var(--dtr-primary) 12%);
        background:
            linear-gradient(180deg,
                color-mix(in srgb, var(--dtr-primary) 18%, var(--dtr-card-bg) 82%) 0%,
                color-mix(in srgb, var(--dtr-primary) 10%, var(--dtr-card-bg) 90%) 100%);
        box-shadow:
            0 1px 2px color-mix(in srgb, #0f172a 8%, transparent),
            0 0 0 1px color-mix(in srgb, #fff 55%, transparent) inset;
        transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease, transform 0.12s ease;
    }
    .recent-logs-download-btn i {
        font-size: 1.05rem;
        opacity: 0.92;
        flex-shrink: 0;
    }
    .recent-logs-download-btn:hover {
        color: var(--dtr-heading);
        border-color: color-mix(in srgb, var(--dtr-primary) 58%, var(--dtr-border-soft) 42%);
        background:
            linear-gradient(180deg,
                color-mix(in srgb, var(--dtr-primary) 26%, var(--dtr-card-bg) 74%) 0%,
                color-mix(in srgb, var(--dtr-primary) 14%, var(--dtr-card-bg) 86%) 100%);
        box-shadow:
            0 2px 8px color-mix(in srgb, var(--dtr-primary) 22%, transparent),
            0 0 0 1px color-mix(in srgb, #fff 40%, transparent) inset;
        transform: translateY(-1px);
    }
    .recent-logs-download-btn:focus-visible {
        outline: none;
        box-shadow:
            0 0 0 3px color-mix(in srgb, var(--dtr-primary) 35%, transparent),
            0 1px 2px color-mix(in srgb, #0f172a 10%, transparent);
    }
    html[data-theme="dark"] .recent-logs-download-btn {
        color: #ecfeff;
        border-color: color-mix(in srgb, var(--dtr-primary) 55%, #1e293b 45%);
        background:
            linear-gradient(180deg,
                color-mix(in srgb, var(--dtr-primary) 32%, #0f172a 68%) 0%,
                color-mix(in srgb, var(--dtr-primary) 18%, #0c1222 82%) 100%);
        box-shadow:
            0 2px 12px color-mix(in srgb, #000 45%, transparent),
            0 0 0 1px color-mix(in srgb, #fff 8%, transparent) inset;
    }
    html[data-theme="dark"] .recent-logs-download-btn:hover {
        color: #f0fdfa;
        border-color: color-mix(in srgb, var(--dtr-primary) 70%, #1e293b 30%);
        background:
            linear-gradient(180deg,
                color-mix(in srgb, var(--dtr-primary) 42%, #0f172a 58%) 0%,
                color-mix(in srgb, var(--dtr-primary) 24%, #0a0f18 76%) 100%);
        box-shadow:
            0 4px 16px color-mix(in srgb, #000 50%, transparent),
            0 0 20px color-mix(in srgb, var(--dtr-primary) 18%, transparent);
    }
    html[data-theme="dark"] .recent-logs-download-btn:focus-visible {
        box-shadow:
            0 0 0 3px color-mix(in srgb, var(--dtr-primary) 45%, transparent),
            0 2px 12px color-mix(in srgb, #000 45%, transparent);
    }

    /* Verification snapshot buttons — clean, modern, theme-aware */
    .dtr-attendance-history .btn-snapshot {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.75rem;
        border-radius: 8px;
        font-size: 0.8125rem;
        font-weight: 600;
        letter-spacing: 0.01em;
        text-decoration: none;
        white-space: nowrap;
        border: 1px solid color-mix(in srgb, var(--dtr-primary) 35%, var(--dtr-border-soft) 65%);
        color: color-mix(in srgb, var(--dtr-primary) 85%, var(--dtr-text) 15%);
        background:
            linear-gradient(180deg,
                color-mix(in srgb, var(--dtr-primary) 12%, var(--dtr-card-bg) 88%) 0%,
                color-mix(in srgb, var(--dtr-primary) 8%, var(--dtr-card-bg) 92%) 100%);
        box-shadow:
            0 1px 2px color-mix(in srgb, #0f172a 6%, transparent),
            0 0 0 1px color-mix(in srgb, #fff 50%, transparent) inset;
        transition: all 0.15s ease;
    }
    .dtr-attendance-history .btn-snapshot i {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    .dtr-attendance-history .btn-snapshot:hover {
        color: color-mix(in srgb, var(--dtr-primary) 95%, var(--dtr-heading) 5%);
        border-color: color-mix(in srgb, var(--dtr-primary) 55%, var(--dtr-border-soft) 45%);
        background:
            linear-gradient(180deg,
                color-mix(in srgb, var(--dtr-primary) 22%, var(--dtr-card-bg) 78%) 0%,
                color-mix(in srgb, var(--dtr-primary) 12%, var(--dtr-card-bg) 88%) 100%);
        box-shadow:
            0 2px 6px color-mix(in srgb, var(--dtr-primary) 18%, transparent),
            0 0 0 1px color-mix(in srgb, #fff 35%, transparent) inset;
        transform: translateY(-1px);
    }
    .dtr-attendance-history .btn-snapshot:active {
        transform: translateY(0);
    }
    html[data-theme="dark"] .dtr-attendance-history .btn-snapshot {
        color: #7dd3fc;
        border-color: color-mix(in srgb, var(--dtr-primary) 45%, #334155 55%);
        background:
            linear-gradient(180deg,
                color-mix(in srgb, var(--dtr-primary) 28%, #0f172a 72%) 0%,
                color-mix(in srgb, var(--dtr-primary) 16%, #0c1222 84%) 100%);
        box-shadow:
            0 2px 8px color-mix(in srgb, #000 40%, transparent),
            0 0 0 1px color-mix(in srgb, #fff 6%, transparent) inset;
    }
    html[data-theme="dark"] .dtr-attendance-history .btn-snapshot:hover {
        color: #bae6fd;
        border-color: color-mix(in srgb, var(--dtr-primary) 65%, #334155 35%);
        background:
            linear-gradient(180deg,
                color-mix(in srgb, var(--dtr-primary) 38%, #0f172a 62%) 0%,
                color-mix(in srgb, var(--dtr-primary) 22%, #0c1222 78%) 100%);
        box-shadow:
            0 4px 12px color-mix(in srgb, var(--dtr-primary) 15%, transparent),
            0 0 0 1px color-mix(in srgb, #fff 8%, transparent) inset;
    }

    .dtr-attendance-history .table-responsive {
        border-radius: 10px;
        overflow-x: auto;
        border: 1px solid var(--attendance-border);
        background: var(--attendance-surface);
    }
    /* Fit card width first; horizontal scroll only on very small viewports */
    .dtr-attendance-history .table-responsive .table { width: 100%; min-width: 0; max-width: 100%; margin-bottom: 0; table-layout: fixed; }
    .dtr-attendance-history .table-dtr-layout col.col-log-date { width: 9%; min-width: 4.25rem; }
    .dtr-attendance-history .table-dtr-layout col.col-log-time { width: 13%; min-width: 0; }
    .dtr-attendance-history .table-dtr-layout col.col-log-hours { width: 14%; min-width: 0; }
    .dtr-attendance-history .table-dtr-layout col.col-log-snaps { width: auto; min-width: 0; }
    .dtr-attendance-history .table-dtr-layout thead th {
        text-align: center;
        vertical-align: middle;
    }
    .dtr-attendance-history .table-dtr-layout thead tr.subhead th {
        font-size: 0.625rem;
        padding-top: 0.35rem;
        padding-bottom: 0.35rem;
    }
    .dtr-attendance-history .table thead th {
        background: var(--attendance-surface-soft);
        color: var(--attendance-muted);
        font-weight: 600;
        font-size: 0.625rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 0.5rem 0.35rem;
        border-bottom: 1px solid var(--attendance-border);
        vertical-align: bottom;
        line-height: 1.2;
    }
    .dtr-attendance-history .table tbody td {
        padding: 0.5rem 0.35rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--attendance-border);
        font-size: 0.8125rem;
        color: var(--attendance-text);
    }
    .dtr-attendance-history .table tbody tr:last-child td { border-bottom: none; }
    .dtr-attendance-history .table tbody tr:hover { background: var(--attendance-hover); }
    .dtr-attendance-history .table-dtr-layout tbody td.dtr-time {
        text-align: center;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    .dtr-attendance-history .empty-state {
        text-align: center;
        padding: 2.5rem 1.5rem;
        color: var(--attendance-muted);
    }
    .dtr-attendance-history .empty-state i { font-size: 2.5rem; color: var(--attendance-muted); opacity: 0.45; margin-bottom: 0.75rem; display: block; }
    .term-status-card {
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        border: 1px solid var(--dtr-border-soft);
        box-shadow: var(--dtr-shadow-soft);
        background:
            linear-gradient(135deg, rgba(20, 184, 166, 0.08), transparent 48%),
            var(--dtr-card-bg);
        margin-bottom: 1.5rem;
    }
    .term-status-head {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    .term-status-head h4 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--dtr-text);
        margin: 0;
    }
    .term-status-head p {
        margin: 0.2rem 0 0;
        color: var(--dtr-muted);
        font-size: 0.875rem;
    }
    .term-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.38rem 0.75rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .term-status-badge.status-active {
        background: rgba(34, 197, 94, 0.14);
        color: #15803d;
        border-color: rgba(34, 197, 94, 0.25);
    }
    .term-status-badge.status-completed {
        background: rgba(37, 99, 235, 0.14);
        color: #1d4ed8;
        border-color: rgba(37, 99, 235, 0.25);
    }
    .term-status-badge.status-pending {
        background: rgba(245, 158, 11, 0.16);
        color: #b45309;
        border-color: rgba(245, 158, 11, 0.25);
    }
    html[data-theme="dark"] .term-status-badge.status-active {
        color: #86efac;
    }
    html[data-theme="dark"] .term-status-badge.status-completed {
        color: #93c5fd;
    }
    html[data-theme="dark"] .term-status-badge.status-pending {
        color: #fcd34d;
    }
    .term-status-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 0.75rem;
    }
    .term-status-item {
        padding: 0.85rem 0.95rem;
        border-radius: 10px;
        background: var(--dtr-surface-soft);
        border: 1px solid var(--dtr-border-soft);
    }
    .term-status-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--dtr-muted);
        margin-bottom: 0.3rem;
    }
    .term-status-value {
        display: block;
        font-size: 0.96rem;
        font-weight: 700;
        color: var(--dtr-text);
    }
    .term-status-note {
        margin: 0.95rem 0 0;
        color: var(--dtr-muted);
        font-size: 0.875rem;
    }
    .term-history-strip {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.9rem;
    }
    .term-history-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.34rem 0.65rem;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.12);
        border: 1px solid rgba(148, 163, 184, 0.18);
        color: var(--dtr-muted);
        font-size: 0.76rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="dtr-attendance-history">
    <h1 class="page-title">Attendance Logs and Progress</h1>
    
    <div class="term-status-card">
        <div class="term-status-head">
            <div>
                <h4>Current OJT Term</h4>
                <p>Your attendance and progress are tied to this active term.</p>
            </div>
            <span class="term-status-badge {{ $termSummary['badge_class'] }}">{{ $termSummary['badge'] }}</span>
        </div>
        <div class="term-status-grid">
            <div class="term-status-item">
                <span class="term-status-label">Term</span>
                <span class="term-status-value">{{ $termSummary['headline'] }}</span>
            </div>
            <div class="term-status-item">
                <span class="term-status-label">Section</span>
                <span class="term-status-value">{{ $termSummary['section'] ?: 'Not assigned' }}</span>
            </div>
            <div class="term-status-item">
                <span class="term-status-label">School Year</span>
                <span class="term-status-value">{{ $termSummary['school_year'] ?: 'Not set' }}</span>
            </div>
            <div class="term-status-item">
                <span class="term-status-label">Program</span>
                <span class="term-status-value">{{ $termSummary['program'] ?: 'Not set' }}</span>
            </div>
        </div>
        <p class="term-status-note">{{ $termSummary['note'] }}</p>
        @if(($termSummary['history'] ?? collect())->isNotEmpty())
            <div class="term-history-strip">
                @foreach($termSummary['history'] as $historyItem)
                    <span class="term-history-chip">{{ $historyItem->term }} · {{ $historyItem->section }}</span>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Rendering progress -->
    <div class="progress-card">
        <div class="card-header">
            <i class="bi bi-graph-up-arrow"></i>
            <h4>Rendering progress</h4>
        </div>
        <div class="progress-stats mb-3">
            <span class="progress-stat-label">Rendered</span>
            <span class="progress-stat-value">{{ number_format($rendered ?? 0, 1) }} hrs</span>
            <span class="progress-stat-label">Required</span>
            <span class="progress-stat-value">{{ number_format($required ?? 120, 1) }} hrs</span>
            <span class="progress-stat-label">Remaining</span>
            <span class="progress-stat-value">{{ number_format($remaining ?? 0, 1) }} hrs</span>
        </div>
        <div class="progress-bar-wrap">
            <div class="progress-bar-track">
                <div class="progress-bar-fill" data-pct="{{ (int) ($progressPct ?? 0) }}" role="progressbar" aria-valuenow="{{ $progressPct ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <span class="progress-bar-label">{{ $progressPct ?? 0 }}% complete</span>
        </div>
        @if(($rendered ?? 0) >= ($required ?? 120))
        <p class="text-success small mb-0 mt-2"><i class="bi bi-check-circle-fill me-1"></i>You have reached the required OJT hours.</p>
        @endif
    </div>

    <div class="card">
        <div class="card-body">
            <h4><i class="bi bi-clock-history me-2"></i>Recent logs</h4>

            <form method="GET" class="row g-3 align-items-center flex-wrap filter-form mb-3">
                <div class="col-auto">
                    <span class="text-muted small me-2">View by</span>
                    <div class="filter-tabs d-inline-flex">
                        <input type="radio" name="filter" id="recentFilterMonth" value="month" {{ ($filter ?? 'month') === 'month' ? 'checked' : '' }}>
                        <label for="recentFilterMonth">Month</label>
                        <input type="radio" name="filter" id="recentFilterWeek" value="week" {{ ($filter ?? '') === 'week' ? 'checked' : '' }}>
                        <label for="recentFilterWeek">Week</label>
                    </div>
                </div>
                <div class="col-auto filter-panel {{ ($filter ?? 'month') === 'month' ? 'active' : '' }}" data-filter="month">
                    <input type="month" id="recentMonthSelect" name="month" class="form-control form-control-sm" value="{{ $selectedMonth ?? now()->format('Y-m') }}" style="min-width: 160px;" aria-label="Month">
                </div>
                <div class="col-auto filter-panel {{ ($filter ?? '') === 'week' ? 'active' : '' }}" data-filter="week">
                    <div class="d-flex flex-wrap gap-2">
                        <input type="week" id="recentWeekStart" name="week_start" class="form-control form-control-sm" value="{{ $weekStartInput ?? $weekInput ?? '' }}" style="min-width: 160px;" aria-label="Start week">
                        <input type="week" id="recentWeekEnd" name="week_end" class="form-control form-control-sm" value="{{ $weekEndInput ?? $weekStartInput ?? $weekInput ?? '' }}" style="min-width: 160px;" aria-label="End week">
                    </div>
                </div>
                <div class="col-auto d-flex flex-wrap align-items-center gap-2 filter-actions">
                    <button type="submit" class="btn btn-sm dtr-apply-ghost"><span class="dtr-apply-ghost__text">Apply</span></button>
                    <a
                        href="#"
                        id="recentLogsDownloadLink"
                        class="recent-logs-download-btn"
                        data-download-base="{{ route('student.recentlogs.download') }}"
                    >
                        <i class="bi bi-file-earmark-arrow-down" aria-hidden="true"></i>
                        <span>Download PDF logs</span>
                    </a>
                </div>
            </form>
            @if(($filter ?? '') === 'week' && !empty($weekLabel))
            <p class="text-muted small mb-3"><i class="bi bi-calendar3 me-1"></i>{{ $weekLabel }}</p>
            @endif
            @if($logs->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p class="mb-0 fw-medium">No attendance logs found</p>
                    <p class="small mt-1 mb-0">for {{ ($filter ?? 'month') === 'week' ? 'the selected weeks' : 'this month' }}</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle table-dtr-layout">
                        <colgroup>
                            <col class="col-log-date">
                            <col class="col-log-time">
                            <col class="col-log-time">
                            <col class="col-log-time">
                            <col class="col-log-time">
                            <col class="col-log-hours">
                            <col class="col-log-snaps">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Morning<br>Time In</th>
                                <th>Lunch<br>Break Out</th>
                                <th>Afternoon<br>Time In</th>
                                <th>Time Out</th>
                                <th>Hours<br>Rendered</th>
                                <th class="text-nowrap">Verification<br>Snapshots</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($log->date)->format('m/d/y') }}</td>
                                    <td class="dtr-time">@if($log->time_in)<span class="badge bg-success">{{ $log->time_in_12 }}</span>@else<span class="text-muted">—</span>@endif</td>
                                    <td class="dtr-time">@if($log->lunch_break_out)<span class="badge bg-info">{{ $log->lunch_break_out_12 }}</span>@else<span class="text-muted">—</span>@endif</td>
                                    <td class="dtr-time">@if($log->afternoon_time_in)<span class="badge bg-success">{{ $log->afternoon_time_in_12 }}</span>@else<span class="text-muted">—</span>@endif</td>
                                    <td class="dtr-time">@if($log->time_out)<span class="badge bg-info">{{ $log->time_out_12 }}</span>@else<span class="text-muted">—</span>@endif</td>
                                    <td class="dtr-time">
                                        @if($log->hours_rendered_display)
                                            {{ $log->hours_rendered_display }}
                                        @else
                                            <span class="text-muted" title="Recorded when you time out for the day.">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $snapshots = [];
                                            if ($log->verification_snapshot) {
                                                $snapshots[] = '<a href="' . e(route('student.attendance.verification_snapshot', [$log, 'morning'])) . '" target="_blank" rel="noopener" class="btn btn-snapshot"><i class="bi bi-camera-fill"></i> Morning</a>';
                                            }
                                            if ($log->resolvedLunchBreakVerificationSnapshot()) {
                                                $snapshots[] = '<a href="' . e(route('student.attendance.verification_snapshot', [$log, 'lunch'])) . '" target="_blank" rel="noopener" class="btn btn-snapshot"><i class="bi bi-camera-fill"></i> Break</a>';
                                            }
                                            if ($log->afternoon_verification_snapshot) {
                                                $snapshots[] = '<a href="' . e(route('student.attendance.verification_snapshot', [$log, 'afternoon'])) . '" target="_blank" rel="noopener" class="btn btn-snapshot"><i class="bi bi-camera-fill"></i> Afternoon</a>';
                                            }
                                            if ($log->timeout_verification_snapshot) {
                                                $snapshots[] = '<a href="' . e(route('student.attendance.verification_snapshot', [$log, 'timeout'])) . '" target="_blank" rel="noopener" class="btn btn-snapshot"><i class="bi bi-camera-fill"></i> Time out</a>';
                                            }
                                        @endphp
                                        @if(count($snapshots) > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                {!! implode(' ', $snapshots) !!}
                                            </div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                    
            @endif
        </div>
    </div>
</div>
@push('scripts')
<script>
document.querySelectorAll('.progress-bar-fill[data-pct]').forEach(function(el) { el.style.width = (el.getAttribute('data-pct') || 0) + '%'; });
(function() {
    var filterMonth = document.getElementById('recentFilterMonth');
    var filterWeek = document.getElementById('recentFilterWeek');
    var panels = document.querySelectorAll('.dtr-attendance-history .filter-panel');
    var weekStartInput = document.getElementById('recentWeekStart');
    var weekEndInput = document.getElementById('recentWeekEnd');
    function currentIsoWeek() {
        var now = new Date();
        var start = new Date(now);
        start.setDate(now.getDate() - now.getDay() + (now.getDay() === 0 ? -6 : 1));
        var y = start.getFullYear();
        var w = Math.ceil((((start - new Date(y, 0, 1)) / 86400000) + 1) / 7);
        return y + '-W' + String(w).padStart(2, '0');
    }
    function updatePanels() {
        var isWeek = filterWeek && filterWeek.checked;
        panels.forEach(function(p) {
            p.classList.toggle('active', p.getAttribute('data-filter') === (isWeek ? 'week' : 'month'));
        });
        if (isWeek) {
            var fallbackWeek = currentIsoWeek();
            if (weekStartInput && !weekStartInput.value) {
                weekStartInput.value = fallbackWeek;
            }
            if (weekEndInput && !weekEndInput.value) {
                weekEndInput.value = weekStartInput && weekStartInput.value ? weekStartInput.value : fallbackWeek;
            }
        }
    }
    if (weekStartInput && weekEndInput) {
        weekStartInput.addEventListener('change', function() {
            if (!weekEndInput.value || weekEndInput.value < weekStartInput.value) {
                weekEndInput.value = weekStartInput.value;
            }
        });
    }
    if (filterMonth) filterMonth.addEventListener('change', updatePanels);
    if (filterWeek) filterWeek.addEventListener('change', updatePanels);
    updatePanels();

    var dlLink = document.getElementById('recentLogsDownloadLink');
    function updateDownloadHref() {
        if (!dlLink) return;
        var base = dlLink.getAttribute('data-download-base');
        var u = new URL(base, window.location.origin);
        if (filterWeek && filterWeek.checked) {
            u.searchParams.set('filter', 'week');
            if (weekStartInput && weekStartInput.value) {
                u.searchParams.set('week_start', weekStartInput.value);
            }
            if (weekEndInput && weekEndInput.value) {
                u.searchParams.set('week_end', weekEndInput.value);
            }
            u.searchParams.delete('month');
        } else {
            u.searchParams.set('filter', 'month');
            var m = document.getElementById('recentMonthSelect');
            if (m && m.value) {
                u.searchParams.set('month', m.value);
            }
            u.searchParams.delete('week_start');
            u.searchParams.delete('week_end');
        }
        dlLink.setAttribute('href', u.pathname + u.search);
    }
    updateDownloadHref();
    if (filterMonth) filterMonth.addEventListener('change', updateDownloadHref);
    if (filterWeek) filterWeek.addEventListener('change', updateDownloadHref);
    if (weekStartInput) weekStartInput.addEventListener('change', updateDownloadHref);
    if (weekEndInput) weekEndInput.addEventListener('change', updateDownloadHref);
    var monthSel = document.getElementById('recentMonthSelect');
    if (monthSel) monthSel.addEventListener('change', updateDownloadHref);
})();
</script>
@endpush
@endsection



