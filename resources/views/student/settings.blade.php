@extends('layouts.student')

@section('title', 'Settings')

@push('styles')
<style>
    .settings-page .back-link { margin-bottom: 0.5rem; color: var(--dtr-muted); text-decoration: none; font-size: 0.9rem; }
    .settings-page .back-link:hover { color: var(--dtr-text); }
    .settings-page .page-title { font-size: 1.5rem; font-weight: 600; color: var(--dtr-text); margin-bottom: 0.25rem; text-align: center; }
    .settings-page .page-sub { font-size: 0.9rem; color: var(--dtr-muted); margin: 0 auto 1.5rem; text-align: center; max-width: 720px; }
    .settings-page .card {
        border: 1px solid var(--dtr-border-soft);
        background: var(--dtr-card-bg);
        box-shadow: var(--dtr-shadow-soft);
    }
    .settings-page .card-body { padding: 1.5rem; }
    .settings-page .section-title { font-size: 1rem; font-weight: 600; color: var(--dtr-text); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .settings-page .section-title i { color: var(--dtr-primary); }
    .settings-page .settings-list { list-style: none; padding: 0; margin: 0; }
    .settings-page .settings-list li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 0.25rem;
        border-bottom: 1px solid var(--dtr-border-soft);
        gap: 1rem;
        border-radius: 10px;
        transition: background-color 0.15s ease;
    }
    .settings-page .settings-list li:hover { background: var(--dtr-hover-bg); }
    .settings-page .settings-list li:last-child { border-bottom: none; padding-bottom: 0; }
    .settings-page .settings-list .setting-label { font-size: 0.95rem; color: var(--dtr-text); font-weight: 500; }
    .settings-page .settings-list .setting-desc { font-size: 0.8rem; color: var(--dtr-muted); margin-top: 0.15rem; }
    .settings-page .settings-list .btn-outline-primary {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 8px;
        border: 1px solid var(--dtr-primary);
        color: var(--dtr-primary);
        background: transparent;
        transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }
    .settings-page .settings-list .btn-outline-primary:hover {
        background: var(--dtr-primary);
        color: #fff;
        border-color: var(--dtr-primary);
    }
    /* Change password modal – modern minimalist */
    .settings-page .modal-backdrop.show { opacity: 0.4; }
    .settings-page #changePasswordModal .modal-dialog { max-width: 400px; }
    .settings-page #changePasswordModal .modal-content {
        border: none;
        border-radius: 16px;
        box-shadow: 0 24px 48px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .settings-page #changePasswordModal .modal-header {
        border: none;
        padding: 1.5rem 1.5rem 0.5rem;
        background: transparent;
    }
    .settings-page #changePasswordModal .modal-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--dtr-text);
        letter-spacing: -0.01em;
    }
    .settings-page #changePasswordModal .btn-close {
        opacity: 0.5;
        padding: 0.5rem;
        font-size: 0.75rem;
    }
    .settings-page #changePasswordModal .btn-close:hover { opacity: 0.8; }
    .settings-page #changePasswordModal .modal-body {
        padding: 0.5rem 1.5rem 1.5rem;
    }
    .settings-page #changePasswordModal .form-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--dtr-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.35rem;
    }
    .settings-page #changePasswordModal .form-control {
        padding: 0.65rem 0.875rem;
        border-radius: 10px;
        border: 1px solid var(--dtr-input-border);
        font-size: 0.9375rem;
        background: var(--dtr-input-bg);
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
    }
    .settings-page #changePasswordModal .form-control::placeholder { color: var(--dtr-muted); opacity: 0.9; }
    .settings-page #changePasswordModal .form-control:hover { background: var(--dtr-card-bg); border-color: var(--dtr-input-border); }
    .settings-page #changePasswordModal .form-control:focus {
        background: var(--dtr-card-bg);
        border-color: var(--dtr-primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        outline: none;
    }
    .settings-page #changePasswordModal .mb-3 { margin-bottom: 1.125rem !important; }
    .settings-page #changePasswordModal .mb-0 { margin-bottom: 0 !important; }
    .settings-page #changePasswordModal .modal-footer {
        border: none;
        padding: 0 1.5rem 1.5rem;
        gap: 0.65rem;
        justify-content: flex-end;
    }
    .settings-page .password-toggle-wrap { position: relative; display: block; }
    .settings-page .password-toggle-wrap .form-control { padding-right: 2.75rem; }
    .settings-page .password-toggle-btn {
        position: absolute;
        right: 0.5rem;
        top: 50%;
        transform: translateY(-50%);
        width: 2rem;
        height: 2rem;
        padding: 0;
        border: none;
        background: none;
        color: var(--dtr-muted);
        cursor: pointer;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: color 0.15s ease, background 0.15s ease;
    }
    .settings-page .password-toggle-btn:hover { color: var(--dtr-primary); background: rgba(79, 70, 229, 0.08); }
    .settings-page .face-status {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.85rem;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        border: 1px solid var(--dtr-border-soft);
        background: var(--dtr-surface-soft);
    }
    .settings-page .face-status.missing { color: #b45309; border-color: rgba(217, 119, 6, 0.3); }
    .settings-page .face-status.ready { color: #166534; border-color: rgba(22, 163, 74, 0.28); }
    .settings-page .office-status {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.85rem;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        border: 1px solid var(--dtr-border-soft);
        background: var(--dtr-surface-soft);
        color: var(--dtr-text);
    }
    .settings-page .office-status.pending {
        color: #a16207;
        border-color: rgba(217, 119, 6, 0.35);
    }
    .settings-page .office-status.rejected {
        color: #b91c1c;
        border-color: rgba(220, 38, 38, 0.35);
    }
    .settings-page .face-enroll-remark {
        margin-top: 0.6rem;
        padding: 0.55rem 0.7rem;
        border-radius: 10px;
        background: rgba(220, 38, 38, 0.08);
        border: 1px solid rgba(220, 38, 38, 0.28);
        color: #7f1d1d;
        font-size: 0.8rem;
        line-height: 1.4;
    }
    .settings-page .face-enroll-remark i { color: #dc2626; }
    html[data-theme="dark"] .settings-page .face-enroll-remark {
        background: rgba(239, 68, 68, 0.14);
        border-color: rgba(248, 113, 113, 0.38);
        color: #fecaca;
    }
    html[data-theme="dark"] .settings-page .face-enroll-remark i { color: #f87171; }
    #faceEnrollVideo { width: 100%; max-width: 520px; border-radius: 10px; }
    #faceEnrollCanvas { position: absolute; top: 0; left: 0; width: 100%; max-width: 520px; pointer-events: none; }
</style>
@endpush

@section('content')
<div class="settings-page">
    <h1 class="page-title">Settings</h1>
    <p class="page-sub">Account and preferences.</p>

    <div class="card">
        <div class="card-body">
            <h2 class="section-title"><i class="bi bi-person"></i> Account</h2>
            <ul class="settings-list">
                <li>
                    <div>
                        <span class="setting-label">Password</span>
                        <div class="setting-desc">Update your login password (min 8 characters)</div>
                    </div>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class="bi bi-key me-1"></i> Change password
                    </button>
                </li>
                <li>
                    <div>
                        <span class="setting-label">Face enrollment</span>
                        <div class="setting-desc">Capture or update your face verification data for camera login checks.</div>
                        @php($student = auth()->guard('student')->user())
                        @if($student && empty($student->face_encoding))
                            <div class="face-status missing mt-2"><i class="bi bi-camera-video-off"></i> Missing</div>
                            <div class="face-enroll-remark">
                                <i class="bi bi-info-circle me-1"></i>
                                Remark: No face data is saved yet. This usually happens when registration is done without camera permission/device camera.
                                Use <strong>Enroll / Re-enroll</strong> to complete setup for camera-based attendance verification.
                            </div>
                        @else
                            <div class="face-status ready mt-2"><i class="bi bi-camera-fill"></i> Enrolled</div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#faceEnrollmentModal">
                        <i class="bi bi-camera me-1"></i> Enroll / Re-enroll
                    </button>
                </li>
                <li>
                    <div>
                        <span class="setting-label">Assigned office</span>
                        <div class="setting-desc">Set your assigned office. New assignments and re-assignments are verified by admin.</div>
                        @php($currentOffice = trim((string) ($student?->assigned_office ?? '')))
                        <div class="mt-2">
                            <span class="office-status">
                                <i class="bi bi-building"></i>
                                Current: {{ $currentOffice !== '' ? $currentOffice : 'Not assigned yet' }}
                            </span>
                        </div>
                        @if(!empty($latestOfficeRequest))
                            @php($requestStatus = (string) $latestOfficeRequest->status)
                            <div class="mt-2">
                                <span class="office-status {{ $requestStatus }}">
                                    <i class="bi bi-clock-history"></i>
                                    Latest request: {{ ucfirst($requestStatus) }} to {{ $latestOfficeRequest->requested_office }}
                                </span>
                            </div>
                            <div class="setting-desc mt-2">
                                @if($latestOfficeRequest->admin_remarks)
                                    Admin remarks: {{ $latestOfficeRequest->admin_remarks }}
                                @endif
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#officeAssignmentModal">
                        <i class="bi bi-arrow-left-right me-1"></i> Request update
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('student.password.change.submit') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="changePasswordModalLabel">Change password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current password</label>
                            <div class="password-toggle-wrap">
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required autocomplete="current-password" placeholder="Your current password">
                                <button type="button" class="password-toggle-btn" data-password-toggle aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">New password</label>
                            <div class="password-toggle-wrap">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Min 8 characters">
                                <button type="button" class="password-toggle-btn" data-password-toggle aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label for="password_confirmation" class="form-label">Confirm new password</label>
                            <div class="password-toggle-wrap">
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Same as above">
                                <button type="button" class="password-toggle-btn" data-password-toggle aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="faceEnrollmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="modal-title mb-0">Face Enrollment</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="faceEnrollRefreshBtn" aria-label="Refresh camera">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh Camera
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" aria-label="Cancel">Cancel</button>
                    </div>
                </div>
                <div class="modal-body text-center">
                    <p class="text-muted small mb-2">Look directly at the camera in a well-lit area. Blink naturally and keep your head steady. Enrollment will submit automatically once your live face is verified.</p>
                    <div class="position-relative d-inline-block">
                        <video id="faceEnrollVideo" autoplay playsinline></video>
                        <canvas id="faceEnrollCanvas"></canvas>
                    </div>
                    <div id="faceEnrollStatus" class="mt-3 text-muted">Initializing camera...</div>
                    <div id="faceEnrollLiveness" class="mt-2"><small class="text-info">Blink detection: <span id="faceEnrollBlinkCount">0</span> blinks detected</small></div>
                    <form id="faceEnrollForm" action="{{ route('student.settings.face-enrollment') }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="face_encoding" id="faceEnrollEncoding">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="officeAssignmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('student.settings.office-request') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Request office assignment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Assigned office</label>
                            <select name="requested_office" class="form-select" required>
                                <option value="">Select office</option>
                                @foreach(($officeOptions ?? []) as $officeOption)
                                    <option value="{{ $officeOption }}">{{ $officeOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Remarks (reason for assignment/re-assignment)</label>
                            <textarea name="student_remarks" rows="4" class="form-control" maxlength="1000" placeholder="Example: Re-assigned by deployment memo to Registrar front desk." required></textarea>
                        </div>
                        <p class="text-muted small mt-2 mb-0">Your request will be reflected in coordinator filters after admin approval.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary dtr-mbtn--rose"><i class="bi bi-send me-1" aria-hidden="true"></i>Submit for verification</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>window.FACE_API_MODEL_BASE = "{{ asset('vendor/face-api/model') }}";</script>
<script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
<script src="{{ asset('js/face-recognition.js') }}"></script>
<script>
(function () {
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
})();

(function () {
    var modalEl = document.getElementById('faceEnrollmentModal');
    if (!modalEl) return;
    var statusEl = document.getElementById('faceEnrollStatus');
    var blinkEl = document.getElementById('faceEnrollBlinkCount');
    var refreshBtn = document.getElementById('faceEnrollRefreshBtn');
    var intervalRef = null;
    var isSubmitting = false;

    async function startFaceEnrollment() {
        if (intervalRef) {
            clearTimeout(intervalRef);
            intervalRef = null;
        }
        isSubmitting = false;
        if (blinkEl) blinkEl.textContent = '0';
        if (refreshBtn) refreshBtn.disabled = true;
        statusEl.textContent = 'Loading face recognition models...';
        var loaded = await faceRecognition.loadModels();
        if (!loaded) {
            statusEl.textContent = 'Could not load face recognition models.';
            if (refreshBtn) refreshBtn.disabled = false;
            return;
        }
        var camera = await faceRecognition.initializeCamera(
            document.getElementById('faceEnrollVideo'),
            document.getElementById('faceEnrollCanvas')
        );
        if (!camera || !camera.ok) {
            statusEl.textContent = (camera && camera.message) ? camera.message : 'Camera unavailable.';
            if (refreshBtn) refreshBtn.disabled = false;
            return;
        }
        if (refreshBtn) refreshBtn.disabled = false;
        statusEl.textContent = 'Camera ready. Detecting your face...';
        faceRecognition.resetLiveness();
        var startedAt = Date.now();
        var maxWaitTime = 12000;

        var runLoop = async function () {
            if (!modalEl.classList.contains('show') || isSubmitting) return;
            var detection = await faceRecognition.detectFace(false, { drawLandmarks: true, detectorProfile: 'fast' });
            var elapsed = Date.now() - startedAt;
            var nextDelay = 260;

            if (detection) {
                var blinkCount = faceRecognition.blinkCount || 0;
                if (blinkEl) blinkEl.textContent = String(blinkCount);
                var isLive = faceRecognition.checkLiveness(detection);
                nextDelay = 420;

                if (isLive) {
                    isSubmitting = true;
                    statusEl.innerHTML = '<p class="text-success mb-1"><i class="bi bi-check-circle me-2"></i>Live face verified.</p><p class="text-muted small mb-0">Capturing enrollment data...</p>';
                    try {
                        var encoding = await faceRecognition.captureFaceEncoding({
                            sampleCount: 3,
                            intervalMs: 200,
                            useCacheMs: 1200,
                            drawLandmarks: false,
                            detectorProfile: 'normal'
                        });
                        document.getElementById('faceEnrollEncoding').value = encoding;
                        document.getElementById('faceEnrollForm').submit();
                    } catch (e) {
                        statusEl.textContent = (e && e.message) ? e.message : 'Capture failed. Please try again.';
                        isSubmitting = false;
                        if (refreshBtn) refreshBtn.disabled = false;
                    }
                    return;
                }

                if (blinkCount > 0) {
                    statusEl.innerHTML = '<p class="text-info mb-0"><i class="bi bi-eye me-2"></i>Face detected. Hold still while genuinity is checked...</p>';
                } else {
                    statusEl.innerHTML = '<p class="text-info mb-0"><i class="bi bi-person me-2"></i>Face detected. Please blink naturally to continue.</p>';
                }
                if (elapsed > maxWaitTime) {
                    statusEl.innerHTML = '<p class="text-warning mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Still verifying liveness. Improve lighting and keep your whole face in frame.</p>';
                }
            } else {
                statusEl.innerHTML = '<p class="text-warning mb-0"><i class="bi bi-exclamation-triangle me-2"></i>No face detected. Position your face in front of the camera.</p>';
                if (blinkEl) blinkEl.textContent = '0';
                nextDelay = 220;
            }
            intervalRef = setTimeout(runLoop, nextDelay);
        };

        runLoop();
    }

    function stopFaceEnrollment() {
        if (intervalRef) {
            clearTimeout(intervalRef);
            intervalRef = null;
        }
        faceRecognition.stopCamera();
        isSubmitting = false;
        if (refreshBtn) refreshBtn.disabled = false;
    }

    async function refreshFaceEnrollment() {
        if (refreshBtn) refreshBtn.disabled = true;
        stopFaceEnrollment();
        statusEl.textContent = 'Refreshing camera...';
        await startFaceEnrollment();
    }

    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            refreshFaceEnrollment();
        });
    }

    modalEl.addEventListener('shown.bs.modal', startFaceEnrollment);
    modalEl.addEventListener('hidden.bs.modal', stopFaceEnrollment);
})();
</script>
@endpush
