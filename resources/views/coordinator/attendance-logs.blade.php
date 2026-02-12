@extends('layouts.coordinator')

@section('title', 'Attendance Logs')

@push('styles')
<style>
    .dtr-attendance {
        --attendance-text: #1e293b;
        --attendance-muted: #64748b;
        --attendance-border: #e2e8f0;
        --attendance-surface: #ffffff;
        --attendance-heading: #0f172a;
    }
    .dtr-attendance .back-link {
        display: inline-flex; align-items: center; gap: 0.35rem;
        font-size: 0.875rem; color: var(--attendance-muted); text-decoration: none;
        margin-bottom: 0.75rem; font-weight: 500; letter-spacing: 0.01em;
        transition: color 0.2s ease;
    }
    .dtr-attendance .back-link:hover { color: var(--dtr-primary); }
    .dtr-attendance .page-title {
        font-size: 1.5rem; font-weight: 600; color: var(--attendance-heading);
        margin-bottom: 0.25rem; letter-spacing: -0.02em; line-height: 1.3;
    }
    .dtr-attendance .page-sub {
        font-size: 0.875rem; color: var(--attendance-muted);
        margin-bottom: 1.5rem; letter-spacing: 0.01em;
    }
    .dtr-attendance .stats-box {
        background: var(--attendance-surface);
        border: 1px solid var(--attendance-border);
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 1.25rem 1rem;
        text-align: center;
        margin-bottom: 1rem;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .dtr-attendance .stats-box:hover { box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.06); }
    .dtr-attendance .stats-box h4 {
        font-size: 0.6875rem; font-weight: 600; color: var(--attendance-muted);
        text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;
    }
    .dtr-attendance .stats-box .fs-3 { font-variant-numeric: tabular-nums; color: var(--attendance-text); font-weight: 600; }
    .dtr-attendance .stats-box.stats-warning h4,
    .dtr-attendance .stats-box.stats-warning .fs-3 { color: #b45309; }
    .dtr-attendance .card {
        border-radius: 12px;
        padding: 1.5rem 1.75rem;
        border: 1px solid var(--attendance-border);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        background: var(--attendance-surface);
    }
    .dtr-attendance .card h4 {
        font-size: 1rem; font-weight: 600; color: var(--attendance-heading);
        margin-bottom: 1rem; letter-spacing: -0.01em;
    }
    .dtr-attendance .table-responsive {
        border-radius: 10px; overflow-x: auto;
        border: 1px solid var(--attendance-border);
        background: var(--attendance-surface);
    }
    .dtr-attendance .table-responsive .table { min-width: 700px; margin-bottom: 0; }
    .dtr-attendance .table thead th {
        background: #f8fafc;
        color: var(--attendance-muted);
        font-weight: 600;
        font-size: 0.6875rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 0.875rem 1.125rem;
        border-bottom: 1px solid var(--attendance-border);
    }
    .dtr-attendance .table tbody td {
        padding: 0.875rem 1.125rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.875rem;
        color: var(--attendance-text);
    }
    .dtr-attendance .table tbody tr:last-child td { border-bottom: none; }
    .dtr-attendance .table tbody tr:hover { background: #f8fafc; }
    .dtr-attendance .alert { border-radius: 10px; border: 1px solid var(--attendance-border); }
    .dtr-attendance .search-wrap { margin-bottom: 1.25rem; }
    .dtr-attendance .search-row { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; max-width: 520px; }
    .dtr-attendance .search-inner { position: relative; flex: 1; min-width: 220px; }
    .dtr-attendance .search-input {
        width: 100%; padding: 0.625rem 2.75rem 0.625rem 2.5rem;
        font-size: 0.9375rem; border: 1px solid var(--attendance-border); border-radius: 10px;
        background: var(--attendance-surface); color: var(--attendance-text);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .dtr-attendance .search-input::placeholder { color: #94a3b8; }
    .dtr-attendance .search-input:focus {
        outline: none; border-color: var(--dtr-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    .dtr-attendance .search-icon {
        position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);
        color: #94a3b8; font-size: 1rem; pointer-events: none;
    }
    .dtr-attendance .search-clear {
        position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%);
        width: 28px; height: 28px; border: none; border-radius: 8px;
        background: transparent; color: #64748b;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; transition: background 0.2s, color 0.2s; text-decoration: none; font-size: 0.8125rem;
    }
    .dtr-attendance .search-clear:hover { background: #f1f5f9; color: var(--attendance-text); }
    .dtr-attendance .btn-search {
        padding: 0.625rem 1.125rem; font-size: 0.875rem; font-weight: 600;
        border-radius: 10px; white-space: nowrap; flex-shrink: 0;
        letter-spacing: 0.02em;
    }
    .dtr-attendance .search-hint { font-size: 0.8125rem; color: var(--attendance-muted); margin-top: 0.5rem; }
    .dtr-attendance .filter-tabs {
        display: flex; gap: 0; border-radius: 10px; overflow: hidden;
        border: 1px solid var(--attendance-border); background: #f8fafc;
    }
    .dtr-attendance .filter-tabs label {
        margin: 0; cursor: pointer; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500;
        color: var(--attendance-muted); transition: color 0.2s, background 0.2s;
        letter-spacing: 0.02em;
    }
    .dtr-attendance .filter-tabs input { position: absolute; opacity: 0; }
    .dtr-attendance .filter-tabs input:checked + label {
        background: var(--attendance-surface); color: var(--dtr-primary);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
    }
    .dtr-attendance .filter-panel { display: none; }
    .dtr-attendance .filter-panel.active { display: block; }
    .dtr-attendance .card-body form.row { align-items: center; }
    .dtr-attendance .card-body form .btn-primary.btn-sm { min-height: 31px; display: inline-flex; align-items: center; justify-content: center; padding: 0.25rem 0.75rem; font-weight: 600; font-size: 0.875rem; border-radius: 10px; letter-spacing: 0.02em; }
    .dtr-attendance .search-row { align-items: center; }
    .dtr-attendance .search-input { min-height: 42px; }
    .dtr-attendance .btn-search { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; }
    .dtr-attendance .view-student-bar {
        display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem;
        padding: 0.75rem 1.25rem; margin-bottom: 1.25rem;
        background: #f0f9ff; border: 1px solid #bae6fd;
        border-radius: 10px; font-size: 0.875rem;
        letter-spacing: 0.01em;
    }
    .dtr-attendance .view-student-bar .view-student-label { color: var(--attendance-text); font-weight: 500; }
    .dtr-attendance .view-student-bar .view-student-name { color: #0369a1; font-weight: 600; }
    .dtr-attendance .view-student-bar .btn-show-all {
        padding: 0.375rem 0.75rem; font-size: 0.8125rem; font-weight: 600;
        border-radius: 8px; letter-spacing: 0.02em;
        flex-shrink: 0; display: inline-flex; align-items: center;
    }
    .dtr-attendance .btn-view-log {
        padding: 0.375rem 0.75rem; font-size: 0.8125rem; font-weight: 500;
        border-radius: 8px; white-space: nowrap; letter-spacing: 0.02em;
    }
    .dtr-attendance .student-list { list-style: none; padding: 0; margin: 0; }
    .dtr-attendance .student-list li {
        display: flex; align-items: center; flex-wrap: nowrap; gap: 1rem;
        padding: 0.875rem 0; border-bottom: 1px solid #f1f5f9;
        transition: background 0.2s ease;
    }
    .dtr-attendance .student-list li:last-child { border-bottom: none; }
    .dtr-attendance .student-list li:hover { background: #f8fafc; padding-left: 0.5rem; padding-right: 0.5rem; margin: 0 -0.5rem; border-radius: 8px; }
    .dtr-attendance .student-list .student-name {
        font-weight: 600; color: var(--attendance-text); width: 200px; min-width: 200px; flex-shrink: 0;
        font-size: 0.9375rem; letter-spacing: 0.01em;
    }
    .dtr-attendance .student-list .student-no {
        color: var(--attendance-muted); font-size: 0.875rem;
        font-variant-numeric: tabular-nums; flex: 1; min-width: 0;
    }
    .dtr-attendance .student-list .btn-view-attendance {
        padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600;
        border-radius: 10px; letter-spacing: 0.02em;
        flex-shrink: 0; margin-left: auto; display: inline-flex; align-items: center; justify-content: center;
    }
    .dtr-attendance .text-center.py-4.text-muted .fs-2 { color: #cbd5e1; }
    .dtr-attendance .text-muted.mb-0 { font-size: 0.9375rem; color: var(--attendance-muted); }
</style>
@endpush

@section('content')
@php
    $coordinator = auth()->guard('coordinator')->user();
    $major = $coordinator->major ?? null;
@endphp
<div class="dtr-attendance">
    <a href="{{ route('coordinator.dashboard') }}" class="back-link d-inline-block">
        <i class="bi bi-arrow-left"></i> Dashboard
    </a>
    <h1 class="page-title">Attendance Logs</h1>
    <p class="page-sub">Attendance Management @if(($filter ?? 'month') === 'week' && !empty($weekLabel)) — {{ $weekLabel }} @else — {{ now()->format('F Y') }}@endif @if($major) · {{ $major }}@endif</p>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="stats-box">
                <h4>Total Students</h4>
                <p class="fs-3 fw-bold mb-0">{{ $totalStudents ?? '-' }}</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-box">
                <h4>Present Today</h4>
                <p class="fs-3 fw-bold mb-0">{{ $presentToday ?? '-' }}</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-box">
                <h4>Absent Today</h4>
                <p class="fs-3 fw-bold mb-0">{{ $absentToday ?? '-' }}</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-box stats-warning">
                <h4>{{ ($filter ?? 'month') === 'week' ? 'Late This Week' : 'Late This Month' }}</h4>
                <p class="fs-3 fw-bold mb-0">{{ $lateCount ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-3 align-items-center flex-wrap">
                <div class="col-auto">
                    <span class="text-muted small me-2">View by</span>
                    <div class="filter-tabs d-inline-flex">
                        <input type="radio" name="filter" id="filterMonth" value="month" {{ ($filter ?? 'month') === 'month' ? 'checked' : '' }}>
                        <label for="filterMonth">Month</label>
                        <input type="radio" name="filter" id="filterWeek" value="week" {{ ($filter ?? '') === 'week' ? 'checked' : '' }}>
                        <label for="filterWeek">Week</label>
                    </div>
                </div>
                <div class="col-auto filter-panel {{ ($filter ?? 'month') === 'month' ? 'active' : '' }}" data-filter="month">
                    <input type="month" id="monthSelect" name="month" class="form-control form-control-sm" value="{{ request('month', now()->format('Y-m')) }}" style="min-width: 160px;" aria-label="Month">
                </div>
                <div class="col-auto filter-panel {{ ($filter ?? '') === 'week' ? 'active' : '' }}" data-filter="week">
                    <label for="weekSelect" class="form-label small mb-0 text-muted">Week</label>
                    <input type="week" id="weekSelect" name="week" class="form-control form-control-sm" value="{{ $weekInput ?? '' }}" style="min-width: 160px;">
                </div>
                @if(!empty($search ?? ''))
                <input type="hidden" name="q" value="{{ e($search) }}">
                @endif
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                </div>
            </form>
        </div>
    </div>

    @if($major)
    <div class="alert alert-info py-2 mb-3">
        <i class="bi bi-info-circle me-2"></i>Showing logs for <strong>{{ $major }}</strong> only.
    </div>
    @endif

    @if(!empty($viewStudent))
    <div class="view-student-bar">
        <span class="view-student-label"><i class="bi bi-person-video2 me-1"></i>Showing attendance for</span>
        <span class="view-student-name">{{ $viewStudent->name }} ({{ $viewStudent->student_no ?? '—' }})</span>
        <a href="{{ route('coordinator.attendance.logs', array_filter(['month' => request('month', now()->format('Y-m')), 'filter' => $filter ?? 'month', 'week' => ($filter ?? '') === 'week' ? ($weekInput ?? null) : null])) }}" class="btn btn-sm btn-outline-primary btn-show-all ms-2">Show all</a>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <h4 class="mb-3">Logs @if(($filter ?? 'month') === 'week' && !empty($weekLabel))<span class="text-muted fw-normal">({{ $weekLabel }})</span>@elseif($major)<span class="text-muted fw-normal">({{ $major }})</span>@endif</h4>
            <div class="search-wrap">
                <form action="{{ route('coordinator.attendance.logs') }}" method="GET" class="search-row" role="search">
                    <input type="hidden" name="filter" value="{{ $filter ?? 'month' }}">
                    <input type="hidden" name="month" value="{{ request('month', now()->format('Y-m')) }}">
                    @if(($filter ?? '') === 'week' && !empty($weekInput))
                    <input type="hidden" name="week" value="{{ $weekInput }}">
                    @endif
                    <div class="search-inner">
                        <i class="bi bi-search search-icon" aria-hidden="true"></i>
                        <input type="text"
                               name="q"
                               class="search-input form-control"
                               placeholder="Name or student number…"
                               value="{{ old('q', $search ?? '') }}"
                               autocomplete="off"
                               aria-label="Search by name or student number">
                        @if(!empty($search ?? ''))
                        <a href="{{ route('coordinator.attendance.logs', array_filter(['month' => request('month', now()->format('Y-m')), 'filter' => $filter ?? 'month', 'week' => ($filter ?? '') === 'week' ? ($weekInput ?? null) : null])) }}" class="search-clear" title="Clear search" aria-label="Clear search">
                            <i class="bi bi-x-lg"></i>
                        </a>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary btn-search">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                </form>
                <p class="search-hint">Matches any part of name or student number.</p>
            </div>

        @if(!empty($viewStudent))
            {{-- Viewing one student: show their logs table --}}
            @if($logs->count() > 0)
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Morning Time In</th>
                            <th>Lunch Break Out</th>
                            <th>Afternoon Time In</th>
                            <th>Time Out</th>
                            <th>Status</th>
                            <th>Hours Rendered</th>
                            <th class="text-nowrap">Verification snapshot</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($log->date)->format('m/d/y') }}</td>
                                <td>
                                    @if($log->time_in)
                                        <span class="badge {{ $log->is_late ? 'bg-warning text-dark' : 'bg-success' }}">{{ $log->time_in_12 }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->lunch_break_out)
                                        <span class="badge bg-info">{{ $log->lunch_break_out_12 }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->afternoon_time_in)
                                        <span class="badge {{ $log->afternoon_is_late ? 'bg-warning text-dark' : 'bg-success' }}">{{ $log->afternoon_time_in_12 }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->time_out)
                                        <span class="badge bg-danger">{{ $log->time_out_12 }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statuses = array_filter([
                                            $log->time_in ? '<span class="badge ' . ($log->is_late ? 'bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Morning Late (' . $log->late_display . ')' : 'bg-success"><i class="bi bi-check-circle me-1"></i>Morning On Time') . '</span>' : null,
                                            $log->afternoon_time_in ? '<span class="badge ' . ($log->afternoon_is_late ? 'bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Afternoon Late (' . $log->afternoon_late_display . ')' : 'bg-success"><i class="bi bi-check-circle me-1"></i>Afternoon On Time') . '</span>' : null,
                                        ]);
                                    @endphp
                                    @if($statuses)
                                        {!! implode('<br>', $statuses) !!}
                                    @else
                                        <span class="badge bg-secondary">No Time In</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $hoursDisplay = $log->hours_rendered ?? null;
                                        if (empty($hoursDisplay)) {
                                            $totalMinutes = 0;
                                            $dateStr = \Carbon\Carbon::parse($log->date)->format('Y-m-d');
                                            $toDatetime = function($time) use ($dateStr) {
                                                if (empty($time)) return null;
                                                $t = (string) $time;
                                                return str_contains($t, ' ') ? $t : $dateStr . ' ' . $t;
                                            };
                                            try {
                                                if ($log->time_in && $log->lunch_break_out) {
                                                    $start = \Carbon\Carbon::parse($toDatetime($log->time_in));
                                                    $end = \Carbon\Carbon::parse($toDatetime($log->lunch_break_out));
                                                    if ($end->gt($start)) $totalMinutes += $start->diffInMinutes($end);
                                                }
                                                if ($log->afternoon_time_in && $log->time_out) {
                                                    $start = \Carbon\Carbon::parse($toDatetime($log->afternoon_time_in));
                                                    $end = \Carbon\Carbon::parse($toDatetime($log->time_out));
                                                    if ($end->gt($start)) $totalMinutes += $start->diffInMinutes($end);
                                                }
                                            } catch (\Throwable $e) {
                                                $totalMinutes = 0;
                                            }
                                            $hoursDisplay = $totalMinutes > 0 ? (floor($totalMinutes / 60) . ' hr ' . ($totalMinutes % 60) . ' min') : null;
                                        }
                                    @endphp
                                    @if($hoursDisplay)
                                        {{ $hoursDisplay }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $snapshots = [];
                                        if ($log->verification_snapshot) {
                                            $snapshots[] = '<a href="' . e(route('coordinator.attendance.verification_snapshot', [$log, 'morning'])) . '" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 mb-1"><i class="bi bi-camera-fill"></i> Morning</a>';
                                        }
                                        if ($log->afternoon_verification_snapshot) {
                                            $snapshots[] = '<a href="' . e(route('coordinator.attendance.verification_snapshot', [$log, 'afternoon'])) . '" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 mb-1"><i class="bi bi-camera-fill"></i> Afternoon</a>';
                                        }
                                        if ($log->timeout_verification_snapshot) {
                                            $snapshots[] = '<a href="' . e(route('coordinator.attendance.verification_snapshot', [$log, 'timeout'])) . '" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 mb-1"><i class="bi bi-camera-fill"></i> Time out</a>';
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
            @else
            <p class="text-muted mb-0">No attendance logs for this student for {{ ($filter ?? 'month') === 'week' ? 'this week' : 'this month' }}.</p>
            @endif
        @else
            {{-- List of students (no repeating names) with View attendance button --}}
            @if(($studentsWithLogs ?? collect())->count() > 0)
            <p class="text-muted small mb-2">Select a student to view their attendance logs for this period.</p>
            <ul class="student-list">
                @foreach($studentsWithLogs as $student)
                @php
                    $viewParams = array_filter([
                        'student_id' => $student->id ?? null,
                        'month' => request('month', now()->format('Y-m')),
                        'filter' => $filter ?? 'month',
                        'week' => ($filter ?? '') === 'week' ? ($weekInput ?? null) : null,
                    ]);
                @endphp
                <li>
                    <span class="student-name">{{ $student->name ?? '-' }}</span>
                    <span class="student-no">{{ $student->student_no ?? '—' }}</span>
                    <a href="{{ route('coordinator.attendance.logs', $viewParams) }}" class="btn btn-primary btn-sm btn-view-attendance">
                        <i class="bi bi-person-lines-fill me-1"></i>View attendance
                    </a>
                </li>
                @endforeach
            </ul>
            @else
            @if(!empty($search ?? ''))
            <div class="text-center py-4 text-muted">
                <i class="bi bi-search d-block fs-2 mb-2" style="color: #cbd5e1;"></i>
                <p class="mb-0 fw-medium">No logs match "{{ e($search) }}"</p>
                <p class="small mt-1 mb-0">Try a different name or student number, or <a href="{{ route('coordinator.attendance.logs', array_filter(['month' => request('month', now()->format('Y-m')), 'filter' => $filter ?? 'month', 'week' => ($filter ?? '') === 'week' ? ($weekInput ?? null) : null])) }}">clear the search</a>.</p>
            </div>
            @else
            <p class="text-muted mb-0">No attendance logs for {{ ($filter ?? 'month') === 'week' ? 'this week' : 'this month' }}.</p>
            @endif
            @endif
        @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var filterMonth = document.getElementById('filterMonth');
    var filterWeek = document.getElementById('filterWeek');
    var panels = document.querySelectorAll('.dtr-attendance .filter-panel');
    var weekInput = document.getElementById('weekSelect');
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
