<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Coordinator') - NORSU OJT DTR</title>
    <link rel="icon" type="image/png" href="{{ asset('images/coordinator-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/coordinator-icon.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/norsu-dtr-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/norsu-dtr-classic-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/norsu-dtr-dialogs.css') }}">
    <link rel="stylesheet" href="{{ asset('css/hide-native-password-reveal.css') }}">
    <script>
        (function () {
            var storedTheme = localStorage.getItem('norsu-theme');
            var preferredTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', storedTheme || preferredTheme);
        })();
    </script>
    <style>
        :root {
            --dtr-primary: #2563eb; --dtr-primary-dark: #1d4ed8; --dtr-primary-soft: rgba(37,99,235,0.12); --dtr-sidebar-bg: #fafafa; --dtr-sidebar-border: #eee; --dtr-main-bg: #f5f5f5; --dtr-card-bg: #fff; --dtr-text: #111827; --dtr-heading: #0b1220; --dtr-muted: #475569; --dtr-border-soft: rgba(0,0,0,0.06); --dtr-hover-bg: rgba(0,0,0,0.04); --dtr-surface-soft: #f8fafc; --dtr-surface-2: #f1f5f9; --dtr-shadow-soft: 0 1px 3px rgba(0,0,0,0.06); --dtr-input-bg: #fff; --dtr-input-border: #d1d5db; --dtr-radius: 8px; --sidebar-width: 220px; --dtr-font: system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
        }
        html[data-theme="dark"] {
            --dtr-sidebar-bg: #0f172a; --dtr-sidebar-border: #233143; --dtr-main-bg: #020617; --dtr-card-bg: #111827; --dtr-text: #e5edf7; --dtr-heading: #f8fbff; --dtr-muted: #9cb0cc; --dtr-border-soft: rgba(148,163,184,0.22); --dtr-hover-bg: rgba(148,163,184,0.16); --dtr-primary-soft: rgba(96,165,250,0.14); --dtr-surface-soft: #1f2937; --dtr-surface-2: #172033; --dtr-shadow-soft: 0 8px 24px rgba(0,0,0,0.3); --dtr-input-bg: #0b1220; --dtr-input-border: #334155;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--dtr-font); min-height: 100vh; color: var(--dtr-text); background: var(--dtr-main-bg); line-height: 1.5; }
        body::before { display: none; }
        .layout-wrap { display: flex; min-height: 100vh; min-width: 0; overflow-x: hidden; }
        .sidebar { width: var(--sidebar-width); background: var(--dtr-sidebar-bg); border-right: 1px solid var(--dtr-sidebar-border); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; height: 100vh; z-index: 1000; transition: transform 0.3s ease, width 0.25s ease; }
        .sidebar-brand { padding: 1.25rem 1rem; border-bottom: 1px solid var(--dtr-sidebar-border); display: flex; align-items: center; gap: 0.75rem; }
        .sidebar-brand img { width: 38px; height: 38px; object-fit: contain; }
        .sidebar-brand span { font-weight: 700; font-size: 1.05rem; letter-spacing: 0.03em; color: var(--dtr-heading); line-height: 1.2; }
        .sidebar-collapse-btn, .sidebar-toggle { border: 1px solid var(--dtr-sidebar-border); border-radius: var(--dtr-radius); background: var(--dtr-card-bg); color: var(--dtr-text); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
        .sidebar-collapse-btn { width: 30px; height: 30px; margin-left: auto; }
        .sidebar-toggle { display: none; position: fixed; top: 1rem; left: 1rem; z-index: 1001; width: 40px; height: 40px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .sidebar-nav { flex: 1; padding: 0.75rem 0; overflow-y: auto; }
        .sidebar-nav a { display: flex; align-items: center; gap: 0.7rem; padding: 0.65rem 0.9rem; margin: 0 0.6rem; color: var(--dtr-text); text-decoration: none; font-size: 0.92rem; font-weight: 500; border-radius: var(--dtr-radius); }
        .sidebar-nav a .nav-text { flex: 1; min-width: 0; }
        .sidebar-nav a .nav-badge { margin-left: auto; min-width: 1.3rem; height: 1.2rem; padding: 0 0.32rem; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.64rem; font-weight: 700; background: #dc2626; color: #fff; }
        .sidebar-nav a:hover { background: var(--dtr-hover-bg); color: var(--dtr-text); }
        .sidebar-nav a.active { background: var(--dtr-primary-soft); color: var(--dtr-primary); }
        .sidebar-footer { padding: 1rem; border-top: 1px solid var(--dtr-sidebar-border); }
        .theme-toggle-wrap { margin-bottom: 0.75rem; }
        .theme-toggle-btn, .btn-logout { width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.65rem 0.9rem; background: transparent; border: 1px solid var(--dtr-sidebar-border); border-radius: var(--dtr-radius); color: var(--dtr-text); font-size: 0.9rem; font-weight: 500; cursor: pointer; text-decoration: none; }
        .theme-toggle-btn { justify-content: space-between; background: var(--dtr-card-bg); }
        .theme-toggle-label { display: inline-flex; align-items: center; gap: 0.45rem; font-size: 0.82rem; font-weight: 600; }
        .theme-switch { width: 42px; height: 24px; border-radius: 999px; background: #cbd5e1; border: 1px solid rgba(15,23,42,0.15); padding: 2px; display: inline-flex; align-items: center; }
        .theme-switch-thumb { width: 18px; height: 18px; border-radius: 50%; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.2); transform: translateX(0); transition: transform 0.3s ease; }
        html[data-theme="dark"] .theme-switch-thumb { transform: translateX(18px); }
        .main-content { flex: 1; margin-left: var(--sidebar-width); min-height: 100vh; min-width: 0; padding: clamp(1rem, 4vw, 1.5rem); width: 100%; }
        .main-content, .main-content p, .main-content h1, .main-content h2, .main-content h3, .main-content h4, .main-content h5, .main-content h6, .main-content label, .main-content small, .main-content li, .main-content td, .main-content th { color: var(--dtr-text); }
        .main-content .text-muted, .main-content .text-secondary, .main-content .form-text, .main-content .text-body-secondary { color: var(--dtr-muted) !important; }
        .main-content .card, .main-content .card-body, .main-content .list-group-item, .main-content .modal-content, .main-content .dropdown-menu { background-color: var(--dtr-card-bg); color: var(--dtr-text); border-color: var(--dtr-border-soft); }
        .main-content .form-control, .main-content .form-select { color: var(--dtr-text); background-color: var(--dtr-input-bg); border-color: var(--dtr-input-border); }
        .main-content .table > :not(caption) > * > * { background-color: var(--dtr-card-bg); border-color: var(--dtr-border-soft); color: var(--dtr-text); }
        .card { background: var(--dtr-card-bg); border-radius: var(--dtr-radius); box-shadow: var(--dtr-shadow-soft); border: 1px solid var(--dtr-border-soft); overflow: hidden; }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.3); z-index: 999; }
        .layout-collapsed .sidebar { width: 78px; }
        .layout-collapsed .main-content { margin-left: 78px; }
        .layout-collapsed .sidebar-brand span, .layout-collapsed .sidebar-nav .nav-text, .layout-collapsed .sidebar-nav .nav-badge, .layout-collapsed .theme-toggle-wrap, .layout-collapsed .btn-logout span { display: none !important; }
        .layout-collapsed .sidebar-brand, .layout-collapsed .sidebar-nav a, .layout-collapsed .btn-logout { justify-content: center; }
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); box-shadow: 4px 0 20px rgba(0,0,0,0.08); }
            .sidebar-overlay.show { display: block; }
            .sidebar-toggle { display: flex; }
            .sidebar-collapse-btn { display: none; }
            .main-content, .layout-collapsed .main-content { margin-left: 0; padding-top: 3.5rem; }
            .layout-collapsed .sidebar { width: var(--sidebar-width); }
        }
        @stack('styles')
    </style>
</head>
<body>
@php
    $coordinatorPendingInvalidationCount = 0;
    $coordinatorPendingVerificationCount = 0;
    $coordinatorPendingOjtCompletionCount = 0;
    $coordinatorPendingManualRequestCount = 0;
    if (auth()->guard('coordinator')->check()) {
        try {
            $coordinator = auth()->guard('coordinator')->user();
            $studentIds = \App\Models\Student::forCoordinator($coordinator)->pluck('id');
            $coordinatorPendingVerificationCount = \App\Models\Student::query()->whereIn('id', $studentIds)->pendingVerification()->count();
            $coordinatorPendingInvalidationCount = \App\Models\Attendance::query()->whereIn('student_id', $studentIds)->where('invalidation_status', 'requested')->count();
            $coordinatorPendingManualRequestCount = \App\Models\ManualAttendanceRequest::query()->whereIn('student_id', $studentIds)->where('status', \App\Models\ManualAttendanceRequest::STATUS_PENDING)->count();
            $coordinatorPendingOjtCompletionCount = \App\Models\Student::forCoordinator($coordinator)->verified()->with('activeTermAssignment')->get()->filter(function ($student) {
                $assignment = $student->activeTermAssignment;
                return $assignment && $student->hasReachedRequiredHours($assignment) && ! $student->isOjtCompletionConfirmed($assignment);
            })->count();
        } catch (\Throwable $e) {
            $coordinatorPendingInvalidationCount = 0;
            $coordinatorPendingVerificationCount = 0;
            $coordinatorPendingOjtCompletionCount = 0;
            $coordinatorPendingManualRequestCount = 0;
        }
    }
@endphp
<div class="layout-wrap">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu"><i class="bi bi-list"></i></button>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/norsu-seal.png') }}" alt="NORSU">
            <span>Coordinator</span>
            <button type="button" class="sidebar-collapse-btn" id="sidebarCollapseBtn" aria-label="Collapse sidebar"><i class="bi bi-chevron-left" id="sidebarCollapseIcon"></i></button>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('coordinator.dashboard') }}" class="{{ request()->routeIs('coordinator.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i><span class="nav-text">Dashboard</span></a>
            <a href="{{ route('coordinator.absent.today') }}" class="{{ request()->routeIs('coordinator.absent*') ? 'active' : '' }}"><i class="bi bi-calendar-x"></i><span class="nav-text">Absent Today</span></a>
            <a href="{{ route('coordinator.pending.verification') }}" class="{{ request()->routeIs('coordinator.pending*') ? 'active' : '' }}"><i class="bi bi-person-check"></i><span class="nav-text">Pending Verification</span>@if($coordinatorPendingVerificationCount > 0)<span class="nav-badge">{{ $coordinatorPendingVerificationCount > 99 ? '99+' : $coordinatorPendingVerificationCount }}</span>@endif</a>
            <a href="{{ route('coordinator.students') }}" class="{{ request()->routeIs('coordinator.students') ? 'active' : '' }}"><i class="bi bi-people"></i><span class="nav-text">Enrolled Students</span></a>
            <a href="{{ route('coordinator.attendance.logs') }}" class="{{ request()->routeIs('coordinator.attendance.logs') ? 'active' : '' }}"><i class="bi bi-clock-history"></i><span class="nav-text">Daily Time Record</span>@if($coordinatorPendingInvalidationCount > 0)<span class="nav-badge">{{ $coordinatorPendingInvalidationCount > 99 ? '99+' : $coordinatorPendingInvalidationCount }}</span>@endif</a>
            <a href="{{ route('coordinator.manual.requests') }}" class="{{ request()->routeIs('coordinator.manual.requests*') ? 'active' : '' }}"><i class="bi bi-journal-check"></i><span class="nav-text">Manual Requests</span>@if($coordinatorPendingManualRequestCount > 0)<span class="nav-badge">{{ $coordinatorPendingManualRequestCount > 99 ? '99+' : $coordinatorPendingManualRequestCount }}</span>@endif</a>
            <a href="{{ route('coordinator.attendance.analytics') }}" class="{{ request()->routeIs('coordinator.attendance.analytics') ? 'active' : '' }}"><i class="bi bi-bar-chart-line"></i><span class="nav-text">Attendance Analytics</span></a>
            <a href="{{ route('coordinator.ojt.completion') }}" class="{{ request()->routeIs('coordinator.ojt*') ? 'active' : '' }}"><i class="bi bi-patch-check"></i><span class="nav-text">OJT Completion</span>@if($coordinatorPendingOjtCompletionCount > 0)<span class="nav-badge">{{ $coordinatorPendingOjtCompletionCount > 99 ? '99+' : $coordinatorPendingOjtCompletionCount }}</span>@endif</a>
            <a href="{{ route('coordinator.generate.report') }}" class="{{ request()->routeIs('coordinator.generate*') ? 'active' : '' }}"><i class="bi bi-file-earmark-pdf"></i><span class="nav-text">Generate Report</span></a>
            <a href="{{ route('coordinator.settings') }}" class="{{ request()->routeIs('coordinator.settings') ? 'active' : '' }}"><i class="bi bi-gear"></i><span class="nav-text">Settings</span></a>
        </nav>
        <div class="sidebar-footer">
            <div class="theme-toggle-wrap">
                <button type="button" class="theme-toggle-btn" id="themeToggle" aria-pressed="false" aria-label="Toggle dark mode">
                    <span class="theme-toggle-label"><i class="bi bi-sun-fill" id="themeIcon"></i><span id="themeLabel">Light mode</span></span>
                    <span class="theme-switch" aria-hidden="true"><span class="theme-switch-thumb"></span></span>
                </button>
            </div>
            <form action="{{ route('coordinator.logout') }}" method="POST" class="d-inline w-100" id="coordinatorLogoutForm">@csrf<button type="submit" class="btn-logout w-100"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></button></form>
        </div>
    </aside>
    <main class="main-content">
        <div id="coordinatorOfflineBanner" class="coordinator-offline-banner" role="status" aria-live="polite" hidden><i class="bi bi-wifi-off" aria-hidden="true"></i><span><strong>No network.</strong> Open the app using <strong>localhost</strong> or your LAN if the server is on this machine. Reports and live data need the server online.</span></div>
        @yield('content')
    </main>
</div>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/norsu-dtr-dialogs.js') }}"></script>
<script>
(function () {
    var root = document.body, sidebar = document.getElementById('sidebar'), overlay = document.getElementById('sidebarOverlay'), mobileToggle = document.getElementById('sidebarToggle'), collapseBtn = document.getElementById('sidebarCollapseBtn'), collapseIcon = document.getElementById('sidebarCollapseIcon'), collapseKey = 'norsu-coordinator-sidebar-collapsed';
    function setCollapsed(collapsed) { root.classList.toggle('layout-collapsed', collapsed); if (collapseIcon) collapseIcon.className = collapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left'; localStorage.setItem(collapseKey, collapsed ? '1' : '0'); }
    function openMobileSidebar(open) { if (!sidebar || window.innerWidth > 991) return; sidebar.classList.toggle('open', open); if (overlay) overlay.classList.toggle('show', open); }
    if (localStorage.getItem(collapseKey) === '1' && window.innerWidth > 991) setCollapsed(true);
    if (collapseBtn) collapseBtn.addEventListener('click', function () { setCollapsed(!root.classList.contains('layout-collapsed')); });
    if (mobileToggle) mobileToggle.addEventListener('click', function () { openMobileSidebar(!sidebar.classList.contains('open')); });
    if (overlay) overlay.addEventListener('click', function () { openMobileSidebar(false); });
})();
(function () {
    var toggle = document.getElementById('themeToggle'), icon = document.getElementById('themeIcon'), label = document.getElementById('themeLabel');
    if (!toggle || !icon || !label) return;
    function applyTheme(theme) { var isDark = theme === 'dark'; document.documentElement.setAttribute('data-theme', theme); localStorage.setItem('norsu-theme', theme); toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false'); icon.className = isDark ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill'; label.textContent = isDark ? 'Dark mode' : 'Light mode'; }
    toggle.addEventListener('click', function () { applyTheme((document.documentElement.getAttribute('data-theme') || 'light') === 'dark' ? 'light' : 'dark'); });
    applyTheme(document.documentElement.getAttribute('data-theme') || 'light');
})();
(function () {
    var banner = document.getElementById('coordinatorOfflineBanner');
    function syncOffline() {
        if (!banner) return;
        if (typeof navigator !== 'undefined' && !navigator.onLine) {
            banner.hidden = false;
            banner.classList.add('show');
        } else {
            banner.classList.remove('show');
            banner.hidden = true;
        }
    }
    window.addEventListener('online', syncOffline);
    window.addEventListener('offline', syncOffline);
    syncOffline();
})();
if ('serviceWorker' in navigator) navigator.serviceWorker.register("{{ asset('sw.js') }}").catch(function () {});
</script>
@stack('scripts')
</body>
</html>
