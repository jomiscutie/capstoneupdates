@extends('layouts.coordinator')

@section('title', 'Attendance Analytics')

@push('styles')
<style>
    .dtr-analytics .back-link { margin-bottom: 0.5rem; }
    .dtr-analytics .page-title { font-size: 1.5rem; font-weight: 600; color: var(--dtr-text); margin-bottom: 0.25rem; text-align: center; }
    .dtr-analytics .page-sub { font-size: 0.9rem; color: var(--dtr-muted); margin: 0 auto 1.25rem; text-align: center; max-width: 760px; }
    .dtr-analytics .card { border-radius: 12px; padding: 1.5rem 1.75rem; border: 1px solid var(--dtr-border-soft); box-shadow: var(--dtr-shadow-soft); }
    .dtr-analytics .analytics-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; }
    .dtr-analytics .analytics-title { font-size: 1.1rem; font-weight: 600; color: var(--dtr-text); margin: 0; }
    .dtr-analytics .analytics-title i { color: var(--dtr-primary); font-size: 1.25rem; }
    .dtr-analytics .analytics-sub { font-size: 0.85rem; color: var(--dtr-muted); margin-bottom: 1.25rem; }
    .dtr-analytics .analytics-table { font-size: 0.9rem; }
    .dtr-analytics .analytics-table th { font-weight: 600; color: var(--dtr-muted); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 1rem; border-bottom: 1px solid var(--dtr-border-soft); }
    .dtr-analytics .analytics-table td { padding: 0.85rem 1rem; border-bottom: 1px solid var(--dtr-border-soft); vertical-align: middle; }
    .dtr-analytics .analytics-table tr:last-child td { border-bottom: none; }
    .dtr-analytics .analytics-table tbody tr:hover { background: var(--dtr-hover-bg); }
    .dtr-analytics .analytics-table .month-cell { font-weight: 500; color: var(--dtr-text); }
    .dtr-analytics .analytics-bar-wrap { display: flex; align-items: center; gap: 0.75rem; min-width: 160px; }
    .dtr-analytics .analytics-bar-track { flex: 1; height: 8px; background: var(--dtr-surface-soft); border-radius: 4px; overflow: hidden; min-width: 80px; }
    .dtr-analytics .analytics-bar-fill { height: 100%; border-radius: 4px; transition: width 0.3s ease; }
    .dtr-analytics .analytics-bar-fill.present { background: linear-gradient(90deg, #22c55e, #16a34a); }
    .dtr-analytics .analytics-num { font-variant-numeric: tabular-nums; font-weight: 600; }
    .dtr-analytics .analytics-num.present { color: #16a34a; }
    .dtr-analytics .analytics-num.absent { color: #d97706; }
    .dtr-analytics .summary-box { background: var(--dtr-surface-soft); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.9rem; color: var(--dtr-muted); }
    .dtr-analytics .summary-box strong { color: var(--dtr-text); }
    .dtr-analytics .compare-card { margin-bottom: 1.5rem; }
    .dtr-analytics .compare-form { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.75rem; }
    .dtr-analytics .compare-form .form-group { margin: 0; }
    .dtr-analytics .compare-form label { font-size: 0.8rem; font-weight: 500; color: var(--dtr-muted); margin-bottom: 0.35rem; display: block; }
    .dtr-analytics .compare-form input[type="month"] { min-width: 150px; padding: 0.5rem 0.65rem; border-radius: 8px; border: 1px solid var(--dtr-input-border); background: var(--dtr-input-bg); color: var(--dtr-text); font-size: 0.9rem; }
    /* Compare — aligned with search-submit / minimalist primary (overrides classic-ui .btn-primary) */
    .layout-wrap .main-content .dtr-analytics .compare-form .btn.btn-primary.btn-compare {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.5rem !important;
        min-height: 42px !important;
        padding: 0.5rem 1.2rem !important;
        border-radius: 11px !important;
        font-size: 0.875rem !important;
        font-weight: 600 !important;
        letter-spacing: 0.02em !important;
        line-height: 1.2 !important;
        border: 1px solid color-mix(in srgb, var(--dtr-primary) 52%, var(--dtr-input-border)) !important;
        background: color-mix(in srgb, var(--dtr-primary) 11%, var(--dtr-card-bg) 89%) !important;
        background-image: none !important;
        color: var(--dtr-primary-dark, var(--dtr-primary)) !important;
        box-shadow: none !important;
        transition: border-color 0.2s ease, background 0.2s ease, color 0.18s ease, transform 0.12s ease !important;
    }
    .layout-wrap .main-content .dtr-analytics .compare-form .btn.btn-primary.btn-compare i {
        font-size: 1rem !important;
        line-height: 1 !important;
    }
    .layout-wrap .main-content .dtr-analytics .compare-form .btn.btn-primary.btn-compare:hover,
    .layout-wrap .main-content .dtr-analytics .compare-form .btn.btn-primary.btn-compare:focus {
        border-color: color-mix(in srgb, var(--dtr-primary) 70%, var(--dtr-input-border)) !important;
        background: color-mix(in srgb, var(--dtr-primary) 20%, var(--dtr-card-bg) 80%) !important;
        color: var(--dtr-heading) !important;
    }
    .layout-wrap .main-content .dtr-analytics .compare-form .btn.btn-primary.btn-compare:focus-visible {
        outline: none !important;
        box-shadow:
            0 0 0 2px var(--dtr-card-bg),
            0 0 0 4px color-mix(in srgb, var(--dtr-primary) 32%, transparent) !important;
    }
    .layout-wrap .main-content .dtr-analytics .compare-form .btn.btn-primary.btn-compare:active {
        transform: scale(0.985);
    }
    @media (prefers-reduced-motion: reduce) {
        .layout-wrap .main-content .dtr-analytics .compare-form .btn.btn-primary.btn-compare {
            transition: border-color 0.2s ease, background 0.2s ease, color 0.18s ease !important;
        }
        .layout-wrap .main-content .dtr-analytics .compare-form .btn.btn-primary.btn-compare:active {
            transform: none;
        }
    }
    html[data-theme="dark"]
        .layout-wrap
        .main-content
        .dtr-analytics
        .compare-form
        .btn.btn-primary.btn-compare {
        border-color: color-mix(in srgb, var(--dtr-primary) 48%, var(--dtr-input-border)) !important;
        background: color-mix(in srgb, var(--dtr-primary) 17%, var(--dtr-card-bg) 83%) !important;
        color: color-mix(in srgb, var(--dtr-primary) 68%, #e8ecff 32%) !important;
    }
    html[data-theme="dark"]
        .layout-wrap
        .main-content
        .dtr-analytics
        .compare-form
        .btn.btn-primary.btn-compare:hover,
    html[data-theme="dark"]
        .layout-wrap
        .main-content
        .dtr-analytics
        .compare-form
        .btn.btn-primary.btn-compare:focus {
        background: color-mix(in srgb, var(--dtr-primary) 26%, var(--dtr-card-bg) 74%) !important;
        color: #f8fafc !important;
    }
    .dtr-analytics .compare-hint { font-size: 0.8rem; color: var(--dtr-muted); margin-top: 0.5rem; }
    .dtr-analytics .comparison-table { font-size: 0.95rem; }
    .dtr-analytics .comparison-table th { font-weight: 600; color: var(--dtr-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 1rem; border-bottom: 1px solid var(--dtr-border-soft); text-align: center; }
    .dtr-analytics .comparison-table th:first-child { text-align: left; }
    .dtr-analytics .comparison-table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--dtr-border-soft); vertical-align: middle; text-align: center; }
    .dtr-analytics .comparison-table td:first-child { text-align: left; font-weight: 500; color: var(--dtr-text); }
    .dtr-analytics .comparison-table tr:last-child td { border-bottom: none; }
    .dtr-analytics .comparison-table .metric-present { color: #16a34a; font-weight: 600; font-variant-numeric: tabular-nums; }
    .dtr-analytics .comparison-table .metric-absent { color: #d97706; font-weight: 600; font-variant-numeric: tabular-nums; }
    .dtr-analytics .comparison-table .metric-rate { color: var(--dtr-primary); font-weight: 600; font-variant-numeric: tabular-nums; }
</style>
@endpush

@section('content')
@php
    $coordinator = auth()->guard('coordinator')->user();
    $assignedProgramsList = isset($assignedPrograms) ? collect($assignedPrograms) : collect();
    $major = $assignedProgramsList->implode(' · ');
    $major = $major !== '' ? $major : ($coordinator->major ?? null);
@endphp
<div class="dtr-analytics">
    <h1 class="page-title">Attendance Analytics</h1>
    <p class="page-sub">Monthly presents and absences @if($major) · {{ $major }} @endif</p>

    <div class="card compare-card">
        <div class="card-body">
            <div class="analytics-header">
                <h2 class="analytics-title"><i class="bi bi-calendar2-range"></i> Compare months</h2>
            </div>
            <p class="analytics-sub mb-3">Select 2 or 3 months to compare side by side.</p>
            <form action="{{ route('coordinator.attendance.analytics') }}" method="GET" class="compare-form">
                @for($i = 0; $i < 3; $i++)
                <div class="form-group">
                    <label for="compare_{{ $i }}">Month {{ $i + 1 }}</label>
                    <input type="month" id="compare_{{ $i }}" name="compare[]" class="form-control" value="{{ $compareValues[$i] ?? '' }}">
                </div>
                @endfor
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-compare"><i class="bi bi-arrow-left-right" aria-hidden="true"></i> Compare</button>
                </div>
            </form>
            <p class="compare-hint">Leave unused slots empty. At least 2 months required.</p>
        </div>
    </div>

    @if(!empty($comparisonMonths) && count($comparisonMonths) >= 2)
    <div class="card mb-4">
        <div class="card-body">
            <div class="analytics-header">
                <h2 class="analytics-title"><i class="bi bi-ui-checks-grid"></i> Comparison result</h2>
            </div>
            <div class="table-responsive">
                <table class="table comparison-table mb-0">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            @foreach($comparisonMonths as $m)
                            <th>{{ $m['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Present (days)</td>
                            @foreach($comparisonMonths as $m)
                            <td class="metric-present">{{ number_format($m['present_days']) }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Absent (students)</td>
                            @foreach($comparisonMonths as $m)
                            <td class="metric-absent">{{ number_format($m['absent_students']) }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>% attended</td>
                            @foreach($comparisonMonths as $m)
                            <td class="metric-rate">{{ $m['rate'] }}%</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="analytics-header">
                <h2 class="analytics-title"><i class="bi bi-bar-chart-line"></i> Attendance by month</h2>
            </div>
            <p class="analytics-sub">Last 12 months: present days (total attendance records) and students with no attendance in that month.</p>
            <div class="summary-box">
                <strong>{{ $totalStudents }}</strong> verified students in scope. “Absent” = students with zero attendance that month.
            </div>
            <div class="table-responsive">
                <table class="table analytics-table mb-0">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Present (days)</th>
                            <th>Absent (students)</th>
                            <th>Trend</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlyAnalytics ?? [] as $row)
                        @php
                            $rate = $totalStudents > 0 ? round(100 * $row['unique_present'] / $totalStudents) : 0;
                        @endphp
                        <tr>
                            <td class="month-cell">{{ $row['label'] }}</td>
                            <td><span class="analytics-num present">{{ number_format($row['present_days']) }}</span></td>
                            <td><span class="analytics-num absent">{{ number_format($row['absent_students']) }}</span></td>
                            <td>
                                <div class="analytics-bar-wrap">
                                    <div class="analytics-bar-track">
                                        <div class="analytics-bar-fill present" data-pct="{{ (int) $rate }}"></div>
                                    </div>
                                    <span class="small text-muted">{{ $rate }}% attended</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.analytics-bar-fill[data-pct]').forEach(function(el) {
    el.style.width = (el.getAttribute('data-pct') || 0) + '%';
});
</script>
@endpush
@endsection
