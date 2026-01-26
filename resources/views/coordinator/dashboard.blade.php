<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinator Dashboard - NORSU OJT DTR</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #f8f9fa;
            background-image: url('/images/negrosorientalstateuniversity_cover.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            padding: 2rem 0;
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.65);
            z-index: 0;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem;
            position: relative;
            z-index: 1;
        }

        /* Header Section */
        .header-card {
            background: linear-gradient(135deg, #357ABD 0%, #2c5ca8 100%);
            color: white;
            padding: 2.5rem 2rem;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(53, 122, 189, 0.3);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .header-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
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
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .header-info p {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0.25rem 0;
        }

        .program-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.75rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .btn-logout {
            padding: 0.625rem 1.5rem;
            font-size: 0.9rem;
            border-radius: 8px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
        }

        /* Info Alert */
        .info-alert {
            background: linear-gradient(135deg, #e7f3ff 0%, #d0e7ff 100%);
            border-left: 4px solid #0d6efd;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.1);
        }

        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            transition: width 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-card:hover::before {
            width: 100%;
            opacity: 0.05;
        }

        .stat-card.primary::before {
            background: #0d6efd;
        }

        .stat-card.success::before {
            background: #198754;
        }

        .stat-card.danger::before {
            background: #dc3545;
        }

        .stat-card.warning::before {
            background: #ffc107;
        }

        .stat-card.warning .stat-icon {
            background: linear-gradient(135deg, #ffc107, #ffb300);
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-card.primary .stat-icon {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
        }

        .stat-card.success .stat-icon {
            background: linear-gradient(135deg, #198754, #157347);
        }

        .stat-card.danger .stat-icon {
            background: linear-gradient(135deg, #dc3545, #bb2d3b);
        }

        .stat-card .number {
            font-size: 3rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 0.5rem;
            line-height: 1;
        }

        .stat-card .label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .sub-label {
            font-size: 0.75rem;
            color: #adb5bd;
            margin-top: 0.25rem;
        }

        /* Action Section */
        .action-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .btn-primary-custom {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            border-radius: 12px;
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: white;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
            color: white;
        }

        .btn-primary-custom i {
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-info h1 {
                font-size: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-container {
                padding: 0 1rem;
            }
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
            <div class="col-md-6">
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
            <div class="col-md-6">
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
</div>
</body>
</html>
