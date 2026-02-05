<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Logs - NORSU OJT DTR</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <style>
        :root {
            --dtr-primary: #2563eb;
            --dtr-primary-dark: #1d4ed8;
            --dtr-muted: #64748b;
            --dtr-surface: #ffffff;
            --dtr-bg: #f8fafc;
            --dtr-border: #e2e8f0;
            --dtr-radius: 1rem;
            --dtr-radius-lg: 1.25rem;
            --dtr-shadow: 0 1px 3px rgba(0,0,0,0.06);
            --dtr-shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --dtr-shadow-lg: 0 10px 40px -10px rgba(37,99,235,0.25);
            --dtr-font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --dtr-transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: url('/images/negrosorientalstateuniversity_cover.jpg') center/cover fixed no-repeat;
            font-family: var(--dtr-font);
            min-height: 100vh;
            position: relative;
            color: #0f172a;
            line-height: 1.6;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse 120% 80% at 80% 0%, rgba(37,99,235,0.06) 0%, transparent 50%),
                linear-gradient(165deg, rgba(255,255,255,0.94) 0%, rgba(248,250,252,0.96) 100%);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            z-index: 0;
        }
        .container {
            position: relative;
            z-index: 1;
            padding: clamp(1.5rem, 4vw, 2.5rem) clamp(1rem, 3vw, 1.5rem);
            max-width: 1400px;
            margin: 0 auto;
        }
        .dashboard-header {
            background: linear-gradient(135deg, var(--dtr-primary) 0%, var(--dtr-primary-dark) 40%, #1e40af 100%);
            color: #fff;
            border-radius: 1.25rem;
            padding: clamp(1.75rem, 4vw, 2.25rem) clamp(1.5rem, 4vw, 2rem);
            box-shadow: 0 20px 50px -15px rgba(37,99,235,0.35), inset 0 1px 0 rgba(255,255,255,0.15);
            text-align: center;
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden;
        }
        .dashboard-header::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -15%;
            width: min(280px, 50vw);
            height: min(280px, 50vw);
            background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 65%);
            border-radius: 50%;
        }
        .dashboard-header::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -10%;
            width: min(180px, 35vw);
            height: min(180px, 35vw);
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            border-radius: 50%;
        }
        .dashboard-header h2 {
            position: relative;
            z-index: 1;
            font-size: clamp(1.35rem, 3vw, 1.75rem);
            font-weight: 700;
            margin-bottom: 0.35rem;
            letter-spacing: -0.02em;
        }
        .dashboard-header p {
            position: relative;
            z-index: 1;
            opacity: 0.95;
            font-size: 0.95rem;
        }
        .stats-box {
            background: rgba(255,255,255,0.78);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: var(--dtr-radius);
            box-shadow: 0 4px 20px rgba(0,0,0,0.06), inset 0 1px 0 rgba(255,255,255,0.9);
            padding: 1.25rem 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255,255,255,0.5);
            transition: transform 0.35s cubic-bezier(0.22,1,0.36,1), box-shadow 0.35s ease;
        }
        .stats-box:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px -12px rgba(37,99,235,0.2);
        }
        .stats-box h4 {
            color: var(--dtr-primary);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
        .stats-box .fs-3 { font-variant-numeric: tabular-nums; }
        .card {
            border-radius: 1.25rem;
            padding: 1.5rem 1.75rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06), inset 0 1px 0 rgba(255,255,255,0.8);
            border: 1px solid rgba(255,255,255,0.5);
            background: rgba(255,255,255,0.78);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .card h4 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1rem;
        }
        .table-responsive {
            border-radius: var(--dtr-radius);
            overflow: hidden;
            border: 1px solid var(--dtr-border);
        }
        .table {
            margin: 0;
        }
        .table thead {
            background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark));
            color: #fff;
        }
        .table thead th {
            padding: 1rem 1.25rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.06em;
            border: none;
            vertical-align: middle;
        }
        .table tbody td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--dtr-border);
        }
        .table tbody tr:hover { background: #f8fafc; }
        .table tbody tr:last-child td { border-bottom: none; }
        .form-control, .form-select {
            border-radius: var(--dtr-radius);
            border: 1px solid var(--dtr-border);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark));
            border: none;
            font-weight: 600;
            border-radius: var(--dtr-radius);
            padding: 0.5rem 1.25rem;
            transition: transform var(--dtr-transition), box-shadow var(--dtr-transition);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37,99,235,0.35);
        }
        .alert-info {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-left: 4px solid var(--dtr-primary);
            border-radius: var(--dtr-radius);
            border: 1px solid #bfdbfe;
        }
    </style>
</head>
<body>
@php
    $coordinator = auth()->guard('coordinator')->user();
    $major = $coordinator->major ?? null;
@endphp
<div class="container mt-5">

    <div class="dashboard-header">
        <h2>Coordinator Dashboard</h2>
        <p>Attendance Management &mdash; {{ now()->format('F Y') }}</p>
        @if($major)
            <div style="margin-top: 1rem; padding: 0.5rem 1rem; background: rgba(255,255,255,0.2); border-radius: 6px; display: inline-block;">
                <i class="bi bi-mortarboard me-1"></i>
                <strong>Program:</strong> {{ $major }}
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

    @if($major)
    <div class="alert alert-info mb-4" style="background: #e7f3ff; border-left: 4px solid #0d6efd; border-radius: 8px;">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Filtered by Program:</strong> You are viewing attendance logs for <strong>{{ $major }}</strong> students only.
    </div>
    @endif

    {{-- Attendance Table --}}
    <div class="card">
        <h4 class="mb-3">Attendance Logs
            @if($major)<small class="text-muted">({{ $major }})</small>@endif
        </h4>
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
                                        $totalMinutes = 0;
                                        if ($log->time_in && $log->lunch_break_out) {
                                            $totalMinutes += \Carbon\Carbon::parse($log->lunch_break_out)->diffInMinutes(\Carbon\Carbon::parse($log->time_in));
                                        }
                                        if ($log->afternoon_time_in && $log->time_out) {
                                            $totalMinutes += \Carbon\Carbon::parse($log->time_out)->diffInMinutes(\Carbon\Carbon::parse($log->afternoon_time_in));
                                        }
                                    @endphp
                                    @if($totalMinutes > 0)
                                        {{ floor($totalMinutes / 60) }} hr {{ $totalMinutes % 60 }} min
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

