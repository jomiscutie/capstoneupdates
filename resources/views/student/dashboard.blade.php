<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - NORSU OJT DTR</title>
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
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            position: relative;
            z-index: 1;
        }

        /* Header Card */
        .header-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2.5rem 2rem;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
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
            text-align: center;
        }

        .header-content h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .header-info {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .header-info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            font-size: 0.9rem;
        }

        /* Card Section */
        .card-section {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .card-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f3f5;
        }

        .card-header h4 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #212529;
            margin: 0;
        }

        .card-header i {
            font-size: 1.5rem;
            color: #667eea;
        }

        /* Time Display */
        .time-display {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .time-item {
            text-align: center;
            padding: 1rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
        }

        .time-item .label {
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .time-item .value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #212529;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }

        .btn-action {
            padding: 0.875rem 2rem;
            font-size: 1rem;
            border-radius: 12px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 140px;
            justify-content: center;
        }

        .btn-timein {
            background: linear-gradient(135deg, #198754, #157347);
            color: white;
            box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
        }

        .btn-timein:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(25, 135, 84, 0.4);
            color: white;
        }

        .btn-timeout {
            background: linear-gradient(135deg, #dc3545, #bb2d3b);
            color: white;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }

        .btn-timeout:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
            color: white;
        }

        .btn-logout {
            background: #6c757d;
            color: white;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
            color: white;
        }

        /* Attendance Summary */
        .attendance-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .summary-item {
            padding: 1.25rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }

        .summary-item .label {
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .summary-item .value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #212529;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }

        .table {
            border-radius: 12px;
            overflow: hidden;
        }

        .table thead {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .table thead th {
            border: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f3f5;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        /* Month Filter */
        .month-filter {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.25rem;
        }

        @media (max-width: 768px) {
            .header-info {
                flex-direction: column;
                gap: 0.75rem;
            }

            .time-display {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
            }

            .container {
                padding: 0 1rem;
            }
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
            @php
                $attendance = \App\Models\Attendance::where('student_id', auth()->guard('student')->id())
                                ->where('date', now()->format('Y-m-d'))
                                ->first();
            @endphp
            @if($attendance)
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
        @php
            $selectedMonth = request('month') ?? now()->format('Y-m');
            $logs = \App\Models\Attendance::where('student_id', auth()->guard('student')->id())
                        ->whereYear('date', explode('-', $selectedMonth)[0])
                        ->whereMonth('date', explode('-', $selectedMonth)[1])
                        ->orderBy('date', 'desc')
                        ->get();
        @endphp
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
<div class="modal fade" id="faceVerificationModal" tabindex="-1" aria-labelledby="faceVerificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="faceVerificationModalLabel">Face Verification Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="stopFaceVerification()"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-3">Please look directly at the camera and ensure good lighting.</p>
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="stopFaceVerification()">Cancel</button>
                <button type="button" class="btn btn-primary" id="verifyFaceBtn" onclick="verifyAndSubmit()" disabled>Verify & Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Face API -->
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.6.9/dist/face-api.min.js"></script>
<script src="{{ asset('js/face-recognition.js') }}"></script>

<script>
let currentAction = '';
let verificationInterval = null;

async function openFaceVerification(action) {
    currentAction = action;
    document.getElementById('actionTypeInput').value = action;
    document.getElementById('verificationStatus').innerHTML = '<p class="text-muted">Loading face recognition models...</p>';
    document.getElementById('verifyFaceBtn').disabled = true;
    
    const modal = new bootstrap.Modal(document.getElementById('faceVerificationModal'));
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
                document.getElementById('verifyFaceBtn').disabled = false;
                clearInterval(verificationInterval);
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
            document.getElementById('faceEncodingInput').value = verification.encoding;
            document.getElementById('faceVerificationForm').action = currentAction === 'timein' 
                ? '{{ route("student.timein") }}' 
                : '{{ route("student.timeout") }}';
            document.getElementById('faceVerificationForm').submit();
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
</script>
</body>
</html>
