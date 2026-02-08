@extends('layouts.student')

@section('title', 'Student Dashboard')

@push('styles')
<style>
    .alert-warning.late-alert { border-radius: 12px; border-left: 4px solid #ffc107; }
    /* Face verification: modal must sit above Bootstrap backdrop (backdrop is 1050) */
    #faceVerificationModal.modal { z-index: 1060 !important; }
    #faceVerificationModal .modal-dialog { z-index: 1061; }
    #faceVerificationModal .modal-content { position: relative; overflow: hidden; }
    #faceVerificationModal .modal-body { position: relative; z-index: 0; }
    #faceVerificationModal .position-relative.d-inline-block {
        overflow: hidden;
        max-height: 50vh;
        position: relative;
        z-index: 0;
    }
    #faceVerificationModal #faceCanvas,
    #faceVerificationModal #faceVideo {
        pointer-events: none !important;
    }
</style>
@endpush

@section('content')
    @if(auth()->guard('student')->check())
        <h1 class="page-title">Dashboard</h1>
        <p class="page-sub">Welcome back, {{ auth()->guard('student')->user()->name }}</p>
        <p class="text-muted small mb-3">
            <i class="bi bi-card-text me-1"></i>{{ auth()->guard('student')->user()->student_no }}
            <span class="ms-2"><i class="bi bi-mortarboard me-1"></i>{{ auth()->guard('student')->user()->course }}</span>
        </p>

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
                    <i class="bi bi-check-circle"></i> Time In
                </button>
                <button type="button" class="btn btn-action btn-timeout" onclick="openFaceVerification('timeout')">
                    <i class="bi bi-x-circle"></i> Time Out
                </button>
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
                <div class="alert alert-warning late-alert mb-3">
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

    <!-- Face Verification Modal: buttons in header so they are never covered by video/canvas -->
    <div class="modal fade" id="faceVerificationModal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="faceVerificationModalLabel" aria-describedby="faceVerificationModalDesc" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="modal-title mb-0" id="faceVerificationModalLabel">Face Verification Required</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" onclick="stopFaceVerification()" aria-label="Cancel">Cancel</button>
                        <button type="button" class="btn btn-primary btn-sm" id="verifyFaceBtn" onclick="verifyAndSubmit()" disabled aria-label="Verify and submit">Verify & Submit</button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="stopFaceVerification()"></button>
                    </div>
                </div>
                <div class="modal-body text-center">
                    <div id="faceVerificationBlock">
                        <p id="faceVerificationModalDesc" class="mb-3">Please look at the camera. When your face is detected, click &quot;Verify & Submit&quot; above (or press Escape to cancel).</p>
                        <div class="position-relative d-inline-block" style="max-height: 50vh;">
                            <video id="faceVideo" autoplay playsinline style="width: 100%; max-width: 640px; max-height: 50vh; border-radius: 10px; display: block;"></video>
                            <canvas id="faceCanvas" style="position: absolute; top: 0; left: 0; width: 100%; max-width: 640px; pointer-events: none;"></canvas>
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
                    <div class="my-3"><hr class="my-3"></div>
                    <p class="text-muted small mb-2">Camera not working or unavailable?</p>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="togglePasswordFallbackBtn" onclick="togglePasswordFallback()">
                        <i class="bi bi-key me-1"></i>Verify with password instead
                    </button>
                    <div id="passwordFallbackBlock" class="mt-3 text-start" style="display: none;">
                        <form id="passwordVerificationForm" method="POST" action="">
                            @csrf
                            <input type="hidden" name="verification_method" value="password">
                            <input type="hidden" name="recorded_at" id="passwordRecordedAt">
                            <div class="mb-2">
                                <label for="verification_reason" class="form-label small">Reason (optional)</label>
                                <select name="verification_reason" id="verification_reason" class="form-select form-select-sm">
                                    <option value="">Select reason</option>
                                    <option value="Camera not working">Camera not working</option>
                                    <option value="Device or browser issue">Device or browser issue</option>
                                    <option value="Face recognition failed">Face recognition failed</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label for="passwordVerificationPassword" class="form-label small">Your password</label>
                                <input type="password" name="password" id="passwordVerificationPassword" class="form-control form-control-sm" placeholder="Enter your account password" required autocomplete="current-password">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100" id="passwordSubmitBtn">
                                <i class="bi bi-key me-1"></i><span id="passwordSubmitLabel">Time In</span> with password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>window.FACE_API_MODEL_BASE = "{{ asset('vendor/face-api/model') }}";</script>
<script type="application/json" id="student-json">@json(auth()->guard('student')->user())</script>
<script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
<script src="{{ asset('js/face-recognition.js') }}"></script>
<script src="{{ asset('js/offline-queue.js') }}"></script>
<script>
function updateClock() {
    const now = new Date();
    const options = { timeZone: 'Asia/Manila', hour12: false };
    const clockEl = document.getElementById('clock');
    const dayEl = document.getElementById('day');
    const monthYearEl = document.getElementById('month-year');
    if (clockEl) clockEl.innerText = now.toLocaleTimeString('en-US', options);
    if (dayEl) dayEl.innerText = now.toLocaleDateString('en-US', options);
    if (monthYearEl) monthYearEl.innerText = now.toLocaleDateString('en-US', { month: 'long', year: 'numeric', timeZone: 'Asia/Manila' });
}
if (document.getElementById('clock')) {
    setInterval(updateClock, 1000);
    updateClock();
}
function scheduleMidnightReload() {
    const now = new Date();
    const midnight = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
    setTimeout(() => location.reload(), midnight - now);
}
scheduleMidnightReload();

let currentAction = '';
let verificationInterval = null;
let faceModalTriggerButton = null;

async function openFaceVerification(action) {
    currentAction = action;
    faceModalTriggerButton = document.activeElement || document.querySelector('[onclick*="openFaceVerification(\'' + action + '\')"]');
    document.getElementById('actionTypeInput').value = action;
    document.getElementById('verificationStatus').innerHTML = '<p class="text-muted">Loading face recognition models...</p>';
    document.getElementById('verifyFaceBtn').disabled = true;
    resetPasswordFallbackVisibility();

    const modalEl = document.getElementById('faceVerificationModal');
    document.body.appendChild(modalEl);
    const modal = new bootstrap.Modal(modalEl);
    modalEl.addEventListener('shown.bs.modal', function onShown() {
        modalEl.removeEventListener('shown.bs.modal', onShown);
        var firstFocusable = modalEl.querySelector('button:not([disabled])');
        if (firstFocusable) firstFocusable.focus();
    }, { once: true });
    modalEl.addEventListener('hidden.bs.modal', function onHidden() {
        modalEl.removeEventListener('hidden.bs.modal', onHidden);
        resetPasswordFallbackVisibility();
        if (faceModalTriggerButton && typeof faceModalTriggerButton.focus === 'function') {
            faceModalTriggerButton.focus();
        }
        faceModalTriggerButton = null;
    }, { once: true });
    modal.show();

    const modelsLoaded = await faceRecognition.loadModels();
    if (!modelsLoaded) {
        document.getElementById('verificationStatus').innerHTML = '<p class="text-danger">Failed to load face recognition models.</p><p class="text-muted small mt-2">You can use <strong>Verify with password instead</strong> below to record your attendance.</p>';
        return;
    }

    const video = document.getElementById('faceVideo');
    const canvas = document.getElementById('faceCanvas');
    const cameraReady = await faceRecognition.initializeCamera(video, canvas);
    if (!cameraReady) {
        document.getElementById('verificationStatus').innerHTML = '<p class="text-danger">Camera unavailable or access denied.</p><p class="text-muted small mt-2">Use <strong>Verify with password instead</strong> below to record your attendance.</p>';
        return;
    }

    faceRecognition.resetLiveness();
    document.getElementById('blinkCount').textContent = '0';

    let startTime = Date.now();
    const maxWaitTime = 10000;
    document.getElementById('verificationStatus').innerHTML = '<p class="text-info">Detecting face... Please look at the camera.</p>';

    verificationInterval = setInterval(async () => {
        const detection = await faceRecognition.detectFace();
        const elapsed = Date.now() - startTime;

        if (detection) {
            const blinkCount = faceRecognition.blinkCount;
            document.getElementById('blinkCount').textContent = blinkCount;
            const isLive = faceRecognition.checkLiveness(detection);

            if (isLive) {
                document.getElementById('verificationStatus').innerHTML = '<p class="text-success"><i class="bi bi-check-circle me-2"></i>Face detected! Click &quot;Verify & Submit&quot; below.</p>';
                var btn = document.getElementById('verifyFaceBtn');
                btn.disabled = false;
                clearInterval(verificationInterval);
                verificationInterval = null;
                try { btn.focus(); } catch (e) {}
            } else if (blinkCount > 0) {
                document.getElementById('verificationStatus').innerHTML = '<p class="text-info"><i class="bi bi-eye me-2"></i>Face detected! Hold still — button will enable shortly.</p>';
            } else {
                document.getElementById('verificationStatus').innerHTML = '<p class="text-info"><i class="bi bi-person me-2"></i>Face detected. Keep looking at the camera — Verify & Submit will enable in a moment.</p>';
            }
            if (elapsed > maxWaitTime && document.getElementById('verifyFaceBtn').disabled) {
                document.getElementById('verificationStatus').innerHTML = '<p class="text-warning"><i class="bi bi-exclamation-triangle me-2"></i>Face not detected clearly. Position your face in the frame and wait a few seconds.</p>';
            }
        } else {
            document.getElementById('verificationStatus').innerHTML = '<p class="text-warning"><i class="bi bi-exclamation-triangle me-2"></i>No face detected. Please position yourself in front of the camera.</p>';
            document.getElementById('blinkCount').textContent = '0';
        }
    }, 400);
}

async function verifyAndSubmit() {
    document.getElementById('verifyFaceBtn').disabled = true;
    document.getElementById('verificationStatus').innerHTML = '<p class="text-info">Verifying face...</p>';

    try {
        var studentEl = document.getElementById('student-json');
        let student = null;
        try {
            student = studentEl ? JSON.parse(studentEl.textContent) : null;
        } catch (e) {
            console.error('Student JSON parse error:', e);
            document.getElementById('verificationStatus').innerHTML = '<p class="text-danger">Could not read account data. Please refresh the page.</p>';
            document.getElementById('verifyFaceBtn').disabled = false;
            return;
        }
        if (!student || !student.face_encoding) {
            alert('Face not registered. Please contact administrator.');
            stopFaceVerification();
            return;
        }

        const verification = await faceRecognition.verifyFace(student.face_encoding);
        if (!verification || typeof verification.verified === 'undefined') {
            document.getElementById('verificationStatus').innerHTML = '<p class="text-danger">Verification failed. Please try again.</p>';
            document.getElementById('verifyFaceBtn').disabled = false;
            return;
        }

        if (verification.verified && verification.encoding) {
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
            document.getElementById('verificationConfidenceInput').value = (verification.confidence != null) ? verification.confidence : 0;
            form.action = currentAction === 'timein' ? timeInUrl : timeOutUrl;
            form.submit();
            return;
        }

        if (!verification.verified) {
            const dist = verification.distance != null ? verification.distance.toFixed(2) : '?';
            const conf = verification.confidence != null ? verification.confidence : 0;
            const ratio = verification.matchRatio != null ? (verification.matchRatio * 100).toFixed(0) : '0';
            const att = verification.attempts != null ? verification.attempts : 0;
            let errorMsg = '<p class="text-danger"><i class="bi bi-exclamation-triangle me-2"></i><strong>Face Verification Failed</strong></p>';
            errorMsg += '<p class="text-muted small">Distance: ' + dist + ' (threshold: 0.6)</p>';
            errorMsg += '<p class="text-muted small">Confidence: ' + conf + '%</p>';
            errorMsg += '<p class="text-muted small">Matches: ' + ratio + '% (' + att + ' attempts)</p>';
            errorMsg += '<p class="text-warning mt-2"><small>Try: better lighting, look straight at the camera, or move slightly closer.</small></p>';
            document.getElementById('verificationStatus').innerHTML = errorMsg;
            document.getElementById('verifyFaceBtn').disabled = false;
            faceRecognition.resetLiveness();
            document.getElementById('blinkCount').textContent = '0';
        }
    } catch (error) {
        console.error('Verification error:', error);
        document.getElementById('verificationStatus').innerHTML = '<p class="text-danger">Error during verification. Please try again.</p><p class="text-muted small">' + (error.message || '') + '</p>';
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

function togglePasswordFallback() {
    var faceBlock = document.getElementById('faceVerificationBlock');
    var passwordBlock = document.getElementById('passwordFallbackBlock');
    var btn = document.getElementById('togglePasswordFallbackBtn');
    if (passwordBlock.style.display === 'none') {
        faceBlock.style.display = 'none';
        passwordBlock.style.display = 'block';
        btn.innerHTML = '<i class="bi bi-camera me-1"></i>Back to face verification';
        document.getElementById('passwordRecordedAt').value = new Date().toISOString();
        document.getElementById('passwordVerificationForm').action = currentAction === 'timein' ? '{{ route("student.timein") }}' : '{{ route("student.timeout") }}';
        document.getElementById('passwordSubmitLabel').textContent = currentAction === 'timein' ? 'Time In' : 'Time Out';
        document.getElementById('passwordVerificationPassword').value = '';
        document.getElementById('passwordVerificationPassword').focus();
    } else {
        faceBlock.style.display = 'block';
        passwordBlock.style.display = 'none';
        btn.innerHTML = '<i class="bi bi-key me-1"></i>Verify with password instead';
    }
}

function resetPasswordFallbackVisibility() {
    var faceBlock = document.getElementById('faceVerificationBlock');
    var passwordBlock = document.getElementById('passwordFallbackBlock');
    var btn = document.getElementById('togglePasswordFallbackBtn');
    faceBlock.style.display = 'block';
    passwordBlock.style.display = 'none';
    btn.innerHTML = '<i class="bi bi-key me-1"></i>Verify with password instead';
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
@endpush
