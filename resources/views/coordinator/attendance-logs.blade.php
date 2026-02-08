@extends('layouts.coordinator')

@section('title', 'Attendance Logs')

@push('styles')
<style>
    .dashboard-header.dtr-attendance-header { text-align: center; }
    .dashboard-header.dtr-attendance-header .back-link { margin-bottom: 0.5rem; }
    .stats-box {
        background: rgba(255,255,255,0.78); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        border-radius: var(--dtr-radius); box-shadow: 0 4px 20px rgba(0,0,0,0.06), inset 0 1px 0 rgba(255,255,255,0.9);
        padding: 1.25rem 1.5rem; text-align: center; margin-bottom: 1.5rem;
        border: 1px solid rgba(255,255,255,0.5);
        transition: transform 0.35s cubic-bezier(0.22,1,0.36,1), box-shadow 0.35s ease;
    }
    .stats-box:hover { transform: translateY(-4px); box-shadow: 0 16px 40px -12px rgba(37,99,235,0.2); }
    .stats-box h4 { color: var(--dtr-primary); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
    .stats-box .fs-3 { font-variant-numeric: tabular-nums; }
    .dtr-attendance .card { border-radius: 1.25rem; padding: 1.5rem 1.75rem; box-shadow: 0 4px 20px rgba(0,0,0,0.06), inset 0 1px 0 rgba(255,255,255,0.8); border: 1px solid rgba(255,255,255,0.5); background: rgba(255,255,255,0.78); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); }
    .dtr-attendance .card h4 { font-size: 1.15rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem; }
    .dtr-attendance .table-responsive {
        border-radius: var(--dtr-radius);
        overflow-x: auto;
        overflow-y: hidden;
        border: 1px solid var(--dtr-border);
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        position: relative;
    }
    .dtr-attendance .table-responsive .table { min-width: 800px; }
    .dtr-attendance .table-responsive::-webkit-scrollbar { height: 10px; }
    .dtr-attendance .table-responsive::-webkit-scrollbar-track {
        background: rgba(0,0,0,0.04);
        border-radius: 0 0 10px 10px;
    }
    .dtr-attendance .table-responsive::-webkit-scrollbar-thumb {
        background: linear-gradient(90deg, var(--dtr-primary), var(--dtr-primary-dark));
        border-radius: 10px;
    }
    .dtr-attendance .table-responsive::-webkit-scrollbar-thumb:hover { background: var(--dtr-primary-dark); }
    @supports (scrollbar-color: auto) {
        .dtr-attendance .table-responsive { scrollbar-color: var(--dtr-primary) rgba(0,0,0,0.06); scrollbar-width: thin; }
    }
    .dtr-attendance .table thead { background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark)); color: #fff; }
    .dtr-attendance .table thead th { padding: 1rem 1.25rem; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.06em; border: none; vertical-align: middle; }
    .dtr-attendance .table tbody td { padding: 1rem 1.25rem; vertical-align: middle; border-bottom: 1px solid var(--dtr-border); }
    .dtr-attendance .table tbody tr:hover { background: #f8fafc; }
    .dtr-attendance .table tbody tr:last-child td { border-bottom: none; }
    .dtr-attendance .form-control, .dtr-attendance .form-select { border-radius: var(--dtr-radius); border: 1px solid var(--dtr-border); }
    .dtr-attendance .btn-primary { background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark)); border: none; font-weight: 600; border-radius: var(--dtr-radius); padding: 0.5rem 1.25rem; transition: transform var(--dtr-transition), box-shadow var(--dtr-transition); }
    .dtr-attendance .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37,99,235,0.35); }
</style>
@endpush

@section('content')
@php
    $coordinator = auth()->guard('coordinator')->user();
    $major = $coordinator->major ?? null;
@endphp
<div class="dtr-attendance">
    <div class="dashboard-header dtr-attendance-header">
        <div>
            <a href="{{ route('coordinator.dashboard') }}" class="back-link d-inline-block">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
            <h2>Attendance Logs</h2>
            <p>Attendance Management &mdash; {{ now()->format('F Y') }}</p>
        </div>
        @if($major)
            <div class="program-badge-inline">
                <i class="bi bi-mortarboard me-1"></i><strong>Program:</strong> {{ $major }}
            </div>
        @endif
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-box">
                <h4>Total Students</h4>
                <p class="fs-3 fw-bold">{{ $totalStudents ?? '-' }}</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-box">
                <h4>Present Today</h4>
                <p class="fs-3 fw-bold">{{ $presentToday ?? '-' }}</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-box">
                <h4>Absent Today</h4>
                <p class="fs-3 fw-bold">{{ $absentToday ?? '-' }}</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-box" style="background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);">
                <h4 style="color: #856404;">Late Arrivals</h4>
                <p class="fs-3 fw-bold" style="color: #856404;">{{ $lateArrivalsMonth ?? 0 }}</p>
                <small style="color: #856404;">This Month</small>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <form method="GET" class="row g-3 align-items-center">
            <div class="col-auto">
                <label for="monthSelect" class="col-form-label">Select Month:</label>
            </div>
            <div class="col-auto">
                <input type="month" id="monthSelect" name="month" class="form-control" value="{{ request('month', now()->format('Y-m')) }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>

    @if($major)
    <div class="alert alert-info mb-4" style="background: #e7f3ff; border-left: 4px solid #0d6efd; border-radius: 8px;">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Filtered by Program:</strong> You are viewing attendance logs for <strong>{{ $major }}</strong> students only.
    </div>
    @endif

    <div class="card">
        <h4 class="mb-3">Attendance Logs @if($major)<small class="text-muted">({{ $major }})</small>@endif</h4>
        @if(($logs ?? collect())->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered mt-2">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Student No</th>
                            <th>Name</th>
                            <th>Morning Time In</th>
                            <th>Lunch Break Out</th>
                            <th>Afternoon Time In</th>
                            <th>Time Out</th>
                            <th>Status</th>
                            <th>Hours Rendered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($log->date)->format('F d, Y') }}</td>
                                <td>{{ $log->student->student_no ?? '-' }}</td>
                                <td>{{ $log->student->name ?? '-' }}</td>
                                <td>
                                    @if($log->time_in)
                                        <span class="badge {{ $log->is_late ? 'bg-warning text-dark' : 'bg-success' }}">{{ $log->time_in }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->lunch_break_out)
                                        <span class="badge bg-info">{{ $log->lunch_break_out }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->afternoon_time_in)
                                        <span class="badge {{ $log->afternoon_is_late ? 'bg-warning text-dark' : 'bg-success' }}">{{ $log->afternoon_time_in }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->time_out)
                                        <span class="badge bg-danger">{{ $log->time_out }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statuses = array_filter([
                                            $log->time_in ? '<span class="badge ' . ($log->is_late ? 'bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Morning Late (' . $log->late_minutes . 'm)' : 'bg-success"><i class="bi bi-check-circle me-1"></i>Morning On Time') . '</span>' : null,
                                            $log->afternoon_time_in ? '<span class="badge ' . ($log->afternoon_is_late ? 'bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Afternoon Late (' . $log->afternoon_late_minutes . 'm)' : 'bg-success"><i class="bi bi-check-circle me-1"></i>Afternoon On Time') . '</span>' : null,
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted">No attendance logs found for this month.</p>
        @endif
    </div>
</div>
@endsection
