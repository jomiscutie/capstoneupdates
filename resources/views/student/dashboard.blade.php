@extends('layouts.student')

@section('title', 'Student Dashboard')

@push('styles')
<style>
    .page-title { text-align: center; }
    .page-sub { text-align: center; }
    .alert-warning.late-alert {
        border-radius: 12px;
        border-left: 4px solid #f59e0b;
        border: 1px solid #fcd34d;
        background: #fef3c7;
        color: #92400e;
    }
    .alert-warning.late-alert i { color: #d97706; }
    .alert-warning.late-alert strong { color: #b45309; }
    html[data-theme="dark"] .alert-warning.late-alert {
        border-color: rgba(245, 158, 11, 0.45);
        background: rgba(245, 158, 11, 0.14);
        color: #fde68a;
    }
    html[data-theme="dark"] .alert-warning.late-alert i { color: #fbbf24; }
    html[data-theme="dark"] .alert-warning.late-alert strong { color: #fcd34d; }
    .alert-attendance-error { border-left: 4px solid #dc2626; font-size: 0.9375rem; }
    .attendance-status-notice { display: flex; flex-direction: column; gap: 0.5rem; }
    .notice-item.notice-recorded {
        display: flex; align-items: center;
        padding: 0.65rem 1rem;
        background: rgba(217, 119, 6, 0.12);
        border: 1px solid rgba(217, 119, 6, 0.35);
        border-radius: 10px;
        font-size: 0.875rem;
        color: var(--dtr-text);
    }
    .notice-item.notice-recorded i { color: #d97706; flex-shrink: 0; }
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
    /* Un-mirror front camera so preview and ID are not inverted */
    #faceVerificationModal #faceVideo {
        transform: scaleX(-1);
    }
    #faceVerificationModal #faceCanvas {
        transform: scaleX(-1);
    }
    .term-status-card {
        border-radius: 16px;
        padding: 1.2rem 1.35rem;
        border: 1px solid var(--dtr-border-soft);
        background:
            linear-gradient(135deg, rgba(20, 184, 166, 0.08), transparent 48%),
            var(--dtr-card-bg);
        box-shadow: var(--dtr-shadow-soft);
        margin-bottom: 1.5rem;
    }
    .term-status-head {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    .term-status-head h4 {
        margin: 0;
        color: var(--dtr-text);
        font-size: 1rem;
        font-weight: 700;
    }
    .term-status-head p {
        margin: 0.2rem 0 0;
        color: var(--dtr-muted);
        font-size: 0.875rem;
    }
    .term-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.38rem 0.75rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .term-status-badge.status-active {
        background: rgba(34, 197, 94, 0.14);
        color: #15803d;
        border-color: rgba(34, 197, 94, 0.25);
    }
    .term-status-badge.status-completed {
        background: rgba(37, 99, 235, 0.14);
        color: #1d4ed8;
        border-color: rgba(37, 99, 235, 0.25);
    }
    .term-status-badge.status-pending {
        background: rgba(245, 158, 11, 0.16);
        color: #b45309;
        border-color: rgba(245, 158, 11, 0.25);
    }
    html[data-theme="dark"] .term-status-badge.status-active {
        color: #86efac;
    }
    html[data-theme="dark"] .term-status-badge.status-completed {
        color: #93c5fd;
    }
    html[data-theme="dark"] .term-status-badge.status-pending {
        color: #fcd34d;
    }
    .term-status-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 0.85rem;
    }
    .term-status-item {
        padding: 0.85rem 0.95rem;
        border-radius: 12px;
        background: var(--dtr-surface-soft);
        border: 1px solid var(--dtr-border-soft);
    }
    .term-status-label {
        display: block;
        font-size: 0.76rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--dtr-muted);
        margin-bottom: 0.3rem;
    }
    .term-status-value {
        display: block;
        color: var(--dtr-text);
        font-size: 0.98rem;
        font-weight: 700;
    }
    .term-status-note {
        margin: 0.95rem 0 0;
        color: var(--dtr-muted);
        font-size: 0.875rem;
    }
    .term-history-strip {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.9rem;
    }
    .term-history-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.34rem 0.65rem;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.12);
        border: 1px solid rgba(148, 163, 184, 0.18);
        color: var(--dtr-muted);
        font-size: 0.76rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
    @if(auth()->guard('student')->check())
        <h1 class="page-title">Dashboard</h1>
        <p class="page-sub">Welcome back, {{ auth()->guard('student')->user()->name }}</p>
        
        <div class="term-status-card">
            <div class="term-status-head">
                <div>
                    <h4>Current OJT Term</h4>
                    <p>See your assigned term, section, and current standing.</p>
                </div>
                <span class="term-status-badge {{ $termSummary['badge_class'] }}">{{ $termSummary['badge'] }}</span>
            </div>
            <div class="term-status-grid">
                <div class="term-status-item">
                    <span class="term-status-label">Term</span>
                    <span class="term-status-value">{{ $termSummary['headline'] }}</span>
                </div>
                <div class="term-status-item">
                    <span class="term-status-label">Section</span>
                    <span class="term-status-value">{{ $termSummary['section'] ?: 'Not assigned' }}</span>
                </div>
                <div class="term-status-item">
                    <span class="term-status-label">School Year</span>
                    <span class="term-status-value">{{ $termSummary['school_year'] ?: 'Not set' }}</span>
                </div>
                <div class="term-status-item">
                    <span class="term-status-label">Program</span>
                    <span class="term-status-value">{{ $termSummary['program'] ?: 'Not set' }}</span>
                </div>
            </div>
            <p class="term-status-note">{{ $termSummary['note'] }}</p>
            @if(($termSummary['history'] ?? collect())->isNotEmpty())
                <div class="term-history-strip">
                    @foreach($termSummary['history'] as $historyItem)
                        <span class="term-history-chip">{{ $historyItem->term }} , {{ $historyItem->section }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Time & Actions Card -->
        <div class="card-section">
            <div class="card-header">
                <i class="bi bi-clock-history"></i>
                <h4>Time & Attendance</h4>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show {{ session('error_type') ? 'alert-attendance-error' : '' }}">
                    <i class="bi bi-shield-exclamation me-2"></i>
                    <strong>Verification:</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(isset($attendance) && $attendance)
            <div class="attendance-status-notice mb-3">
                @if($attendance->time_in)
                    <div class="notice-item notice-recorded">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <span>Morning time-in already recorded today at <strong>{{ $attendance->time_in_12 }}</strong>. Duplicate not allowed.</span>
                    </div>
                @endif
                @if($attendance->afternoon_time_in)
                    <div class="notice-item notice-recorded">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <span>Afternoon time-in already recorded today at <strong>{{ $attendance->afternoon_time_in_12 }}</strong>. Duplicate not allowed.</span>
                    </div>
                @endif
                @if($attendance->lunch_break_out)
                    <div class="notice-item notice-recorded">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <span>Lunch / break out already recorded today at <strong>{{ $attendance->lunch_break_out_12 }}</strong> (DTR morning departure).</span>
                    </div>
                @endif
                @if($attendance->time_out)
                    <div class="notice-item notice-recorded">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <span>Time-out already recorded today at <strong>{{ $attendance->time_out_12 }}</strong>. Duplicate not allowed.</span>
                    </div>
                @endif
            </div>
            @endif

            @php
                $showLunchBreakButton = isset($attendance) && $attendance
                    && $attendance->time_in
                    && ! $attendance->lunch_break_out
                    && ! $attendance->afternoon_time_in
                    && ! $attendance->time_out;
                $timeOutCoolingDown = !empty($timeOutUnlockAtIso ?? null);
                $canTimeOut = isset($attendance) && $attendance
                    && ($attendance->time_in || $attendance->afternoon_time_in)
                    && ! $attendance->time_out
                    && ! $timeOutCoolingDown;
                $showTimeOutLockedHint = ! $canTimeOut && (! isset($attendance) || ! $attendance
                    || ((! $attendance->time_in && ! $attendance->afternoon_time_in) && ! $attendance->time_out));
            @endphp

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
                @if($showLunchBreakButton)
                <button type="button" class="btn btn-action btn-lunchbreak" onclick="openFaceVerification('lunchbreak')">
                    <i class="bi bi-cup-hot"></i> Lunch / break out
                </button>
                @endif
                <button type="button" class="btn btn-action btn-timeout"
                    @if($canTimeOut) onclick="openFaceVerification('timeout')" @endif
                    @if(! $canTimeOut) disabled aria-disabled="true" title="{{ $timeOutCoolingDown ? 'Time Out will be available 30 minutes after your latest time-in.' : 'Record morning or afternoon time-in first, then you can time out.' }}" @endif>
                    <i class="bi bi-x-circle"></i> Time Out
                </button>
            </div>
            @if($timeOutCoolingDown)
            <p class="text-muted small mt-2 mb-0" id="timeOutCooldownHint"
               data-unlock-at="{{ $timeOutUnlockAtIso }}"
               data-initial-minutes="{{ (int) ($timeOutMinutesRemaining ?? 0) }}">
                <i class="bi bi-hourglass-split me-1"></i><strong>Time Out</strong> will unlock in about <strong><span id="timeOutMinutesLeft">{{ (int) ($timeOutMinutesRemaining ?? 0) }}</span> minute(s)</strong> after your latest time-in.
            </p>
            @endif
            @if($showTimeOutLockedHint)
            <p class="text-muted small mt-2 mb-0"><i class="bi bi-info-circle me-1"></i><strong>Time Out</strong> stays disabled until you have at least one <strong>Time In</strong> today (morning or afternoon).</p>
            @endif
            @if($showLunchBreakButton)
            <p class="text-muted small mt-2 mb-0">When you leave for lunch, tap <strong>Lunch / break out</strong> so your DTR <em>A.M. departure</em> is recorded. Then use <strong>Time In</strong> after lunch.</p>
            @endif
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
                        Morning: {{ $attendance->late_display }} late | Afternoon: {{ $attendance->afternoon_late_display }} late
                    @elseif($attendance->is_late)
                        Morning: {{ $attendance->late_display }} late
                    @elseif($attendance->afternoon_is_late)
                        Afternoon: {{ $attendance->afternoon_late_display }} late
                    @endif
                </div>
                @endif
                <div class="attendance-summary-grid">
                    <div class="summary-item">
                        <div class="label">Morning Time In</div>
                        <div class="value">
                            @if($attendance->time_in)
                                @if($attendance->is_late)
                                    <span class="badge bg-warning text-dark me-1">{{ $attendance->time_in_12 }}</span>
                                @else
                                    <span class="badge bg-success">{{ $attendance->time_in_12 }}</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="summary-item">
                        <div class="label">A.M. departure (lunch)</div>
                        <div class="value">{{ $attendance->lunch_break_out_12 ?? '-' }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Afternoon Time In</div>
                        <div class="value">
                            @if($attendance->afternoon_time_in)
                                @if($attendance->afternoon_is_late)
                                    <span class="badge bg-warning text-dark me-1">{{ $attendance->afternoon_time_in_12 }}</span>
                                @else
                                    <span class="badge bg-success">{{ $attendance->afternoon_time_in_12 }}</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Time Out</div>
                        <div class="value">{{ $attendance->time_out_12 ?? '-' }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Hours Rendered</div>
                        <div class="value">
                            @php
                                $hoursRendered = $attendance->hours_rendered ?? '';
                            @endphp
                            @if($hoursRendered !== '')
                                {{ str_replace([' hr ', ' min', ' hr'], ['h ', 'm', 'h'], $hoursRendered) }}
                            @else
                                0h 0m
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x" style="font-size: 3rem; color: var(--dtr-muted);"></i>
                    <p class="text-muted mt-3 mb-0">No attendance record for today</p>
                </div>
            @endif
        </div>

    @else
        <div class="card text-center mt-5 p-5">
            <h2>Welcome, Guest</h2>
            <p>Please <a href="{{ route('login') }}">Login</a> or <a href="{{ route('student.register') }}">Register</a> to access your dashboard.</p>
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
                    </div>
                </div>
                <div class="modal-body text-center">
                    <div id="faceVerificationBlock">
                        <p id="faceVerificationModalDesc" class="mb-3"><strong>Show your ID to the camera</strong> while verifying. Look at the camera with your ID visible in frame. Once your face is verified, attendance is submitted automatically. A timestamped photo will be saved as proof of attendance.</p>
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
                        <form id="faceVerificationForm" method="POST" enctype="multipart/form-data" style="display: none;">
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
                        <p class="text-muted small mb-2">If the camera is still on, show your ID to the camera before submitting - a timestamped snapshot will be saved as proof.</p>
                        <form id="passwordVerificationForm" method="POST" action="" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="verification_method" value="password">
                            <input type="hidden" name="recorded_at" id="passwordRecordedAt">
                            <div class="mb-2">
                                <label for="verification_reason" class="form-label small">Reason</label>
                                <select name="verification_reason" id="verification_reason" class="form-select form-select-sm" required>
                                    <option value="">Select reason</option>
                                    <option value="Camera not working">Camera not working</option>
                                    <option value="Device or browser issue">Device or browser issue</option>
                                    <option value="Face recognition failed">Face recognition failed</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label for="passwordVerificationPassword" class="form-label small">Your password</label>
                                <div class="password-toggle-wrap position-relative">
                                    <input type="password" name="password" id="passwordVerificationPassword" class="form-control form-control-sm" placeholder="Enter your account password" required autocomplete="current-password" style="padding-right: 2.25rem;">
                                    <button type="button" class="password-toggle-btn position-absolute top-50 end-0 translate-middle-y border-0 bg-transparent text-secondary p-1 rounded" style="right: 0.25rem; width: 1.75rem; height: 1.75rem;" data-password-toggle aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
                                </div>
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
<script>
document.body.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-password-toggle]');
    if (!btn) return;
    var wrap = btn.closest('.password-toggle-wrap');
    var input = wrap && wrap.querySelector('input');
    var icon = btn.querySelector('i');
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
        btn.setAttribute('aria-label', 'Hide password');
        btn.setAttribute('title', 'Hide password');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
        btn.setAttribute('aria-label', 'Show password');
        btn.setAttribute('title', 'Show password');
    }
});
</script>
<script>window.FACE_API_MODEL_BASE = "{{ asset('vendor/face-api/model') }}";</script>
<script type="application/json" id="student-json">@json(auth()->guard('student')->user())</script>
<script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
<script src="{{ asset('js/face-recognition.js') }}"></script>
<script src="{{ asset('js/offline-queue.js') }}"></script>
<script>
function updateClock() {
    const now = new Date();
    const clockEl = document.getElementById('clock');
    const dayEl = document.getElementById('day');
    const monthYearEl = document.getElementById('month-year');
    if (clockEl) {
        clockEl.innerText = now.toLocaleTimeString('en-US', {
            timeZone: 'Asia/Manila',
            hour12: true,
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
        });
    }
    if (dayEl) {
        dayEl.innerText = now.toLocaleDateString('en-US', {
            timeZone: 'Asia/Manila',
        });
    }
    if (monthYearEl) {
        monthYearEl.innerText = now.toLocaleDateString('en-US', {
            month: 'long',
            year: 'numeric',
            timeZone: 'Asia/Manila',
        });
    }
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

function updateTimeOutCooldownHint() {
    var hint = document.getElementById('timeOutCooldownHint');
    var minutesEl = document.getElementById('timeOutMinutesLeft');
    if (!hint || !minutesEl) return;
    var unlockAt = hint.getAttribute('data-unlock-at');
    if (!unlockAt) return;
    var unlockDate = new Date(unlockAt);
    var now = new Date();
    var diffMs = unlockDate.getTime() - now.getTime();
    if (diffMs <= 0) {
        minutesEl.textContent = '0';
        window.location.reload();
        return;
    }
    var mins = Math.max(1, Math.ceil(diffMs / 60000));
    minutesEl.textContent = String(mins);
}
setInterval(updateTimeOutCooldownHint, 15000);
updateTimeOutCooldownHint();

let currentAction = '';
let verificationInterval = null;
let faceModalTriggerButton = null;
let autoSubmitTimer = null;
let autoSubmitCountdownTimer = null;
let isSubmittingAttendance = false;

function attendancePostUrlForAction(action) {
    if (action === 'timein') return '{{ route("student.timein") }}';
    if (action === 'lunchbreak') return '{{ route("student.lunch.breakout") }}';
    return '{{ route("student.timeout") }}';
}

function attendanceActionLabel(action) {
    if (action === 'timein') return 'Time In';
    if (action === 'lunchbreak') return 'Lunch / break out';
    return 'Time Out';
}

function showCameraErrorStatus(message) {
    var statusEl = document.getElementById('verificationStatus');
    if (!statusEl) return;
    statusEl.innerHTML =
        '<p class="text-danger mb-1"><i class="bi bi-camera-video-off me-2"></i>Camera unavailable.</p>' +
        '<p class="text-muted small mb-2">' + (message || 'Please check camera permission and availability, then try again.') + '</p>' +
        '<button type="button" class="btn btn-outline-primary btn-sm" onclick="retryCameraSetup()"><i class="bi bi-arrow-repeat me-1"></i>Retry camera</button>' +
        '<p class="text-muted small mt-2 mb-0">You can also use <strong>Verify with password instead</strong> below.</p>';
}

async function retryCameraSetup() {
    if (!currentAction) return;
    stopFaceVerification();
    document.getElementById('verificationStatus').innerHTML = '<p class="text-muted">Retrying camera...</p>';
    var video = document.getElementById('faceVideo');
    var canvas = document.getElementById('faceCanvas');
    const cameraResult = await faceRecognition.initializeCamera(video, canvas);
    if (!cameraResult || !cameraResult.ok) {
        showCameraErrorStatus(cameraResult && cameraResult.message ? cameraResult.message : '');
        return;
    }
    document.getElementById('verificationStatus').innerHTML = '<p class="text-info">Camera reconnected. Detecting face...</p>';
    faceRecognition.resetLiveness();
    document.getElementById('blinkCount').textContent = '0';
    if (verificationInterval) {
        clearInterval(verificationInterval);
        verificationInterval = null;
    }
    let startTime = Date.now();
    const maxWaitTime = 10000;
    const runLoop = async () => {
        if (!document.getElementById('faceVerificationModal')?.classList.contains('show')) return;
        const detection = await faceRecognition.detectFace(false);
        const elapsed = Date.now() - startTime;
        let nextDelay = 240;
        if (detection) {
            const blinkCount = faceRecognition.blinkCount;
            document.getElementById('blinkCount').textContent = blinkCount;
            const isLive = faceRecognition.checkLiveness(detection);
            nextDelay = 460;
            if (isLive) {
                document.getElementById('verificationStatus').innerHTML = '<p class="text-success"><i class="bi bi-check-circle me-2"></i>Face detected. Auto-submitting attendance...</p>';
                verifyAndSubmit();
                return;
            } else if (blinkCount > 0) {
                document.getElementById('verificationStatus').innerHTML = '<p class="text-info"><i class="bi bi-eye me-2"></i>Face detected! Hold still - button will enable shortly.</p>';
            } else {
                document.getElementById('verificationStatus').innerHTML = '<p class="text-info"><i class="bi bi-person me-2"></i>Face detected. Keep looking at the camera - auto submit will start shortly.</p>';
            }
            if (elapsed > maxWaitTime) {
                document.getElementById('verificationStatus').innerHTML = '<p class="text-warning"><i class="bi bi-exclamation-triangle me-2"></i>Face not detected clearly. Position your face in the frame and wait a few seconds.</p>';
            }
        } else {
            document.getElementById('verificationStatus').innerHTML = '<p class="text-warning"><i class="bi bi-exclamation-triangle me-2"></i>No face detected. Please position yourself in front of the camera.</p>';
            document.getElementById('blinkCount').textContent = '0';
            nextDelay = 220;
        }
        verificationInterval = setTimeout(runLoop, nextDelay);
    };
    runLoop();
}

async function openFaceVerification(action) {
    currentAction = action;
    faceModalTriggerButton = document.activeElement || document.querySelector('[onclick*="openFaceVerification(\'' + action + '\')"]');
    var modalTitle = document.getElementById('faceVerificationModalLabel');
    if (modalTitle) {
        modalTitle.textContent = 'Face Verification — ' + attendanceActionLabel(action);
    }
    document.getElementById('actionTypeInput').value = action;
    document.getElementById('verificationStatus').innerHTML = '<p class="text-muted">Loading face recognition models...</p>';
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
    const cameraResult = await faceRecognition.initializeCamera(video, canvas);
    if (!cameraResult || !cameraResult.ok) {
        showCameraErrorStatus(cameraResult && cameraResult.message ? cameraResult.message : '');
        return;
    }

    faceRecognition.resetLiveness();
    document.getElementById('blinkCount').textContent = '0';

    let startTime = Date.now();
    const maxWaitTime = 10000;
    document.getElementById('verificationStatus').innerHTML = '<p class="text-info">Detecting face... Please look at the camera.</p>';

    const runLoop = async () => {
        if (!document.getElementById('faceVerificationModal')?.classList.contains('show')) return;
        const detection = await faceRecognition.detectFace(false);
        const elapsed = Date.now() - startTime;
        let nextDelay = 240;

        if (detection) {
            const blinkCount = faceRecognition.blinkCount;
            document.getElementById('blinkCount').textContent = blinkCount;
            const isLive = faceRecognition.checkLiveness(detection);
            nextDelay = 460;

            if (isLive) {
                var countdownMs = 600;
                var countdownTenths = Math.ceil(countdownMs / 100);
                document.getElementById('verificationStatus').innerHTML =
                    '<p class="text-success mb-1"><i class="bi bi-check-circle me-2"></i>Face verified.</p>' +
                    '<p class="text-muted small mb-0">Auto-submitting in <strong id="autoSubmitCountdown">' + (countdownTenths / 10).toFixed(1) + 's</strong>...</p>';
                if (autoSubmitTimer) {
                    clearTimeout(autoSubmitTimer);
                    autoSubmitTimer = null;
                }
                if (autoSubmitCountdownTimer) {
                    clearInterval(autoSubmitCountdownTimer);
                    autoSubmitCountdownTimer = null;
                }
                autoSubmitCountdownTimer = setInterval(function () {
                    countdownTenths -= 1;
                    var el = document.getElementById('autoSubmitCountdown');
                    if (el) {
                        el.textContent = Math.max(0, countdownTenths / 10).toFixed(1) + 's';
                    }
                    if (countdownTenths <= 0) {
                        clearInterval(autoSubmitCountdownTimer);
                        autoSubmitCountdownTimer = null;
                    }
                }, 100);
                autoSubmitTimer = setTimeout(function () {
                    if (!isSubmittingAttendance) {
                        verifyAndSubmit();
                    }
                }, countdownMs);
                return;
            } else if (blinkCount > 0) {
                document.getElementById('verificationStatus').innerHTML = '<p class="text-info"><i class="bi bi-eye me-2"></i>Face detected! Hold still - button will enable shortly.</p>';
            } else {
                document.getElementById('verificationStatus').innerHTML = '<p class="text-info"><i class="bi bi-person me-2"></i>Face detected. Keep looking at the camera - auto submit will start shortly.</p>';
            }
            if (elapsed > maxWaitTime) {
                document.getElementById('verificationStatus').innerHTML = '<p class="text-warning"><i class="bi bi-exclamation-triangle me-2"></i>Face not detected clearly. Position your face in the frame and wait a few seconds.</p>';
            }
        } else {
            document.getElementById('verificationStatus').innerHTML = '<p class="text-warning"><i class="bi bi-exclamation-triangle me-2"></i>No face detected. Please position yourself in front of the camera.</p>';
            document.getElementById('blinkCount').textContent = '0';
            nextDelay = 220;
        }
        verificationInterval = setTimeout(runLoop, nextDelay);
    };
    runLoop();
}

function captureVerificationSnapshot(jpegQuality) {
    return new Promise(function(resolve, reject) {
        var video = document.getElementById('faceVideo');
        if (!video || video.readyState < 2) {
            reject(new Error('Video not ready'));
            return;
        }
        var canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        var ctx = canvas.getContext('2d');
        /* Draw video un-mirrored (same as preview) so saved snapshot is not inverted */
        ctx.save();
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        ctx.restore();
        var now = new Date();
        var timestampStr = now.toLocaleString('en-CA', { timeZone: 'Asia/Manila', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }) + ' Asia/Manila';
        ctx.fillStyle = 'rgba(0,0,0,0.7)';
        ctx.fillRect(0, canvas.height - 28, canvas.width, 28);
        ctx.fillStyle = '#fff';
        ctx.font = '14px monospace';
        ctx.fillText(timestampStr, 8, canvas.height - 10);
        var q = typeof jpegQuality === 'number' ? jpegQuality : 0.92;
        canvas.toBlob(function(blob) {
            if (blob) resolve(blob);
            else reject(new Error('Failed to create image'));
        }, 'image/jpeg', q);
    });
}

function blobToBase64(blob) {
    return new Promise(function(resolve, reject) {
        var reader = new FileReader();
        reader.onloadend = function() {
            var s = reader.result;
            if (typeof s !== 'string') {
                reject(new Error('Read failed'));
                return;
            }
            var i = s.indexOf(',');
            resolve(i >= 0 ? s.slice(i + 1) : s);
        };
        reader.onerror = function() {
            reject(reader.error || new Error('Read failed'));
        };
        reader.readAsDataURL(blob);
    });
}

/** Keeps queued snapshots under Laravel max upload (~2MB) when possible. */
function captureSnapshotForOfflineQueue() {
    return captureVerificationSnapshot(0.86).then(function(blob) {
        if (blob.size <= 1900000) return blob;
        return captureVerificationSnapshot(0.72);
    }).then(function(blob) {
        if (blob.size <= 1900000) return blob;
        return captureVerificationSnapshot(0.62);
    });
}

async function verifyAndSubmit() {
    if (isSubmittingAttendance) return;
    isSubmittingAttendance = true;
    if (autoSubmitTimer) {
        clearTimeout(autoSubmitTimer);
        autoSubmitTimer = null;
    }
    if (autoSubmitCountdownTimer) {
        clearInterval(autoSubmitCountdownTimer);
        autoSubmitCountdownTimer = null;
    }
    document.getElementById('verificationStatus').innerHTML = '<p class="text-info">Verifying face...</p>';

    try {
        var studentEl = document.getElementById('student-json');
        let student = null;
        try {
            student = studentEl ? JSON.parse(studentEl.textContent) : null;
        } catch (e) {
            console.error('Student JSON parse error:', e);
            document.getElementById('verificationStatus').innerHTML = '<p class="text-danger">Could not read account data. Please refresh the page.</p>';
            isSubmittingAttendance = false;
            return;
        }
        if (!student || !student.face_encoding) {
            alert('Face not registered. Please contact administrator.');
            stopFaceVerification();
            isSubmittingAttendance = false;
            return;
        }

        const verification = await faceRecognition.verifyFace(student.face_encoding);
        if (!verification || typeof verification.verified === 'undefined') {
            document.getElementById('verificationStatus').innerHTML = '<p class="text-danger">Verification failed. Please try again.</p>';
            isSubmittingAttendance = false;
            return;
        }

        if (verification.verified && verification.encoding) {
            const encoding = verification.encoding;
            const recordedAt = new Date().toISOString();
            const form = document.getElementById('faceVerificationForm');
            const token = form.querySelector('input[name="_token"]').value;
            const timeInUrl = '{{ route("student.timein") }}';
            const timeOutUrl = '{{ route("student.timeout") }}';
            const lunchBreakUrl = '{{ route("student.lunch.breakout") }}';
            var postUrl = attendancePostUrlForAction(currentAction);

            if (typeof window.DtrOfflineQueue !== 'undefined' && !window.DtrOfflineQueue.isOnline()) {
                var confidence = verification.confidence;
                captureSnapshotForOfflineQueue().then(function(blob) {
                    return blobToBase64(blob);
                }).then(function(b64) {
                    return window.DtrOfflineQueue.addPending({
                        action_type: currentAction,
                        face_encoding: encoding,
                        recorded_at: recordedAt,
                        _token: token,
                        time_in_url: timeInUrl,
                        time_out_url: timeOutUrl,
                        lunch_break_url: lunchBreakUrl,
                        verification_confidence: confidence,
                        snapshot_jpeg_base64: b64
                    });
                }).then(function() {
                    stopFaceVerification();
                    var modalEl = document.getElementById('faceVerificationModal');
                    var modalInst = bootstrap.Modal.getInstance(modalEl);
                    if (modalInst) modalInst.hide();
                    showOfflineRecordedMessage(confidence);
                }).catch(function(err) {
                    console.error('Offline queue add failed', err);
                    document.getElementById('verificationStatus').innerHTML = '<p class="text-danger">Could not save offline with a verification photo. Check the camera, then try again (or wait until you are online).</p>';
                    isSubmittingAttendance = false;
                });
                return;
            }

            document.getElementById('faceEncodingInput').value = encoding;
            document.getElementById('recordedAtInput').value = recordedAt;
            document.getElementById('verificationConfidenceInput').value = (verification.confidence != null) ? verification.confidence : 0;
            form.action = postUrl;

            captureVerificationSnapshot().then(function(blob) {
                var formData = new FormData();
                formData.append('_token', form.querySelector('input[name="_token"]').value);
                formData.append('face_encoding', encoding);
                formData.append('recorded_at', recordedAt);
                formData.append('verification_confidence', (verification.confidence != null) ? verification.confidence : 0);
                formData.append('verification_snapshot', blob, 'verification-' + currentAction + '.jpg');
                form.action = postUrl;
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                }).then(function(res) {
                    if (res.redirected) {
                        window.location.href = res.url;
                    } else {
                        window.location.reload();
                    }
                }).catch(function(err) {
                    console.error('Submit error', err);
                    if (typeof window.DtrOfflineQueue === 'undefined') {
                        document.getElementById('verificationStatus').innerHTML = '<p class="text-danger">Failed to submit. Please try again.</p>';
                        isSubmittingAttendance = false;
                        return;
                    }
                    var confidenceQueued = verification.confidence;
                    blobToBase64(blob).then(function(b64) {
                        return window.DtrOfflineQueue.addPending({
                            action_type: currentAction,
                            face_encoding: encoding,
                            recorded_at: recordedAt,
                            _token: token,
                            time_in_url: timeInUrl,
                            time_out_url: timeOutUrl,
                            lunch_break_url: lunchBreakUrl,
                            verification_confidence: confidenceQueued,
                            snapshot_jpeg_base64: b64
                        });
                    }).then(function() {
                        stopFaceVerification();
                        var modalEl2 = document.getElementById('faceVerificationModal');
                        var modalInst2 = bootstrap.Modal.getInstance(modalEl2);
                        if (modalInst2) modalInst2.hide();
                        showOfflineRecordedMessage(confidenceQueued);
                    }).catch(function(queueErr) {
                        console.error('Queue after failed submit', queueErr);
                        document.getElementById('verificationStatus').innerHTML = '<p class="text-danger">Failed to submit and could not save for later sync. Please try again.</p>';
                        isSubmittingAttendance = false;
                    });
                });
            }).catch(function(err) {
                console.error('Snapshot error', err);
                var formData = new FormData(form);
                form.action = postUrl;
                fetch(form.action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } }).then(function(res) {
                    if (res.redirected) window.location.href = res.url;
                    else window.location.reload();
                }).catch(function() {
                    isSubmittingAttendance = false;
                    form.submit();
                });
            });
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
            faceRecognition.resetLiveness();
            document.getElementById('blinkCount').textContent = '0';
            isSubmittingAttendance = false;
        }
    } catch (error) {
        console.error('Verification error:', error);
        document.getElementById('verificationStatus').innerHTML = '<p class="text-danger">Error during verification. Please try again.</p><p class="text-muted small">' + (error.message || '') + '</p>';
        isSubmittingAttendance = false;
    }
}

function stopFaceVerification() {
    if (verificationInterval) {
        clearTimeout(verificationInterval);
        verificationInterval = null;
    }
    if (autoSubmitTimer) {
        clearTimeout(autoSubmitTimer);
        autoSubmitTimer = null;
    }
    if (autoSubmitCountdownTimer) {
        clearInterval(autoSubmitCountdownTimer);
        autoSubmitCountdownTimer = null;
    }
    isSubmittingAttendance = false;
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
        document.getElementById('passwordVerificationForm').action = attendancePostUrlForAction(currentAction);
        document.getElementById('passwordSubmitLabel').textContent = attendanceActionLabel(currentAction);
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

(function() {
    var pwForm = document.getElementById('passwordVerificationForm');
    if (pwForm) {
        pwForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            var submitBtn = document.getElementById('passwordSubmitBtn');
            if (submitBtn) submitBtn.disabled = true;
            captureVerificationSnapshot().then(function(blob) {
                var formData = new FormData(form);
                formData.append('verification_snapshot', blob, 'verification-' + currentAction + '.jpg');
                return fetch(form.action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } });
            }).catch(function() {
                return fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } });
            }).then(function(res) {
                if (res.redirected) window.location.href = res.url;
                else window.location.reload();
            }).catch(function(err) {
                console.error('Submit error', err);
                if (submitBtn) submitBtn.disabled = false;
                form.submit();
            });
        });
    }
})();

function showOfflineRecordedMessage(confidence) {
    var matchText = (confidence != null && confidence !== '') ? ' - ' + confidence + '% match' : '';
    var alert = document.createElement('div');
    alert.className = 'alert alert-info';
    alert.innerHTML = '<i class="bi bi-cloud-download me-2"></i><strong>Recorded offline' + matchText + '.</strong> Your ' + attendanceActionLabel(currentAction) + ' and verification photo will sync when you are back online.';
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
    // Warm-up face-api models in background for faster first verification.
    if (typeof faceRecognition !== 'undefined' && faceRecognition && typeof faceRecognition.loadModels === 'function') {
        faceRecognition.loadModels().catch(function() {});
    }
    if (typeof window.DtrOfflineQueue === 'undefined' || !window.DtrOfflineQueue.isOnline()) return;
    window.DtrOfflineQueue.getAllPending().then(function(items) {
        if (items.length) {
            return window.DtrOfflineQueue.processQueue().then(function() {
                window.location.reload();
            });
        }
    }).catch(function() {});
});

</script>
@endpush

