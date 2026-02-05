<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinator Dashboard - NORSU OJT DTR</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <style>
        :root {
            --dtr-primary: #2563eb;
            --dtr-primary-dark: #1d4ed8;
            --dtr-success: #059669;
            --dtr-danger: #dc2626;
            --dtr-warning: #d97706;
            --dtr-muted: #64748b;
            --dtr-surface: #ffffff;
            --dtr-glass: rgba(255,255,255,0.75);
            --dtr-glass-border: rgba(255,255,255,0.4);
            --dtr-border: #e2e8f0;
            --dtr-radius: 1rem;
            --dtr-radius-lg: 1.25rem;
            --dtr-radius-xl: 1.5rem;
            --dtr-shadow-md: 0 4px 20px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
            --dtr-shadow-lg: 0 20px 50px -15px rgba(37,99,235,0.3), 0 8px 20px -8px rgba(0,0,0,0.12);
            --dtr-font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
            background: radial-gradient(ellipse 120% 80% at 80% 0%, rgba(37,99,235,0.06) 0%, transparent 50%),
                linear-gradient(165deg, rgba(255,255,255,0.94) 0%, rgba(248,250,252,0.96) 100%);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            z-index: 0;
        }
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 clamp(1rem, 3vw, 1.5rem);
            position: relative;
            z-index: 1;
        }

        /* Header — premium gradient + orbs */
        .header-card {
            background: linear-gradient(135deg, var(--dtr-primary) 0%, var(--dtr-primary-dark) 40%, #1e40af 100%);
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        .header-info h1 {
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 700;
            margin-bottom: 0.35rem;
            letter-spacing: -0.02em;
            text-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-info p { font-size: 0.95rem; opacity: 0.95; margin: 0.2rem 0; }
        .program-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.75rem;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .btn-logout {
            padding: 0.625rem 1.5rem;
            font-size: 0.9rem;
            border-radius: var(--dtr-radius);
            border: 2px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.15);
            color: #fff;
            transition: transform var(--dtr-transition), background var(--dtr-transition), border-color var(--dtr-transition);
            font-weight: 600;
            cursor: pointer;
        }
        .btn-logout:hover {
            background: rgba(255,255,255,0.25);
            border-color: rgba(255,255,255,0.5);
            transform: translateY(-3px);
            color: #fff;
        }

        /* Info Alert — glass */
        .info-alert {
            background: rgba(239,246,255,0.85);
            backdrop-filter: blur(12px);
            border-left: 4px solid var(--dtr-primary);
            border-radius: var(--dtr-radius);
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 12px rgba(37,99,235,0.08);
            border: 1px solid rgba(191,219,254,0.6);
        }

        /* Stats Grid — glass cards + gradient border on hover */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--dtr-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 1.75rem;
            border-radius: var(--dtr-radius-xl);
            box-shadow: var(--dtr-shadow-md), inset 0 1px 0 rgba(255,255,255,0.8);
            transition: transform var(--dtr-transition), box-shadow var(--dtr-transition);
            position: relative;
            overflow: hidden;
            border: 1px solid var(--dtr-glass-border);
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            transition: width var(--dtr-transition);
        }
        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 48px -16px rgba(37,99,235,0.2), 0 12px 28px -8px rgba(0,0,0,0.1);
        }
        .stat-card:hover::before { width: 100%; opacity: 0.08; }
        .stat-card.primary::before { background: var(--dtr-primary); }
        .stat-card.success::before { background: var(--dtr-success); }
        .stat-card.danger::before { background: var(--dtr-danger); }
        .stat-card.warning::before { background: var(--dtr-warning); }
        .stat-card.warning .stat-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--dtr-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            color: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .stat-card.primary .stat-icon {
            background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark));
        }
        .stat-card.success .stat-icon {
            background: linear-gradient(135deg, var(--dtr-success), #047857);
        }
        .stat-card.danger .stat-icon {
            background: linear-gradient(135deg, var(--dtr-danger), #b91c1c);
        }
        .stat-card .number {
            font-size: clamp(2rem, 4vw, 2.75rem);
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.25rem;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }
        .stat-card .label {
            font-size: 0.8rem;
            color: var(--dtr-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .stat-card .sub-label { font-size: 0.7rem; color: #94a3b8; margin-top: 0.2rem; }

        /* Action Cards — glass + gradient border */
        .action-card {
            background: var(--dtr-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 2rem;
            border-radius: var(--dtr-radius-xl);
            box-shadow: var(--dtr-shadow-md), inset 0 1px 0 rgba(255,255,255,0.8);
            text-align: center;
            border: 1px solid var(--dtr-glass-border);
            transition: transform var(--dtr-transition), box-shadow var(--dtr-transition);
        }
        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 48px -16px rgba(37,99,235,0.2);
        }
        .btn-primary-custom {
            padding: 1rem 2rem;
            font-size: 1rem;
            border-radius: var(--dtr-radius);
            background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark));
            color: #fff;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: transform var(--dtr-transition), box-shadow var(--dtr-transition);
            font-weight: 600;
            box-shadow: 0 4px 16px rgba(37,99,235,0.4), inset 0 1px 0 rgba(255,255,255,0.2);
            cursor: pointer;
        }
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px -4px rgba(37,99,235,0.5), inset 0 1px 0 rgba(255,255,255,0.2);
            color: #fff;
        }
        .btn-primary-custom i { font-size: 1.15rem; }

        @media (max-width: 768px) {
            .header-content { flex-direction: column; align-items: flex-start; }
            .header-info h1 { font-size: 1.5rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .dashboard-container { padding: 0 1rem; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header Card -->
        <div class="header-card">
            <div class="header-content">
                <div class="header-info">
                    <h1><i class="bi bi-speedometer2 me-2"></i>Coordinator Dashboard</h1>
                    <p>Welcome back, <strong>{{ auth()->guard('coordinator')->user()->name }}</strong></p>
                    @if(auth()->guard('coordinator')->user()->major)
                        <div class="program-badge">
                            <i class="bi bi-mortarboard"></i>
                            <span>Managing: <strong>{{ auth()->guard('coordinator')->user()->major }}</strong></span>
                        </div>
                    @endif
                </div>
        <form action="{{ route('coordinator.logout') }}" method="POST">
            @csrf
                    <button type="submit" class="btn-logout">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </button>
        </form>
    </div>
        </div>

        <!-- Program Info Alert -->
        @if(auth()->guard('coordinator')->user()->major)
        <div class="info-alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-3" style="font-size: 1.5rem; color: #0d6efd;"></i>
                <div>
                    <strong>Program Filter Active:</strong> You are viewing data exclusively for 
                    <strong>{{ auth()->guard('coordinator')->user()->major }}</strong> students. 
                    Students from other programs are not visible in your dashboard.
                </div>
            </div>
        </div>
        @endif

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-card-header">
                    <div>
                        <div class="label">Total Students</div>
                        <div class="sub-label">{{ auth()->guard('coordinator')->user()->major ?? 'All Programs' }}</div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                <div class="number">{{ $totalStudents }}</div>
            </div>

            <div class="stat-card success">
                <div class="stat-card-header">
                    <div>
                        <div class="label">Present Today</div>
                        <div class="sub-label">Timed In</div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-check-circle-fill"></i>
            </div>
        </div>
                <div class="number">{{ $studentsTimedIn }}</div>
            </div>

            <div class="stat-card danger">
                <div class="stat-card-header">
                    <div>
                        <div class="label">Absent Today</div>
                        <div class="sub-label">Not Timed In</div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-x-circle-fill"></i>
            </div>
        </div>
                <div class="number">{{ $studentsNotTimedIn }}</div>
            </div>

            <div class="stat-card warning">
                <div class="stat-card-header">
                    <div>
                        <div class="label">Late Arrivals</div>
                        <div class="sub-label">Today</div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-clock-history"></i>
            </div>
                </div>
                <div class="number">{{ $lateArrivalsToday ?? 0 }}</div>
        </div>
    </div>

        <!-- Action Cards -->
        <div class="row g-3">
            <div class="col-md-4">
                <div class="action-card" style="border-left: 4px solid var(--dtr-warning);">
                    <h3 class="mb-3" style="color: #212529; font-weight: 600;">
                        <i class="bi bi-person-check me-2"></i>Verify Students
                    </h3>
                    <p class="text-muted mb-4">Students who register under your program must be verified by you before they can log in. Confirm they belong to your class or reject if not.</p>
                    <a href="{{ route('coordinator.pending.verification') }}" class="btn-primary-custom" style="background: linear-gradient(135deg, #d97706, #b45309); box-shadow: 0 4px 15px rgba(217, 119, 6, 0.35);">
                        <i class="bi bi-person-check"></i>
                        {{ ($pendingVerificationCount ?? 0) > 0 ? 'Pending Verification (' . $pendingVerificationCount . ')' : 'Verify Students' }}
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="action-card">
                    <h3 class="mb-3" style="color: #212529; font-weight: 600;">
                        <i class="bi bi-clock-history me-2"></i>Attendance Management
                    </h3>
                    <p class="text-muted mb-4">View detailed attendance logs for your program</p>
                    <a href="{{ route('coordinator.attendance.logs') }}" class="btn-primary-custom">
                        <i class="bi bi-list-ul"></i>
                        View Attendance Logs
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="action-card">
                    <h3 class="mb-3" style="color: #212529; font-weight: 600;">
                        <i class="bi bi-patch-check me-2"></i>OJT Completion
                    </h3>
                    <p class="text-muted mb-4">Confirm completion, set student passwords (e.g. if they forgot), and download certificates</p>
                    <a href="{{ route('coordinator.ojt.completion') }}" class="btn-primary-custom" style="background: linear-gradient(135deg, #059669, #047857); box-shadow: 0 4px 15px rgba(5, 150, 105, 0.35);">
                        <i class="bi bi-patch-check"></i>
                        OJT Completion
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="action-card">
                    <h3 class="mb-3" style="color: #212529; font-weight: 600;">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Generate Reports
                    </h3>
                    <p class="text-muted mb-4">Create and download monthly attendance reports as PDF</p>
                    <a href="{{ route('coordinator.generate.report') }}" class="btn-primary-custom" style="background: linear-gradient(135deg, #dc3545, #bb2d3b); box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);">
                        <i class="bi bi-download"></i>
                        Generate Monthly Report
                    </a>
                </div>
            </div>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-4">
                <div class="action-card">
                    <h3 class="mb-3" style="color: #212529; font-weight: 600;">
                        <i class="bi bi-person-x me-2"></i>Duplicate Check
                    </h3>
                    <p class="text-muted mb-4">Review duplicate student numbers or names in your program</p>
                    <a href="{{ route('coordinator.duplicate.check') }}" class="btn-primary-custom" style="background: linear-gradient(135deg, #d97706, #b45309); box-shadow: 0 4px 15px rgba(217, 119, 6, 0.35);">
                        <i class="bi bi-person-x"></i>
                        Check Duplicates
                    </a>
                </div>
            </div>
    </div>
</div>
</body>
</html>
