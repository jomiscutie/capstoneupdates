<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4f46e5">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <title>Student Dashboard - NORSU OJT DTR</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <style>
        :root {
            --dtr-primary: #4f46e5;
            --dtr-primary-dark: #4338ca;
            --dtr-success: #059669;
            --dtr-success-dark: #047857;
            --dtr-danger: #dc2626;
            --dtr-danger-dark: #b91c1c;
            --dtr-muted: #64748b;
            --dtr-surface: #ffffff;
            --dtr-glass: rgba(255,255,255,0.75);
            --dtr-glass-border: rgba(255,255,255,0.4);
            --dtr-border: #e2e8f0;
            --dtr-radius: 1rem;
            --dtr-radius-lg: 1.25rem;
            --dtr-radius-xl: 1.5rem;
            --dtr-shadow: 0 1px 3px rgba(0,0,0,0.06);
            --dtr-shadow-md: 0 4px 20px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
            --dtr-shadow-lg: 0 20px 50px -15px rgba(79,70,229,0.3), 0 8px 20px -8px rgba(0,0,0,0.12);
            --dtr-glow: 0 0 40px -10px rgba(79,70,229,0.4);
            --dtr-font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --dtr-ease: cubic-bezier(0.34, 1.56, 0.64, 1);
            --dtr-ease-out: cubic-bezier(0.22, 1, 0.36, 1);
            --dtr-transition: 0.35s var(--dtr-ease-out);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: url('/images/negrosorientalstateuniversity_cover.jpg') center/cover fixed no-repeat;
            font-family: var(--dtr-font);
            padding: clamp(1.5rem, 4vw, 2.5rem) 0;
            min-height: 100vh;
            position: relative;
            color: #0f172a;
            line-height: 1.6;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: 
                radial-gradient(ellipse 120% 80% at 20% 0%, rgba(79,70,229,0.06) 0%, transparent 50%),
                linear-gradient(165deg, rgba(255,255,255,0.94) 0%, rgba(248,250,252,0.96) 50%, rgba(241,245,249,0.98) 100%);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            z-index: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 clamp(1rem, 3vw, 1.5rem);
            position: relative;
            z-index: 1;
        }

        /* Header Card — premium gradient + glass orbs */
        .header-card {
            background: linear-gradient(135deg, var(--dtr-primary) 0%, var(--dtr-primary-dark) 40%, #3730a3 100%);
            color: #fff;
            padding: clamp(2rem, 5vw, 2.75rem) clamp(1.5rem, 4vw, 2rem);
            border-radius: var(--dtr-radius-xl);
            box-shadow: var(--dtr-shadow-lg), inset 0 1px 0 rgba(255,255,255,0.15);
            margin-bottom: clamp(1.5rem, 4vw, 2rem);
            position: relative;
            overflow: hidden;
        }
        .header-card::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -15%;
            width: min(320px, 60vw);
            height: min(320px, 60vw);
            background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 65%);
            border-radius: 50%;
        }
        .header-card::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -10%;
            width: min(200px, 40vw);
            height: min(200px, 40vw);
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            border-radius: 50%;
        }
        .header-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }
        .header-content h2 {
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
            text-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-info {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        .header-info-item {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 500;
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        /* Card Section — glassmorphism + gradient border on hover */
        .card-section {
            background: var(--dtr-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: var(--dtr-radius-xl);
            box-shadow: var(--dtr-shadow-md), inset 0 1px 0 rgba(255,255,255,0.8);
            padding: clamp(1.5rem, 4vw, 2rem);
            margin-bottom: 1.5rem;
            transition: transform var(--dtr-transition), box-shadow var(--dtr-transition);
            border: 1px solid var(--dtr-glass-border);
            position: relative;
        }
        .card-section::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: inherit;
            padding: 1px;
            background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity var(--dtr-transition);
            pointer-events: none;
        }
        .card-section:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 48px -16px rgba(79,70,229,0.2), 0 12px 28px -8px rgba(0,0,0,0.1);
        }
        .card-section:hover::before { opacity: 0.6; }
        .card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(0,0,0,0.06);
        }
        .card-header h4 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.01em;
        }
        .card-header i {
            font-size: 1.4rem;
            color: var(--dtr-primary);
        }

        /* Time Display — glass tiles */
        .time-display {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .time-item {
            text-align: center;
            padding: 1.25rem 1rem;
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: var(--dtr-radius);
            border: 1px solid rgba(255,255,255,0.6);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.9), 0 2px 8px rgba(0,0,0,0.04);
            transition: transform var(--dtr-transition), box-shadow var(--dtr-transition);
        }
        .time-item:hover {
            transform: translateY(-2px);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.9), 0 8px 20px -6px rgba(79,70,229,0.15);
        }
        .time-item .label {
            font-size: 0.7rem;
            color: var(--dtr-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.35rem;
            font-weight: 600;
        }
        .time-item .value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
            font-variant-numeric: tabular-nums;
        }

        /* Action Buttons — gradient + glow */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }
        .btn-action {
            padding: 0.875rem 1.75rem;
            font-size: 0.95rem;
            border-radius: var(--dtr-radius);
            font-weight: 600;
            border: none;
            transition: transform var(--dtr-transition), box-shadow var(--dtr-transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 130px;
            justify-content: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        .btn-action::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.2) 0%, transparent 50%);
            pointer-events: none;
        }
        .btn-timein {
            background: linear-gradient(135deg, var(--dtr-success) 0%, var(--dtr-success-dark) 100%);
            color: #fff;
            box-shadow: 0 4px 16px rgba(5,150,105,0.4), inset 0 1px 0 rgba(255,255,255,0.2);
        }
        .btn-timein:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px -4px rgba(5,150,105,0.5), inset 0 1px 0 rgba(255,255,255,0.2);
            color: #fff;
        }
        .btn-timeout {
            background: linear-gradient(135deg, var(--dtr-danger) 0%, var(--dtr-danger-dark) 100%);
            color: #fff;
            box-shadow: 0 4px 16px rgba(220,38,38,0.4), inset 0 1px 0 rgba(255,255,255,0.2);
        }
        .btn-timeout:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px -4px rgba(220,38,38,0.5), inset 0 1px 0 rgba(255,255,255,0.2);
            color: #fff;
        }
        .btn-logout {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: #fff;
            box-shadow: var(--dtr-shadow-md), inset 0 1px 0 rgba(255,255,255,0.15);
        }
        .btn-logout:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -4px rgba(100,116,139,0.35);
            color: #fff;
        }

        /* Attendance Summary — glass tiles + accent */
        .attendance-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }
        .summary-item {
            padding: 1.25rem;
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: var(--dtr-radius);
            border: 1px solid rgba(255,255,255,0.6);
            border-left: 4px solid var(--dtr-primary);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.8), 0 2px 8px rgba(0,0,0,0.04);
            transition: transform var(--dtr-transition), box-shadow var(--dtr-transition);
        }
        .summary-item:hover {
            transform: translateY(-2px);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.8), 0 8px 20px -6px rgba(79,70,229,0.12);
        }
        .summary-item .label {
            font-size: 0.7rem;
            color: var(--dtr-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.35rem;
            font-weight: 600;
        }
        .summary-item .value {
            font-size: 1.35rem;
            font-weight: 700;
            color: #0f172a;
        }

        /* Table — refined header + row transition */
        .table-container {
            overflow-x: auto;
            border-radius: var(--dtr-radius-lg);
            border: 1px solid rgba(255,255,255,0.5);
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .table {
            margin: 0;
            border-radius: var(--dtr-radius-lg);
            overflow: hidden;
        }
        .table thead {
            background: linear-gradient(135deg, var(--dtr-primary) 0%, var(--dtr-primary-dark) 100%);
            color: #fff;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.15);
        }
        .table thead th {
            border: none;
            padding: 1rem 1.25rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.06em;
        }
        .table tbody td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: background 0.2s ease;
        }
        .table tbody tr:hover td {
            background: rgba(79,70,229,0.04);
        }
        .table tbody tr:last-child td { border-bottom: none; }

        /* Month Filter — glass */
        .month-filter {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            padding: 1rem 1.25rem;
            background: rgba(255,255,255,0.5);
            backdrop-filter: blur(12px);
            border-radius: var(--dtr-radius);
            border: 1px solid rgba(255,255,255,0.6);
        }

        /* Alerts */
        .alert {
            border-radius: var(--dtr-radius);
            border: none;
            padding: 1rem 1.25rem;
        }

        /* Face Verification Modal — premium */
        #faceVerificationModal .modal-content {
            border-radius: var(--dtr-radius-xl);
            border: 1px solid rgba(255,255,255,0.5);
            box-shadow: 0 32px 64px -12px rgba(0,0,0,0.25), 0 0 0 1px rgba(0,0,0,0.05);
        }
        #faceVerificationModal .modal-header {
            border-bottom: 1px solid var(--dtr-border);
            padding: 1.25rem 1.5rem;
        }
        #faceVerificationModal .modal-body {
            padding: 1.5rem;
        }
        #faceVerificationModal .modal-footer {
            border-top: 1px solid var(--dtr-border);
            padding: 1rem 1.5rem;
        }
        #faceVerificationModal .btn-primary {
            background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark));
            border: none;
            font-weight: 600;
            border-radius: var(--dtr-radius);
            box-shadow: 0 4px 14px rgba(79,70,229,0.4);
        }

        /* Offline / sync indicator */
        .offline-banner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: #fff;
            padding: 0.6rem 1rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: none;
        }
        .offline-banner.show { display: block; }
        .sync-toast {
            position: fixed;
            bottom: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            background: #059669;
            color: #fff;
            padding: 0.75rem 1.5rem;
            border-radius: var(--dtr-radius);
            font-weight: 600;
            box-shadow: var(--dtr-shadow-lg);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        .sync-toast.show { opacity: 1; }

        @media (max-width: 768px) {
            .header-info { flex-direction: column; gap: 0.75rem; }
            .time-display { grid-template-columns: 1fr; }
            .action-buttons { flex-direction: column; }
            .btn-action { width: 100%; }
            .container { padding: 0 1rem; }
        }
    </style>
    <script>
        function updateClock() {
            const now = new Date();
            const options = { timeZone: 'Asia/Manila', hour12: false };
            document.getElementById('clock').innerText = now.toLocaleTimeString('en-US', options);
            document.getElementById('day').innerText = now.toLocaleDateString('en-US', options);
            document.getElementById('month-year').innerText = now.toLocaleDateString('en-US', { month: 'long', year: 'numeric', timeZone: 'Asia/Manila' });
        }
        setInterval(updateClock, 1000);
        function scheduleMidnightReload() {
            const now = new Date();
            const midnight = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
            const msToMidnight = midnight - now;
            setTimeout(() => location.reload(), msToMidnight);
        }
        scheduleMidnightReload();
    </script>
</head>
<body>
<div id="offlineBanner" class="offline-banner" aria-hidden="true">
    <i class="bi bi-wifi-off me-2"></i>You're offline. Time-in/out will be saved and synced when you're back online.
</div>
<div id="syncToast" class="sync-toast" aria-live="polite">
    <i class="bi bi-cloud-check me-2"></i>Offline records synced.
</div>
<div class="container">

    @if(auth()->guard('student')->check())
        <!-- Header Card -->
        <div class="header-card">
            <div class="header-content">
                <h2><i class="bi bi-person-badge me-2"></i>Student Dashboard</h2>
                <p style="font-size: 1.1rem; opacity: 0.95;">Welcome back, <strong>{{ auth()->guard('student')->user()->name }}</strong></p>
                <div class="header-info">
                    <div class="header-info-item">
                        <i class="bi bi-card-text"></i>
                        <span><strong>ID:</strong> {{ auth()->guard('student')->user()->student_no }}</span>
                    </div>
                    <div class="header-info-item">
                        <i class="bi bi-mortarboard"></i>
                        <span><strong>Program:</strong> {{ auth()->guard('student')->user()->course }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Time & Actions Card -->
        <div class="card-section">
            <div class="card-header">
                <i class="bi bi-clock-history"></i>
                <h4>Time & Attendance</h4>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                </div>
            @endif

            <div class="time-display">
                <div class="time-item">
                    <div class="label">Today</div>
                    <div class="value" id="day">-</div>
                </div>
                <div class="time-item">
                    <div class="label">Current Time</div>
                    <div class="value" id="clock">-</div>
                </div>
                <div class="time-item">
                    <div class="label">Month & Year</div>
                    <div class="value" id="month-year">-</div>
                </div>
            </div>

            <div class="action-buttons">
                <button type="button" class="btn btn-action btn-timein" onclick="openFaceVerification('timein')">
                    <i class="bi bi-check-circle"></i>Time In
                </button>
                <button type="button" class="btn btn-action btn-timeout" onclick="openFaceVerification('timeout')">
                    <i class="bi bi-x-circle"></i>Time Out
                </button>
                <a href="{{ route('student.password.change') }}" class="btn btn-action" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%); color: #fff; border: none;">
                    <i class="bi bi-key"></i>Change password
                </a>
                <form action="{{ route('student.logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-action btn-logout">
                        <i class="bi bi-box-arrow-right"></i>Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Today's Attendance Summary -->
        <div class="card-section">
            <div class="card-header">
                <i class="bi bi-calendar-check"></i>
                <h4>Today's Attendance</h4>
            </div>
            @if(isset($attendance) && $attendance)
                @if($attendance->is_late || $attendance->afternoon_is_late)
                <div class="alert alert-warning mb-3" style="border-radius: 12px; border-left: 4px solid #ffc107;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Late Arrival:</strong> 
                    @if($attendance->is_late && $attendance->afternoon_is_late)
                        Morning: {{ $attendance->late_minutes }}m late | Afternoon: {{ $attendance->afternoon_late_minutes }}m late
                    @elseif($attendance->is_late)
                        Morning: {{ $attendance->late_minutes }} minute(s) late
                    @elseif($attendance->afternoon_is_late)
                        Afternoon: {{ $attendance->afternoon_late_minutes }} minute(s) late
                    @endif
                </div>
                @endif
                <div class="attendance-summary-grid">
                    <div class="summary-item">
                        <div class="label">Morning Time In</div>
                        <div class="value">
                            @if($attendance->time_in)
                                @if($attendance->is_late)
                                    <span class="badge bg-warning text-dark me-1">{{ $attendance->time_in }}</span>
                                @else
                                    <span class="badge bg-success">{{ $attendance->time_in }}</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Afternoon Time In</div>
                        <div class="value">
                            @if($attendance->afternoon_time_in)
                                @if($attendance->afternoon_is_late)
                                    <span class="badge bg-warning text-dark me-1">{{ $attendance->afternoon_time_in }}</span>
                                @else
                                    <span class="badge bg-success">{{ $attendance->afternoon_time_in }}</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Time Out</div>
                        <div class="value">{{ $attendance->time_out ?? '-' }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Hours Rendered</div>
                        <div class="value">
                            @php
                                $totalMinutes = 0;
                                if ($attendance->time_in && $attendance->time_out) {
                            $in = \Carbon\Carbon::parse($attendance->time_in);
                            $out = \Carbon\Carbon::parse($attendance->time_out);
                                    $totalMinutes += abs($out->diffInMinutes($in));
                                }
                                if ($attendance->afternoon_time_in && $attendance->time_out) {
                                    $in = \Carbon\Carbon::parse($attendance->afternoon_time_in);
                                    $out = \Carbon\Carbon::parse($attendance->time_out);
                                    $totalMinutes += abs($out->diffInMinutes($in));
                                }
                                $hours = floor($totalMinutes / 60);
                                $minutes = $totalMinutes % 60;
                        @endphp
                            @if($totalMinutes > 0)
                                {{ $hours }}h {{ $minutes }}m
                    @else
                                0h 0m
                    @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x" style="font-size: 3rem; color: #adb5bd;"></i>
                    <p class="text-muted mt-3 mb-0">No attendance record for today</p>
                </div>
            @endif
        </div>

        <!-- Attendance Logs -->
        <div class="card-section">
            <div class="card-header">
                <i class="bi bi-list-ul"></i>
                <h4>Attendance History</h4>
            </div>

            <div class="month-filter">
                <label for="monthSelect" class="mb-0" style="font-weight: 600; color: #495057;">
                    <i class="bi bi-calendar3 me-2"></i>Select Month:
                </label>
                <form method="GET" class="d-flex gap-2 flex-grow-1" style="max-width: 300px;">
                    <input type="month" id="monthSelect" name="month" class="form-control"
                           value="{{ $selectedMonth }}">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>

            @if($logs->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
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
                                    <td>
                                        <i class="bi bi-calendar3 me-2"></i>
                                        {{ \Carbon\Carbon::parse($log->date)->format('F d, Y') }}
                                    </td>
                                    <td>
                                        @if($log->time_in)
                                            @if($log->is_late)
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-clock me-1"></i>{{ $log->time_in }}
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="bi bi-clock me-1"></i>{{ $log->time_in }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->afternoon_time_in)
                                            @if($log->afternoon_is_late)
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-clock me-1"></i>{{ $log->afternoon_time_in }}
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="bi bi-clock me-1"></i>{{ $log->afternoon_time_in }}
                                                </span>
                                            @endif
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
                                        @if($log->time_out)
                                            <span class="badge bg-danger">
                                                <i class="bi bi-clock me-1"></i>{{ $log->time_out }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $totalMinutes = 0;
                                            if ($log->time_in && $log->time_out) {
                                                $in = \Carbon\Carbon::parse($log->time_in);
                                                $out = \Carbon\Carbon::parse($log->time_out);
                                                $totalMinutes += abs($out->diffInMinutes($in));
                                            }
                                            if ($log->afternoon_time_in && $log->time_out) {
                                                $in = \Carbon\Carbon::parse($log->afternoon_time_in);
                                                $out = \Carbon\Carbon::parse($log->time_out);
                                                $totalMinutes += abs($out->diffInMinutes($in));
                                            }
                                            $h = floor($totalMinutes / 60);
                                            $m = $totalMinutes % 60;
                                            @endphp
                                        @if($totalMinutes > 0)
                                            <span class="badge bg-info" style="font-size: 0.9rem;">
                                                {{ $h }}h {{ $m }}m
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #adb5bd;"></i>
                    <p class="text-muted mt-3 mb-0">No attendance logs found for this month</p>
                </div>
            @endif
        </div>
    @else
        <div class="card text-center mt-5 p-5">
            <h2>Welcome, Guest</h2>
            <p>Please <a href="{{ route('student.login') }}">Login</a> or <a href="{{ route('student.register') }}">Register</a> to access your dashboard.</p>
        </div>
    @endif

    <script>updateClock();</script>
</div>

<!-- Face Verification Modal -->
<div class="modal fade" id="faceVerificationModal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="faceVerificationModalLabel" aria-describedby="faceVerificationModalDesc" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="faceVerificationModalLabel">Face Verification Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close modal" onclick="stopFaceVerification()"></button>
            </div>
            <div class="modal-body text-center">
                <p id="faceVerificationModalDesc" class="mb-3">Please look directly at the camera and ensure good lighting. When your face is detected and verified, use the Verify & Submit button or press Escape to cancel.</p>
                <div class="position-relative d-inline-block">
                    <video id="faceVideo" autoplay playsinline style="width: 100%; max-width: 640px; border-radius: 10px;"></video>
                    <canvas id="faceCanvas" style="position: absolute; top: 0; left: 0; width: 100%; max-width: 640px;"></canvas>
                </div>
                <div id="verificationStatus" class="mt-3">
                    <p class="text-muted">Initializing camera...</p>
                </div>
                <div id="livenessStatus" class="mt-2">
                    <small class="text-info">Blink detection: <span id="blinkCount">0</span> blinks detected</small>
                </div>
                <form id="faceVerificationForm" method="POST" style="display: none;">
                    @csrf
                    <input type="hidden" name="face_encoding" id="faceEncodingInput">
                    <input type="hidden" name="action_type" id="actionTypeInput">
                    <input type="hidden" name="recorded_at" id="recordedAtInput">
                    <input type="hidden" name="verification_confidence" id="verificationConfidenceInput">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="stopFaceVerification()" aria-label="Cancel and close">Cancel</button>
                <button type="button" class="btn btn-primary" id="verifyFaceBtn" onclick="verifyAndSubmit()" disabled aria-label="Verify face and submit time in or time out">Verify & Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- Face API (local for offline support) -->
<script>window.FACE_API_MODEL_BASE = "{{ asset('vendor/face-api/model') }}";</script>
<script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
<script src="{{ asset('js/face-recognition.js') }}"></script>
<script src="{{ asset('js/offline-queue.js') }}"></script>

<script>
let currentAction = '';
let verificationInterval = null;
let faceModalTriggerButton = null;

async function openFaceVerification(action) {
    currentAction = action;
    faceModalTriggerButton = document.activeElement || document.querySelector('[onclick*="openFaceVerification(\'' + action + '\')"]');
    document.getElementById('actionTypeInput').value = action;
    document.getElementById('verificationStatus').innerHTML = '<p class="text-muted">Loading face recognition models...</p>';
    document.getElementById('verifyFaceBtn').disabled = true;
    
    const modalEl = document.getElementById('faceVerificationModal');
    const modal = new bootstrap.Modal(modalEl);
    modalEl.addEventListener('shown.bs.modal', function onShown() {
        modalEl.removeEventListener('shown.bs.modal', onShown);
        var firstFocusable = modalEl.querySelector('button:not([disabled])');
        if (firstFocusable) firstFocusable.focus();
    }, { once: true });
    modalEl.addEventListener('hidden.bs.modal', function onHidden() {
        modalEl.removeEventListener('hidden.bs.modal', onHidden);
        if (faceModalTriggerButton && typeof faceModalTriggerButton.focus === 'function') {
            faceModalTriggerButton.focus();
        }
        faceModalTriggerButton = null;
    }, { once: true });
    modal.show();
    
    // Load models
    const modelsLoaded = await faceRecognition.loadModels();
    if (!modelsLoaded) {
        document.getElementById('verificationStatus').innerHTML = '<p class="text-danger">Failed to load face recognition models. Please refresh the page.</p>';
        return;
    }
    
    // Initialize camera
    const video = document.getElementById('faceVideo');
    const canvas = document.getElementById('faceCanvas');
    
    const cameraReady = await faceRecognition.initializeCamera(video, canvas);
    if (!cameraReady) {
        document.getElementById('verificationStatus').innerHTML = '<p class="text-danger">Camera access denied. Please allow camera permissions.</p>';
        return;
    }
    
    // Reset liveness detection
    faceRecognition.resetLiveness();
    document.getElementById('blinkCount').textContent = '0';
    
    let startTime = Date.now();
    const maxWaitTime = 10000; // 10 seconds max wait
    
    // Start face detection (faster interval: 300ms instead of 500ms)
    document.getElementById('verificationStatus').innerHTML = '<p class="text-info">Detecting face... Please look at the camera.</p>';
    
    verificationInterval = setInterval(async () => {
        const detection = await faceRecognition.detectFace();
        const elapsed = Date.now() - startTime;
        
        if (detection) {
            const blinkCount = faceRecognition.blinkCount;
            document.getElementById('blinkCount').textContent = blinkCount;
            
            // Check liveness with detection object for stability check
            const isLive = faceRecognition.checkLiveness(detection);
            
            if (isLive) {
                document.getElementById('verificationStatus').innerHTML = '<p class="text-success"><i class="bi bi-check-circle me-2"></i>Face verified! Ready to submit.</p>';
                var btn = document.getElementById('verifyFaceBtn');
                btn.disabled = false;
                clearInterval(verificationInterval);
                btn.focus();
            } else if (blinkCount > 0) {
                document.getElementById('verificationStatus').innerHTML = '<p class="text-info"><i class="bi bi-eye me-2"></i>Face detected! Please blink once or hold still.</p>';
            } else {
                document.getElementById('verificationStatus').innerHTML = '<p class="text-success"><i class="bi bi-check-circle me-2"></i>Face detected! Please blink once or hold still for 2 seconds.</p>';
            }
            
            // Enhanced timeout: Require liveness before allowing verification
            // This prevents photo spoofing
            if (elapsed > maxWaitTime && !isLive) {
                document.getElementById('verificationStatus').innerHTML = '<p class="text-warning"><i class="bi bi-exclamation-triangle me-2"></i>Liveness check required. Please blink twice or hold still for 2 seconds.</p>';
                // Don't enable button - require liveness for security
            }
        } else {
            document.getElementById('verificationStatus').innerHTML = '<p class="text-warning"><i class="bi bi-exclamation-triangle me-2"></i>No face detected. Please position yourself in front of the camera.</p>';
            document.getElementById('blinkCount').textContent = '0';
        }
    }, 300); // Faster detection interval
}

async function verifyAndSubmit() {
    document.getElementById('verifyFaceBtn').disabled = true;
    document.getElementById('verificationStatus').innerHTML = '<p class="text-info">Verifying face...</p>';
    
    try {
        const student = @json(auth()->guard('student')->user());
        if (!student.face_encoding) {
            alert('Face not registered. Please contact administrator.');
            stopFaceVerification();
            return;
        }
        
        const verification = await faceRecognition.verifyFace(student.face_encoding);
        
        if (verification.verified) {
            const encoding = verification.encoding;
            const recordedAt = new Date().toISOString();
            const form = document.getElementById('faceVerificationForm');
            const token = form.querySelector('input[name="_token"]').value;
            const timeInUrl = '{{ route("student.timein") }}';
            const timeOutUrl = '{{ route("student.timeout") }}';

            if (typeof window.DtrOfflineQueue !== 'undefined' && !window.DtrOfflineQueue.isOnline()) {
                var confidence = verification.confidence;
                window.DtrOfflineQueue.addPending({
                    action_type: currentAction,
                    face_encoding: encoding,
                    recorded_at: recordedAt,
                    _token: token,
                    time_in_url: timeInUrl,
                    time_out_url: timeOutUrl,
                    verification_confidence: confidence
                }).then(function() {
                    stopFaceVerification();
                    bootstrap.Modal.getInstance(document.getElementById('faceVerificationModal')).hide();
                    showOfflineRecordedMessage(confidence);
                }).catch(function(err) {
                    console.error('Offline queue add failed', err);
                    document.getElementById('verificationStatus').innerHTML = '<p class="text-danger">Could not save offline. Try again when online.</p>';
                    document.getElementById('verifyFaceBtn').disabled = false;
                });
                return;
            }

            document.getElementById('faceEncodingInput').value = encoding;
            document.getElementById('recordedAtInput').value = recordedAt;
            document.getElementById('verificationConfidenceInput').value = verification.confidence;
            form.action = currentAction === 'timein' ? timeInUrl : timeOutUrl;
            form.submit();
        } else {
            let errorMsg = '<p class="text-danger"><i class="bi bi-exclamation-triangle me-2"></i><strong>Face Verification Failed</strong></p>';
            errorMsg += '<p class="text-muted small">Distance: ' + verification.distance.toFixed(2) + ' (threshold: 0.4)</p>';
            errorMsg += '<p class="text-muted small">Confidence: ' + verification.confidence + '%</p>';
            errorMsg += '<p class="text-muted small">Matches: ' + (verification.matchRatio * 100).toFixed(0) + '% (' + verification.attempts + ' attempts)</p>';
            errorMsg += '<p class="text-warning mt-2"><small>Please ensure:<br>• You are using your own registered face<br>• Good lighting conditions<br>• Looking directly at the camera<br>• No other faces in the frame</small></p>';
            document.getElementById('verificationStatus').innerHTML = errorMsg;
            document.getElementById('verifyFaceBtn').disabled = false;
            
            // Reset liveness for retry
            faceRecognition.resetLiveness();
            document.getElementById('blinkCount').textContent = '0';
        }
    } catch (error) {
        console.error('Verification error:', error);
        document.getElementById('verificationStatus').innerHTML = '<p class="text-danger">Error during verification. Please try again.</p>';
        document.getElementById('verifyFaceBtn').disabled = false;
    }
}

function stopFaceVerification() {
    if (verificationInterval) {
        clearInterval(verificationInterval);
        verificationInterval = null;
    }
    faceRecognition.stopCamera();
    faceRecognition.resetLiveness();
}

function showOfflineRecordedMessage(confidence) {
    var matchText = (confidence != null && confidence !== '') ? ' — ' + confidence + '% match' : '';
    var alert = document.createElement('div');
    alert.className = 'alert alert-info';
    alert.innerHTML = '<i class="bi bi-cloud-download me-2"></i><strong>Recorded offline' + matchText + '.</strong> Your ' + (currentAction === 'timein' ? 'Time In' : 'Time Out') + ' will sync when you are back online.';
    var card = document.querySelector('.card-section');
    if (card && card.querySelector('.alert')) {
        card.insertBefore(alert, card.querySelector('.alert'));
            } else if (card) {
        card.insertBefore(alert, card.firstChild);
            }
    setTimeout(function() {
        if (alert.parentNode) alert.remove();
    }, 8000);
}

function updateOfflineBanner() {
    var banner = document.getElementById('offlineBanner');
    if (!banner) return;
    if (typeof navigator !== 'undefined' && !navigator.onLine) {
        banner.classList.add('show');
        banner.setAttribute('aria-hidden', 'false');
    } else {
        banner.classList.remove('show');
        banner.setAttribute('aria-hidden', 'true');
    }
}

function showSyncToast() {
    var toast = document.getElementById('syncToast');
    if (toast) {
        toast.classList.add('show');
        setTimeout(function() {
            toast.classList.remove('show');
            window.location.reload();
        }, 2500);
    }
}

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('{{ asset("sw.js") }}').then(function() {
        console.log('SW registered');
    }).catch(function(err) {
        console.warn('SW registration failed', err);
    });
}

window.addEventListener('online', updateOfflineBanner);
window.addEventListener('offline', updateOfflineBanner);
updateOfflineBanner();

window.dtrOfflineQueueOnSynced = showSyncToast;

// Sync any pending offline actions when dashboard loads while online
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.DtrOfflineQueue === 'undefined' || !window.DtrOfflineQueue.isOnline()) return;
    window.DtrOfflineQueue.getAllPending().then(function(items) {
        if (items.length) {
            return window.DtrOfflineQueue.processQueue().then(function() {
                window.location.reload();
            });
        }
    }).catch(function() {});
});

// Session timeout: after 30 min inactivity, redirect to login with message
(function() {
    var timeoutMinutes = 30;
    var timeoutMs = timeoutMinutes * 60 * 1000;
    var logoutUrl = '{{ route("student.login") }}';
    var timer = null;
    var lastActivity = Date.now();
    var throttleMs = 60000;
    var lastThrottle = 0;

    function resetTimer() {
        lastActivity = Date.now();
        if (timer) clearTimeout(timer);
        timer = setTimeout(function() {
            if (document.visibilityState === 'visible') {
                alert('Your session has expired due to inactivity. Please log in again.');
                window.location.href = logoutUrl;
            }
        }, timeoutMs);
    }

    function onActivity(throttled) {
        if (throttled) {
            var now = Date.now();
            if (now - lastThrottle < throttleMs) return;
            lastThrottle = now;
        }
        resetTimer();
    }

    document.addEventListener('click', function() { onActivity(false); }, true);
    document.addEventListener('keydown', function() { onActivity(false); }, true);
    document.addEventListener('scroll', function() { onActivity(true); }, { passive: true });
    document.addEventListener('mousemove', function() { onActivity(true); }, { passive: true });
    resetTimer();
})();
</script>
</body>
</html>
