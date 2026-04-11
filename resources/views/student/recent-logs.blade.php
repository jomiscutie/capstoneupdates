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
    .dtr-attendance-history .filter-form .btn-primary {
        padding: 0.5rem 1rem;
        font-weight: 600;
        font-size: 0.875rem;
        border-radius: 10px;
    }
    .dtr-attendance-history .table-responsive {
        border-radius: 10px;
        overflow-x: auto;
        border: 1px solid var(--attendance-border);
        background: var(--attendance-surface);
    }
    .dtr-attendance-history .table-responsive .table { min-width: 920px; margin-bottom: 0; }
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
        font-size: 0.6875rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 0.875rem 1.125rem;
        border-bottom: 1px solid var(--attendance-border);
    }
    .dtr-attendance-history .table tbody td {
        padding: 0.875rem 1.125rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--attendance-border);
        font-size: 0.875rem;
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
                    <span class="term-history-chip">{{ $historyItem->term }} � {{ $historyItem->section }}</span>
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
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Apply</button>
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
                        <thead>
                            <tr>
                                <th rowspan="2">Date</th>
                                <th colspan="2">A.M.</th>
                                <th colspan="2">P.M.</th>
                                <th colspan="2">Under time</th>
                                <th rowspan="2" title="Total time after you record time out for the day">Hours<br>rendered</th>
                                <th rowspan="2" class="text-nowrap">Verification</th>
                            </tr>
                            <tr class="subhead">
                                <th>Arrival</th>
                                <th>Departure</th>
                                <th>Arrival</th>
                                <th>Departure</th>
                                <th>Hours</th>
                                <th>Minutes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                @php
                                    $lateTotal = (int) ($log->late_minutes ?? 0) + (int) ($log->afternoon_late_minutes ?? 0);
                                    $utH = $lateTotal > 0 ? intdiv($lateTotal, 60) : null;
                                    $utM = $lateTotal > 0 ? $lateTotal % 60 : null;
                                @endphp
                                <tr>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($log->date)->format('m/d/y') }}</td>
                                    <td class="dtr-time">@if($log->time_in){{ $log->time_in_12 }}@else<span class="text-muted">—</span>@endif</td>
                                    <td class="dtr-time">@if($log->lunch_break_out){{ $log->lunch_break_out_12 }}@else<span class="text-muted">—</span>@endif</td>
                                    <td class="dtr-time">@if($log->afternoon_time_in){{ $log->afternoon_time_in_12 }}@else<span class="text-muted">—</span>@endif</td>
                                    <td class="dtr-time">@if($log->time_out){{ $log->time_out_12 }}@else<span class="text-muted">—</span>@endif</td>
                                    <td class="dtr-time">@if($utH !== null){{ $utH }}@else<span class="text-muted">—</span>@endif</td>
                                    <td class="dtr-time">@if($utM !== null){{ $utM }}@else<span class="text-muted">—</span>@endif</td>
                                    <td class="dtr-time">
                                        @if($log->hours_rendered)
                                            {{ $log->hours_rendered }}
                                        @else
                                            <span class="text-muted" title="Recorded when you time out for the day.">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $snapshots = [];
                                            if ($log->verification_snapshot) {
                                                $snapshots[] = '<a href="' . e(route('student.attendance.verification_snapshot', [$log, 'morning'])) . '" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 mb-1"><i class="bi bi-camera-fill"></i> Morning</a>';
                                            }
                                            if ($log->afternoon_verification_snapshot) {
                                                $snapshots[] = '<a href="' . e(route('student.attendance.verification_snapshot', [$log, 'afternoon'])) . '" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 mb-1"><i class="bi bi-camera-fill"></i> Afternoon</a>';
                                            }
                                            if ($log->timeout_verification_snapshot) {
                                                $snapshots[] = '<a href="' . e(route('student.attendance.verification_snapshot', [$log, 'timeout'])) . '" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 mb-1"><i class="bi bi-camera-fill"></i> Time out</a>';
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
})();
</script>
@endpush
@endsection



