@extends('layouts.student')

@section('title', 'Attendance Logs and Progress')

@push('styles')
<style>
    .progress-card { border-radius: 12px; padding: 1.25rem 1.5rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04); background: #fff; max-width: 280px; margin-bottom: 1.5rem; }
    .progress-card .card-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; }
    .progress-card .card-header i { color: var(--dtr-primary, #4f46e5); }
    .progress-card .card-header h4 { font-size: 1rem; font-weight: 600; color: #0f172a; margin: 0; }
    .progress-stats { display: grid; grid-template-columns: auto 1fr; gap: 0.25rem 0.75rem; align-items: baseline; }
    .progress-stat-label { font-size: 0.8125rem; color: #64748b; font-weight: 500; }
    .progress-stat-value { font-size: 1rem; font-weight: 600; color: #1e293b; font-variant-numeric: tabular-nums; }
    .progress-bar-wrap { display: flex; flex-direction: column; gap: 0.35rem; margin-top: 0.75rem; }
    .progress-bar-track { width: 100%; max-width: 160px; height: 14px; background: #e2e8f0; border: 1.5px solid #cbd5e1; border-radius: 6px; overflow: hidden; box-sizing: border-box; }
    .progress-bar-fill { height: 100%; background: linear-gradient(90deg, #16a34a, #22c55e); border-radius: 5px; transition: width 0.3s ease; }
    .progress-bar-label { font-size: 0.8125rem; font-weight: 600; color: #475569; }
    .dtr-attendance-history {
        --attendance-text: #1e293b;
        --attendance-muted: #64748b;
        --attendance-border: #e2e8f0;
        --attendance-surface: #ffffff;
        --attendance-heading: #0f172a;
    }
    .dtr-attendance-history .card {
        border-radius: 12px;
        padding: 1.5rem 1.75rem;
        border: 1px solid var(--attendance-border);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
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
        background: #f8fafc;
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
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
    }
    .dtr-attendance-history .filter-panel { display: none; }
    .dtr-attendance-history .filter-panel.active { display: block; }
    .dtr-attendance-history .filter-form .form-control {
        border: 1px solid var(--attendance-border);
        border-radius: 10px;
        font-size: 0.9375rem;
        padding: 0.5rem 0.75rem;
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
    .dtr-attendance-history .table-responsive .table { min-width: 600px; margin-bottom: 0; }
    .dtr-attendance-history .table thead th {
        background: #f8fafc;
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
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.875rem;
        color: var(--attendance-text);
    }
    .dtr-attendance-history .table tbody tr:last-child td { border-bottom: none; }
    .dtr-attendance-history .table tbody tr:hover { background: #f8fafc; }
    .dtr-attendance-history .empty-state {
        text-align: center;
        padding: 2.5rem 1.5rem;
        color: var(--attendance-muted);
    }
    .dtr-attendance-history .empty-state i { font-size: 2.5rem; color: #e2e8f0; margin-bottom: 0.75rem; display: block; }
</style>
@endpush

@section('content')
<div class="dtr-attendance-history">
    <a href="{{ route('student.dashboard') }}" class="back-link d-inline-block mb-2">
        <i class="bi bi-arrow-left"></i> Dashboard
    </a>
    <h1 class="page-title">Attendance Logs and Progress</h1>
    <p class="page-sub mb-3">View by month or week</p>

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
                    <input type="week" id="recentWeekSelect" name="week" class="form-control form-control-sm" value="{{ $weekInput ?? '' }}" style="min-width: 160px;" aria-label="Week">
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
                    <p class="small mt-1 mb-0">for {{ ($filter ?? 'month') === 'week' ? 'this week' : 'this month' }}</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Hours Rendered</th>
                                <th class="text-nowrap">Verification snapshot</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log->date)->format('m/d/y') }}</td>
                                    <td>{{ $log->time_in_12 ?? '-' }}</td>
                                    <td>{{ $log->time_out_12 ?? '-' }}</td>
                                    <td>{{ $log->hours_rendered ?? '-' }}</td>
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
                                            <span class="text-muted small">—</span>
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
    var weekInput = document.getElementById('recentWeekSelect');
    function updatePanels() {
        var isWeek = filterWeek && filterWeek.checked;
        panels.forEach(function(p) {
            p.classList.toggle('active', p.getAttribute('data-filter') === (isWeek ? 'week' : 'month'));
        });
        if (isWeek && weekInput && !weekInput.value) {
            var now = new Date();
            var start = new Date(now);
            start.setDate(now.getDate() - now.getDay() + (now.getDay() === 0 ? -6 : 1));
            var y = start.getFullYear();
            var w = Math.ceil((((start - new Date(y, 0, 1)) / 86400000) + 1) / 7);
            weekInput.value = y + '-W' + String(w).padStart(2, '0');
        }
    }
    if (filterMonth) filterMonth.addEventListener('change', updatePanels);
    if (filterWeek) filterWeek.addEventListener('change', updatePanels);
    updatePanels();
})();
</script>
@endpush
@endsection
