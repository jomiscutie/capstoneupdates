<!DOCTYPE html>
<html>
<head>
    <title>Attendance Logs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f8; font-family: Arial, sans-serif; }
        .card { border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .table th, .table td { vertical-align: middle; }
        .dashboard-header {
            background: linear-gradient(90deg, #2d6cdf 60%, #4e8cff 100%);
            color: #fff;
            border-radius: 12px 12px 0 0;
            padding: 32px 24px 18px 24px;
            box-shadow: 0 4px 15px rgba(44,108,223,0.08);
            text-align: center;
            margin-bottom: 24px;
        }
        .stats-box {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(44,108,223,0.07);
            padding: 18px 24px;
            text-align: center;
            margin-bottom: 24px;
        }
        .stats-box h4 { color: #2d6cdf; }
    </style>
</head>
<body>
<div class="container mt-5">

    <div class="dashboard-header">
        <h2>Coordinator Dashboard</h2>
        <p>Attendance Management &mdash; {{ now()->format('F Y') }}</p>
        @if(auth()->guard('coordinator')->user()->major)
            <div style="margin-top: 1rem; padding: 0.5rem 1rem; background: rgba(255,255,255,0.2); border-radius: 6px; display: inline-block;">
                <i class="bi bi-mortarboard me-1"></i>
                <strong>Program:</strong> {{ auth()->guard('coordinator')->user()->major }}
            </div>
        @endif
    </div>

    {{-- Stats Section --}}
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

    {{-- Month Filter --}}
    <div class="card mb-4">
        <form method="GET" class="row g-3 align-items-center">
            <div class="col-auto">
                <label for="monthSelect" class="col-form-label">Select Month:</label>
            </div>
            <div class="col-auto">
                <input type="month" id="monthSelect" name="month" class="form-control"
                       value="{{ request('month', now()->format('Y-m')) }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>

    {{-- Program Filter Info --}}
    @if(auth()->guard('coordinator')->user()->major)
    <div class="alert alert-info mb-4" style="background: #e7f3ff; border-left: 4px solid #0d6efd; border-radius: 8px;">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Filtered by Program:</strong> You are viewing attendance logs for <strong>{{ auth()->guard('coordinator')->user()->major }}</strong> students only.
    </div>
    @endif

    {{-- Attendance Table --}}
    <div class="card">
        <h4 class="mb-3">Attendance Logs 
            @if(auth()->guard('coordinator')->user()->major)
                <small class="text-muted">({{ auth()->guard('coordinator')->user()->major }})</small>
            @endif
        </h4>
        @if(isset($logs) && $logs->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered mt-2">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Student No</th>
                            <th>Name</th>
                            <th>Morning Time In</th>
                            <th>Afternoon Time In</th>
                            <th>Status</th>
                            <th>Time Out</th>
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
                                        @if($log->is_late)
                                            <span class="badge bg-warning text-dark">{{ $log->time_in }}</span>
                                        @else
                                            <span class="badge bg-success">{{ $log->time_in }}</span>
                                        @endif
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
                                        @if($log->afternoon_is_late)
                                            <span class="badge bg-warning text-dark">{{ $log->afternoon_time_in }}</span>
                                        @else
                                            <span class="badge bg-success">{{ $log->afternoon_time_in }}</span>
                                        @endif
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
                                        $statuses = [];
                                        if ($log->time_in) {
                                            if ($log->is_late) {
                                                $statuses[] = '<span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Morning Late (' . $log->late_minutes . 'm)</span>';
                                            } else {
                                                $statuses[] = '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Morning On Time</span>';
                                            }
                                        }
                                        if ($log->afternoon_time_in) {
                                            if ($log->afternoon_is_late) {
                                                $statuses[] = '<span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Afternoon Late (' . $log->afternoon_late_minutes . 'm)</span>';
                                            } else {
                                                $statuses[] = '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Afternoon On Time</span>';
                                            }
                                        }
                                    @endphp
                                    @if(count($statuses) > 0)
                                        {!! implode('<br>', $statuses) !!}
                                    @else
                                        <span class="badge bg-secondary">No Time In</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $totalMinutes = 0;
                                        // Morning hours (from morning time-in to lunch break out)
                                        if ($log->time_in && $log->lunch_break_out) {
                                            $in = \Carbon\Carbon::parse($log->time_in);
                                            $out = \Carbon\Carbon::parse($log->lunch_break_out);
                                            $totalMinutes += abs($out->diffInMinutes($in));
                                        }
                                        // Afternoon hours (from afternoon time-in to end of day out)
                                        if ($log->afternoon_time_in && $log->time_out) {
                                            $in = \Carbon\Carbon::parse($log->afternoon_time_in);
                                            $out = \Carbon\Carbon::parse($log->time_out);
                                            $totalMinutes += abs($out->diffInMinutes($in));
                                        }
                                        $h = floor($totalMinutes / 60);
                                        $m = $totalMinutes % 60;
                                    @endphp
                                    @if($totalMinutes > 0)
                                        {{ $h }} hr {{ $m }} min
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
</body>
</html>

