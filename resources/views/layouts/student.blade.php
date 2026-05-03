<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4f46e5">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/app-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/app-icon.png') }}">
    <title>@yield('title', 'Student') - NORSU OJT DTR</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/norsu-dtr-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/norsu-dtr-classic-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/norsu-dtr-dialogs.css') }}">
    <link rel="stylesheet" href="{{ asset('css/norsu-dtr-modal-buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/norsu-dtr-theme-toggle.css') }}">
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
            --dtr-primary: #4f46e5;
            --dtr-primary-dark: #4338ca;
            --dtr-primary-soft: rgba(79, 70, 229, 0.12);
            --dtr-sidebar-bg: #fafafa;
            --dtr-sidebar-border: #eee;
            --dtr-main-bg: #f5f5f5;
            --dtr-card-bg: #fff;
            --dtr-text: #111827;
            --dtr-heading: #0b1220;
            --dtr-muted: #475569;
            --dtr-border-soft: rgba(0,0,0,0.06);
            --dtr-hover-bg: rgba(0,0,0,0.04);
            --dtr-surface-soft: #fafafa;
            --dtr-surface-2: #f1f5f9;
            --dtr-shadow-soft: 0 1px 3px rgba(0,0,0,0.06);
            --dtr-input-bg: #ffffff;
            --dtr-input-border: #d1d5db;
            --dtr-radius: 8px;
            --sidebar-width: 220px;
            --dtr-font: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        html[data-theme="dark"] {
            --dtr-sidebar-bg: #0f172a;
            --dtr-sidebar-border: #233143;
            --dtr-main-bg: #020617;
            --dtr-card-bg: #111827;
            --dtr-text: #e5edf7;
            --dtr-heading: #f8fbff;
            --dtr-primary-soft: rgba(129, 140, 248, 0.16);
            --dtr-muted: #9cb0cc;
            --dtr-border-soft: rgba(148,163,184,0.22);
            --dtr-hover-bg: rgba(148,163,184,0.16);
            --dtr-surface-soft: #1f2937;
            --dtr-surface-2: #172033;
            --dtr-shadow-soft: 0 8px 24px rgba(0,0,0,0.3);
            --dtr-input-bg: #0b1220;
            --dtr-input-border: #334155;
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
        .btn-logout { width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.65rem 0.9rem; background: transparent; border: 1px solid var(--dtr-sidebar-border); border-radius: var(--dtr-radius); color: var(--dtr-text); font-size: 0.9rem; font-weight: 500; cursor: pointer; text-decoration: none; }
        .main-content { flex: 1; margin-left: var(--sidebar-width); min-height: 100vh; min-width: 0; padding: clamp(1rem, 4vw, 1.5rem); width: 100%; }
        .main-content, .main-content p, .main-content h1, .main-content h2, .main-content h3, .main-content h4, .main-content h5, .main-content h6, .main-content label, .main-content small, .main-content li, .main-content td, .main-content th { color: var(--dtr-text); }
        .main-content .text-muted, .main-content .text-secondary, .main-content .form-text, .main-content .text-body-secondary { color: var(--dtr-muted) !important; }
        .main-content .card, .main-content .card-body, .main-content .list-group-item, .main-content .modal-content, .main-content .dropdown-menu { background-color: var(--dtr-card-bg); color: var(--dtr-text); border-color: var(--dtr-border-soft); }
        .main-content .form-control, .main-content .form-select { color: var(--dtr-text); background-color: var(--dtr-input-bg); border-color: var(--dtr-input-border); }
        .main-content .table > :not(caption) > * > * { background-color: var(--dtr-card-bg); border-color: var(--dtr-border-soft); color: var(--dtr-text); }
        .offline-banner {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 2000;
            padding: 0.5rem 1rem;
            font-size: 0.8325rem;
            font-weight: 500;
            text-align: center;
            border-top: 1px solid color-mix(in srgb, #f59e0b 42%, var(--dtr-border-soft));
            background: color-mix(in srgb, #f59e0b 12%, var(--dtr-card-bg));
            color: var(--dtr-heading);
            box-shadow: 0 -8px 28px rgba(0,0,0,0.12);
        }
        html[data-theme="dark"] .offline-banner {
            border-top-color: rgba(251, 191, 36, 0.35);
            background: color-mix(in srgb, #f59e0b 14%, var(--dtr-sidebar-bg));
            color: var(--dtr-text);
        }
        .offline-banner.show { display: block; }
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
    $studentNeedsFaceEnrollment = false;
    if (auth()->guard('student')->check()) {
        $student = auth()->guard('student')->user();
        $studentNeedsFaceEnrollment = empty($student?->face_encoding);
    }
@endphp
<div id="offlineBanner" class="offline-banner" role="status" aria-live="polite" aria-hidden="true"><i class="bi bi-wifi-off me-2" aria-hidden="true"></i><span>You're offline. Reconnect to submit forms and load the latest data.</span></div>
<div class="layout-wrap">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu"><i class="bi bi-list"></i></button>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/norsu-seal.png') }}" alt="NORSU">
            <span>Student</span>
            <button type="button" class="sidebar-collapse-btn" id="sidebarCollapseBtn" aria-label="Collapse sidebar"><i class="bi bi-chevron-left" id="sidebarCollapseIcon"></i></button>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('student.dashboard') }}" class="{{ request()->routeIs('student.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i><span class="nav-text">Dashboard</span></a>
            <a href="{{ route('student.recentlogs') }}" class="{{ request()->routeIs('student.recentlogs') ? 'active' : '' }}"><i class="bi bi-clock-history"></i><span class="nav-text">Daily Time Record Logs</span></a>
            <a href="{{ route('student.settings') }}" class="{{ request()->routeIs('student.settings') ? 'active' : '' }}"><i class="bi bi-gear"></i><span class="nav-text">Settings</span>@if($studentNeedsFaceEnrollment)<span class="nav-badge" title="Face enrollment is required for camera verification">1</span>@endif</a>
        </nav>
        <div class="sidebar-footer">
            <div class="theme-toggle-wrap">
                <button type="button" class="theme-toggle-btn" id="themeToggle" aria-pressed="false" aria-label="Switch to dark mode">
                    <span class="theme-toggle-label"><i class="bi bi-sun" id="themeIcon" aria-hidden="true"></i><span id="themeLabel">Light</span></span>
                    <span class="theme-switch" aria-hidden="true"><span class="theme-switch-thumb"></span></span>
                </button>
            </div>
            <form action="{{ route('student.logout') }}" method="POST" class="d-inline w-100" id="studentLogoutForm">@csrf<button type="submit" class="btn-logout w-100"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></button></form>
        </div>
    </aside>
    <main class="main-content">@yield('content')
        @include('partials.norsu-page-messages')
    </main>
</div>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/norsu-dtr-dialogs.js') }}"></script>
<script>
(function () {
    var root = document.body, sidebar = document.getElementById('sidebar'), overlay = document.getElementById('sidebarOverlay'), mobileToggle = document.getElementById('sidebarToggle'), collapseBtn = document.getElementById('sidebarCollapseBtn'), collapseIcon = document.getElementById('sidebarCollapseIcon'), collapseKey = 'norsu-student-sidebar-collapsed';
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
    function applyTheme(theme) {
        var isDark = theme === 'dark';
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('norsu-theme', theme);
        toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        toggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
        icon.className = 'bi ' + (isDark ? 'bi-moon-stars' : 'bi-sun');
        label.textContent = isDark ? 'Dark' : 'Light';
    }
    toggle.addEventListener('click', function () { applyTheme((document.documentElement.getAttribute('data-theme') || 'light') === 'dark' ? 'light' : 'dark'); });
    applyTheme(document.documentElement.getAttribute('data-theme') || 'light');
})();
(function () {
    var banner = document.getElementById('offlineBanner');
    if (!banner) return;
    function sync() {
        var on = typeof navigator !== 'undefined' && navigator.onLine;
        banner.classList.toggle('show', !on);
        banner.setAttribute('aria-hidden', on ? 'true' : 'false');
    }
    window.addEventListener('online', sync);
    window.addEventListener('offline', sync);
    sync();
})();
if ('serviceWorker' in navigator) navigator.serviceWorker.register("{{ asset('sw.js') }}").catch(function () {});
</script>
@stack('scripts')
</body>
</html>
