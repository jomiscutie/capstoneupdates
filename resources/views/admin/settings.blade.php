@extends('layouts.admin')

@section('title', 'Admin Settings')

@section('content')
<h1 class="page-title">Admin Settings</h1>
<p class="page-sub text-center">Essential admin controls.</p>

<div class="row g-4">
    <div class="col-xl-4 col-md-6">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <div class="eyebrow">System</div>
                <div class="settings-list">
                    <div class="settings-item">
                        <span class="settings-label">Session timeout</span>
                        <span class="settings-value">{{ $sessionLifetime }} min</span>
                    </div>
                    <div class="settings-item">
                        <span class="settings-label">Default OJT hours</span>
                        <span class="settings-value">{{ number_format($defaultRequiredHours, 1) }} hrs</span>
                    </div>
                    <div class="settings-item">
                        <span class="settings-label">Theme</span>
                        <span class="settings-value">Light / Dark</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <div class="eyebrow">Coordinator Access</div>
                <div class="settings-actions">
                    <a href="{{ route('admin.coordinators') }}" class="settings-link">
                        <span>Manage coordinators</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="{{ route('admin.coordinators') }}" class="settings-link">
                        <span>Reset coordinator passwords</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-12">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <div class="eyebrow">Security</div>
                <div class="settings-actions">
                    <button type="button" class="settings-link settings-link-button" data-bs-toggle="modal" data-bs-target="#adminPasswordModal">
                        <span>Change admin password</span>
                        <i class="bi bi-shield-lock"></i>
                    </button>
                    <div class="settings-item settings-item-plain">
                        <span class="settings-label">Session protection</span>
                        <span class="settings-value">Enabled</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="card settings-card">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div>
                        <div class="eyebrow mb-2">Kiosk Setup</div>
                        <p class="mb-0 text-muted small">Generate a ready-to-use kiosk URL with station details, then copy or open it.</p>
                    </div>
                    @if($kioskAccessKey === '')
                        <span class="badge text-bg-danger">Missing DTR_KIOSK_ACCESS_KEY</span>
                    @else
                        <span class="badge text-bg-success">Kiosk key configured</span>
                    @endif
                </div>

                <div class="kiosk-setup-grid">
                    <div>
                        <label class="form-label settings-form-label" for="kioskStationId">Station ID</label>
                        <input type="text" id="kioskStationId" class="form-control" value="station-1" autocomplete="off">
                    </div>
                    <div>
                        <label class="form-label settings-form-label" for="kioskStationName">Station Name</label>
                        <input type="text" id="kioskStationName" class="form-control" value="Main Gate Kiosk" autocomplete="off">
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label settings-form-label" for="kioskUrlPreview">Kiosk URL</label>
                    <textarea id="kioskUrlPreview" class="form-control" rows="2" readonly></textarea>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="button" class="btn btn-primary" id="copyKioskUrlBtn" @if($kioskAccessKey === '') disabled @endif>
                        <i class="bi bi-clipboard me-1"></i> Copy URL
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="openKioskUrlBtn" @if($kioskAccessKey === '') disabled @endif>
                        <i class="bi bi-box-arrow-up-right me-1"></i> Open/Test Kiosk
                    </button>
                    <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#kioskKeyRotateModal">
                        <i class="bi bi-arrow-repeat me-1"></i> Regenerate Key
                    </button>
                </div>
                @if($kioskAccessKey === '')
                    <p class="text-danger small mt-2 mb-0">Add <code>DTR_KIOSK_ACCESS_KEY=your-secret-key</code> to <code>.env</code>, then run <code>php artisan config:clear</code>.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="kioskKeyRotateModal" tabindex="-1" aria-labelledby="kioskKeyRotateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="eyebrow mb-2">Kiosk Security</div>
                    <h2 class="modal-title h5 mb-0" id="kioskKeyRotateModalLabel">Regenerate Kiosk Access Key</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    This will invalidate old kiosk URLs after you update <code>.env</code> and clear config.
                </div>
                <p class="small text-muted mb-2">Step 1: Generate and copy a new key.</p>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="button" class="btn btn-primary" id="generateKioskKeyBtn">
                        <i class="bi bi-magic me-1"></i> Generate New Key
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="copyGeneratedKioskKeyBtn" disabled>
                        <i class="bi bi-clipboard me-1"></i> Copy Key
                    </button>
                </div>
                <label class="form-label settings-form-label" for="generatedKioskKey">Generated key</label>
                <textarea id="generatedKioskKey" class="form-control" rows="2" readonly placeholder="Click Generate New Key"></textarea>
                <p class="small text-muted mt-3 mb-1">Step 2: Update your <code>.env</code>:</p>
                <pre class="small mb-2"><code>DTR_KIOSK_ACCESS_KEY=&lt;paste-generated-key&gt;</code></pre>
                <p class="small text-muted mb-0">Step 3: Run <code>php artisan config:clear</code> then refresh this page.</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adminPasswordModal" tabindex="-1" aria-labelledby="adminPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="eyebrow mb-2">Security</div>
                    <h2 class="modal-title h5 mb-0" id="adminPasswordModalLabel">Change Admin Password</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <form action="{{ route('admin.settings.password') }}" method="POST" class="settings-actions">
                    @csrf
                    <div>
                        <label class="form-label settings-form-label">Current password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label settings-form-label">New password</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div>
                        <label class="form-label settings-form-label">Confirm password</label>
                        <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .settings-card {
        min-height: 100%;
    }
    .eyebrow {
        display: inline-flex;
        align-items: center;
        padding: 0.28rem 0.65rem;
        border-radius: 999px;
        background: var(--dtr-primary-soft);
        color: var(--dtr-primary);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 1rem;
    }
    .settings-list,
    .settings-actions {
        display: grid;
        gap: 0.85rem;
    }
    .settings-item,
    .settings-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.95rem 1rem;
        border: 1px solid var(--dtr-border-soft);
        border-radius: 14px;
        background: var(--dtr-card-solid);
    }
    .settings-link {
        color: inherit;
        text-decoration: none;
        transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
    }
    .settings-link-button {
        width: 100%;
        border: 1px solid var(--dtr-border-soft);
        text-align: left;
        cursor: pointer;
    }
    .settings-link:hover {
        transform: translateY(-1px);
        border-color: rgba(45,212,191,0.28);
        background: color-mix(in srgb, var(--dtr-card-solid) 90%, var(--dtr-primary-soft) 10%);
    }
    .settings-link i {
        color: var(--dtr-primary);
    }
    .settings-label {
        color: var(--dtr-muted);
        font-size: 0.88rem;
    }
    .settings-value {
        color: var(--dtr-heading);
        font-weight: 700;
        text-align: right;
    }
    .settings-item-plain {
        cursor: default;
    }
    .settings-form-label {
        display: block;
        margin-bottom: 0.35rem;
        color: var(--dtr-muted);
        font-size: 0.85rem;
        font-weight: 600;
    }
    .admin-modal {
        background: var(--dtr-card-bg);
        border: 1px solid color-mix(in srgb, var(--dtr-primary) 26%, var(--dtr-border-soft) 74%);
        border-radius: 20px;
        box-shadow: 0 24px 55px -30px rgba(2, 6, 23, 0.65);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }
    .admin-modal .modal-header,
    .admin-modal .modal-body {
        color: var(--dtr-text);
    }
    .admin-modal .modal-header {
        padding: 1.25rem 1.25rem 0.2rem;
    }
    .admin-modal .modal-body {
        padding: 0.9rem 1.25rem 1.25rem;
    }
    .admin-modal .btn-close {
        border-radius: 10px;
        opacity: 0.85;
        padding: 0.55rem;
        background-size: 0.9rem;
        transition: background-color 0.2s ease, opacity 0.2s ease;
    }
    .admin-modal .btn-close:hover {
        opacity: 1;
        background-color: var(--dtr-hover-bg);
    }
    .admin-modal .settings-form-label {
        margin-bottom: 0.45rem;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
    }
    .admin-modal .form-control {
        border-radius: 10px;
        min-height: 48px;
        border: 1px solid color-mix(in srgb, var(--dtr-input-border) 75%, var(--dtr-primary) 25%);
        background: color-mix(in srgb, var(--dtr-input-bg) 88%, transparent 12%);
        color: var(--dtr-text);
        font-size: 0.95rem;
        padding: 0.7rem 0.85rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }
    .admin-modal .form-control:focus {
        border-color: color-mix(in srgb, var(--dtr-primary) 86%, #ffffff 14%);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--dtr-primary) 24%, transparent 76%);
        background: color-mix(in srgb, var(--dtr-input-bg) 95%, transparent 5%);
    }
    .admin-modal .btn.btn-primary {
        border-radius: 8px;
        min-height: 40px;
        font-size: 0.8625rem;
        font-weight: 600;
        letter-spacing: 0.015em;
        background: transparent !important;
        border: 1px solid color-mix(in srgb, var(--dtr-primary) 58%, var(--dtr-input-border)) !important;
        color: var(--dtr-primary) !important;
        box-shadow: none !important;
    }
    .admin-modal .btn.btn-primary:hover,
    .admin-modal .btn.btn-primary:focus {
        background: var(--dtr-primary-soft) !important;
        border-color: color-mix(in srgb, var(--dtr-primary) 72%, transparent) !important;
        color: var(--dtr-heading) !important;
    }
    .admin-modal .btn.btn-outline-secondary {
        border-radius: 8px;
        min-height: 40px;
        font-size: 0.8625rem;
        font-weight: 600;
        background: transparent !important;
        color: color-mix(in srgb, var(--dtr-muted) 78%, var(--dtr-text)) !important;
        border: 1px solid color-mix(in srgb, var(--dtr-input-border) 88%, transparent) !important;
    }
    .admin-modal .btn.btn-outline-secondary:hover:not(:disabled),
    .admin-modal .btn.btn-outline-secondary:focus {
        background: var(--dtr-hover-bg) !important;
        color: var(--dtr-heading) !important;
        border-color: color-mix(in srgb, var(--dtr-muted) 35%, var(--dtr-input-border)) !important;
    }
    html[data-theme="dark"] .admin-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    @media (max-width: 767px) {
        .settings-item,
        .settings-link {
            flex-direction: column;
            align-items: flex-start;
        }
        .settings-value {
            text-align: left;
        }
    }
    .kiosk-setup-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
    }
    @media (max-width: 767px) {
        .kiosk-setup-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var hasPasswordErrors = "{{ $errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation') ? '1' : '0' }}" === '1';
    var modalElement = document.getElementById('adminPasswordModal');
    if (!hasPasswordErrors || !modalElement || typeof bootstrap === 'undefined') return;

    bootstrap.Modal.getOrCreateInstance(modalElement).show();
})();

(function () {
    var baseUrl = "{{ $kioskBaseUrl }}";
    var kioskKey = "{{ $kioskAccessKey }}";
    var stationIdEl = document.getElementById('kioskStationId');
    var stationNameEl = document.getElementById('kioskStationName');
    var previewEl = document.getElementById('kioskUrlPreview');
    var copyBtn = document.getElementById('copyKioskUrlBtn');
    var openBtn = document.getElementById('openKioskUrlBtn');

    if (!stationIdEl || !stationNameEl || !previewEl) return;

    function buildUrl() {
        var stationId = (stationIdEl.value || 'station-1').trim();
        var stationName = (stationNameEl.value || 'Main Gate Kiosk').trim();
        var params = new URLSearchParams();
        params.set('kiosk_key', kioskKey);
        params.set('station_id', stationId);
        params.set('station_name', stationName);
        return baseUrl + '?' + params.toString();
    }

    function refreshPreview() {
        previewEl.value = buildUrl();
    }

    stationIdEl.addEventListener('input', refreshPreview);
    stationNameEl.addEventListener('input', refreshPreview);
    refreshPreview();

    if (copyBtn) {
        copyBtn.addEventListener('click', async function () {
            try {
                await navigator.clipboard.writeText(previewEl.value);
                copyBtn.innerHTML = '<i class="bi bi-check2 me-1"></i> Copied';
                setTimeout(function () {
                    copyBtn.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy URL';
                }, 1400);
            } catch (e) {
                previewEl.focus();
                previewEl.select();
            }
        });
    }

    if (openBtn) {
        openBtn.addEventListener('click', function () {
            window.open(previewEl.value, '_blank', 'noopener');
        });
    }
})();

(function () {
    var generateBtn = document.getElementById('generateKioskKeyBtn');
    var copyBtn = document.getElementById('copyGeneratedKioskKeyBtn');
    var output = document.getElementById('generatedKioskKey');
    if (!generateBtn || !copyBtn || !output) return;

    function randomHex(bytes) {
        var arr = new Uint8Array(bytes);
        crypto.getRandomValues(arr);
        return Array.from(arr, function (b) { return b.toString(16).padStart(2, '0'); }).join('');
    }

    generateBtn.addEventListener('click', function () {
        output.value = randomHex(32);
        copyBtn.disabled = false;
    });

    copyBtn.addEventListener('click', async function () {
        if (!output.value) return;
        try {
            await navigator.clipboard.writeText(output.value);
            copyBtn.innerHTML = '<i class="bi bi-check2 me-1"></i> Copied';
            setTimeout(function () {
                copyBtn.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy Key';
            }, 1400);
        } catch (e) {
            output.focus();
            output.select();
        }
    });
})();
</script>
@endpush
