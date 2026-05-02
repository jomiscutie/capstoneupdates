<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') - NORSU OJT DTR</title>
    <link rel="icon" type="image/png" href="{{ asset('images/coordinator-icon.png') }}">
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
            --dtr-primary: #0d9488;
            --dtr-primary-dark: #0f766e;
            --dtr-primary-soft: rgba(20, 184, 166, 0.12);
            --dtr-accent: #14b8a6;
            --dtr-sidebar-bg: #fafafa;
            --dtr-sidebar-border: #e5e7eb;
            --dtr-main-bg: #f5f5f5;
            --dtr-card-bg: #ffffff;
            --dtr-card-solid: #ffffff;
            --dtr-text: #111827;
            --dtr-heading: #0b1220;
            --dtr-muted: #64748b;
            --dtr-border-strong: rgba(15,23,42,0.14);
            --dtr-row-divider: rgba(148,163,184,0.22);
            --dtr-border-soft: rgba(0,0,0,0.08);
            --dtr-hover-bg: rgba(15,23,42,0.06);
            --dtr-surface-soft: #f8fafc;
            --dtr-surface-2: #f1f5f9;
            --dtr-shadow-soft: 0 1px 3px rgba(15,23,42,0.08);
            --dtr-shadow-strong: 0 4px 24px rgba(15,23,42,0.1);
            --dtr-input-bg: #ffffff;
            --dtr-input-border: #d1d5db;
            --sidebar-width: 220px;
            --dtr-radius: 8px;
            --dtr-font: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        html[data-theme="dark"] {
            --dtr-primary: #2dd4bf;
            --dtr-primary-dark: #14b8a6;
            --dtr-primary-soft: rgba(45, 212, 191, 0.14);
            --dtr-accent: #5eead4;
            --dtr-sidebar-bg: #0f172a;
            --dtr-sidebar-border: #273449;
            --dtr-main-bg: #020617;
            --dtr-card-bg: #111827;
            --dtr-card-solid: #111827;
            --dtr-text: #e5edf7;
            --dtr-heading: #f8fafc;
            --dtr-muted: #94a3b8;
            --dtr-border-strong: rgba(248,250,252,0.14);
            --dtr-row-divider: rgba(71,85,105,0.45);
            --dtr-border-soft: rgba(148,163,184,0.22);
            --dtr-hover-bg: rgba(148,163,184,0.14);
            --dtr-surface-soft: #1e293b;
            --dtr-surface-2: #172033;
            --dtr-shadow-soft: 0 1px 3px rgba(0,0,0,0.35);
            --dtr-shadow-strong: 0 10px 40px rgba(0,0,0,0.45);
            --dtr-input-bg: #0f172a;
            --dtr-input-border: #475569;
        }
        * { box-sizing: border-box; }
        html { color-scheme: light; overflow-x: clip; }
        html[data-theme="dark"] { color-scheme: dark; }
        body {
            margin: 0;
            font-family: var(--dtr-font);
            min-height: 100vh;
            background: var(--dtr-main-bg);
            color: var(--dtr-text);
            overflow-x: hidden;
        }
        body::before {
            display: none;
        }
        .layout-wrap { display: flex; min-height: 100vh; width: 100%; max-width: 100%; }
        .sidebar {
            width: var(--sidebar-width);
            background: var(--dtr-sidebar-bg);
            border-right: 1px solid var(--dtr-sidebar-border);
            position: fixed;
            inset: 0 auto 0 0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform 0.25s ease, width 0.25s ease;
        }
        .sidebar-brand {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid var(--dtr-sidebar-border);
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }
        .sidebar-brand img { width: 38px; height: 38px; object-fit: contain; }
        .sidebar-brand .brand-copy strong { display: block; font-size: 1.05rem; letter-spacing: 0.03em; color: var(--dtr-heading); }
        .sidebar-collapse-btn,
        .sidebar-toggle {
            border: 1px solid var(--dtr-sidebar-border);
            border-radius: var(--dtr-radius);
            background: var(--dtr-card-bg);
            color: var(--dtr-text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .sidebar-collapse-btn { width: 30px; height: 30px; margin-left: auto; }
        .sidebar-toggle { display: none; position: fixed; top: 1rem; left: 1rem; z-index: 1001; width: 40px; height: 40px; }
        .sidebar-nav { padding: 0.75rem 0; flex: 1; overflow-y: auto; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            margin: 0 0.6rem;
            padding: 0.65rem 0.9rem;
            color: var(--dtr-text);
            text-decoration: none;
            border-radius: var(--dtr-radius);
            font-size: 0.92rem;
            font-weight: 500;
        }
        .sidebar-nav a .nav-text { flex: 1; min-width: 0; }
        .sidebar-nav a .nav-badge {
            margin-left: auto;
            min-width: 1.3rem;
            height: 1.2rem;
            padding: 0 0.32rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.64rem;
            font-weight: 700;
            background: #dc2626;
            color: #fff;
        }
        .sidebar-nav a:hover { background: var(--dtr-hover-bg); }
        .sidebar-nav a.active { background: var(--dtr-primary-soft); color: var(--dtr-primary); }
        .sidebar-footer { border-top: 1px solid var(--dtr-sidebar-border); padding: 1rem; display: grid; gap: 0.75rem; }
        .theme-toggle-btn,
        .btn-logout {
            width: 100%;
            border: 1px solid var(--dtr-sidebar-border);
            background: transparent;
            color: var(--dtr-text);
            border-radius: var(--dtr-radius);
            padding: 0.65rem 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
            font-weight: 500;
        }
        .theme-toggle-btn { justify-content: space-between; background: var(--dtr-card-bg); }
        .theme-toggle-label { display: inline-flex; align-items: center; gap: 0.45rem; font-size: 0.82rem; font-weight: 600; }
        .theme-switch { width: 42px; height: 24px; border-radius: 999px; background: #cbd5e1; border: 1px solid rgba(15,23,42,0.15); padding: 2px; display: inline-flex; align-items: center; }
        .theme-switch-thumb { width: 18px; height: 18px; border-radius: 50%; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.2); transform: translateX(0); transition: transform 0.3s ease; }
        html[data-theme="dark"] .theme-switch-thumb { transform: translateX(18px); }
        .main-content { margin-left: var(--sidebar-width); flex: 1; min-width: 0; width: 100%; max-width: 100%; padding: 1.35rem; position: relative; }
        .page-shell { width: 100%; max-width: 1320px; margin: 0 auto; min-width: 0; }
        .card { border: 1px solid var(--dtr-border-soft); background: var(--dtr-card-bg); box-shadow: var(--dtr-shadow-soft); border-radius: var(--dtr-radius); overflow: hidden; }
        .main-content h1, .main-content h2, .main-content h3, .main-content h4, .main-content h5, .main-content h6, .fw-semibold, .fw-bold { color: var(--dtr-heading) !important; }
        .main-content .text-muted, .main-content .text-body-secondary, .main-content .small, .main-content small { color: var(--dtr-muted) !important; }
        .main-content .form-control, .main-content .form-select { background: var(--dtr-input-bg); border-color: var(--dtr-input-border); color: var(--dtr-text); }
        .main-content .list-group-item, .main-content .modal-content, .main-content .dropdown-menu { background: var(--dtr-card-bg); color: var(--dtr-text); border-color: var(--dtr-border-soft); }
        .main-content .alert { border: 1px solid var(--dtr-border-soft); border-radius: var(--dtr-radius); background: var(--dtr-card-bg); color: var(--dtr-text); box-shadow: none; }
        .main-content .table { --bs-table-bg: var(--dtr-card-bg); --bs-table-color: var(--dtr-text); --bs-table-border-color: var(--dtr-border-soft); color: var(--dtr-text); margin-bottom: 0; }
        .main-content .table > :not(caption) > * > * { background-color: var(--dtr-card-bg); color: var(--dtr-text); border-color: var(--dtr-border-soft); }
        .table-responsive { background: var(--dtr-card-solid); border: 1px solid var(--dtr-border-soft); border-radius: var(--dtr-radius); overflow: hidden; }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.3); z-index: 999; }
        .layout-collapsed .sidebar { width: 78px; }
        .layout-collapsed .main-content { margin-left: 78px; }
        .layout-collapsed .sidebar-brand .brand-copy,
        .layout-collapsed .sidebar-nav .nav-text,
        .layout-collapsed .sidebar-nav .nav-badge,
        .layout-collapsed .theme-toggle-wrap,
        .layout-collapsed .btn-logout span { display: none !important; }
        .layout-collapsed .sidebar-brand,
        .layout-collapsed .sidebar-nav a,
        .layout-collapsed .btn-logout { justify-content: center; }
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); box-shadow: 4px 0 20px rgba(0,0,0,0.08); }
            .sidebar-overlay.show { display: block; }
            .sidebar-toggle { display: flex; }
            .sidebar-collapse-btn { display: none; }
            .main-content, .layout-collapsed .main-content { margin-left: 0; padding-top: 3.5rem; }
            .layout-collapsed .sidebar { width: var(--sidebar-width); }
        }
    </style>
    @stack('styles')
</head>
<body>
@php
    $adminPendingInvalidationCount = 0;
    $adminPendingManualRequestCount = 0;
    $adminArchivedStudentsCount = 0;
    $adminMissingFaceEnrollmentCount = 0;
    $adminPendingOfficeRequestCount = 0;
    if (auth()->guard('admin')->check()) {
        try {
            $adminPendingInvalidationCount = \App\Models\Attendance::query()->where('invalidation_status', 'requested')->count();
            $adminPendingManualRequestCount = \App\Models\ManualAttendanceRequest::query()->where('status', \App\Models\ManualAttendanceRequest::STATUS_PENDING)->count();
            $adminArchivedStudentsCount = \App\Models\Student::onlyTrashed()->count();
            $adminMissingFaceEnrollmentCount = \App\Models\Student::query()->where(function ($query) {
                $query->whereNull('face_encoding')->orWhere('face_encoding', '');
            })->count();
            $adminPendingOfficeRequestCount = \App\Models\OfficeAssignmentRequest::query()->where('status', \App\Models\OfficeAssignmentRequest::STATUS_PENDING)->count();
        } catch (\Throwable $e) {
            $adminPendingInvalidationCount = 0;
            $adminPendingManualRequestCount = 0;
            $adminArchivedStudentsCount = 0;
            $adminMissingFaceEnrollmentCount = 0;
            $adminPendingOfficeRequestCount = 0;
        }
    }
@endphp
<div class="layout-wrap">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu"><i class="bi bi-list"></i></button>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/norsu-seal.png') }}" alt="NORSU">
            <div class="brand-copy"><strong>Admin</strong></div>
            <button type="button" class="sidebar-collapse-btn" id="sidebarCollapseBtn" aria-label="Collapse sidebar"><i class="bi bi-chevron-left" id="sidebarCollapseIcon"></i></button>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i><span class="nav-text">Dashboard</span></a>
            <a href="{{ route('admin.coordinators') }}" class="{{ request()->routeIs('admin.coordinators*') ? 'active' : '' }}"><i class="bi bi-people"></i><span class="nav-text">Coordinators</span></a>
            <a href="{{ route('admin.students') }}" class="{{ request()->routeIs('admin.students') ? 'active' : '' }}"><i class="bi bi-mortarboard"></i><span class="nav-text">Students</span></a>
            <a href="{{ route('admin.office-requests') }}" class="{{ request()->routeIs('admin.office-requests*') ? 'active' : '' }}"><i class="bi bi-building-check"></i><span class="nav-text">Office Requests</span>@if($adminPendingOfficeRequestCount > 0)<span class="nav-badge">{{ $adminPendingOfficeRequestCount > 99 ? '99+' : $adminPendingOfficeRequestCount }}</span>@endif</a>
            <a href="{{ route('admin.students.archived') }}" class="{{ request()->routeIs('admin.students.archived') ? 'active' : '' }}"><i class="bi bi-archive"></i><span class="nav-text">Archived students</span>@if($adminArchivedStudentsCount > 0)<span class="nav-badge">{{ $adminArchivedStudentsCount > 99 ? '99+' : $adminArchivedStudentsCount }}</span>@endif</a>
            <a href="{{ route('admin.invalidations') }}" class="{{ request()->routeIs('admin.invalidations*') ? 'active' : '' }}"><i class="bi bi-shield-exclamation"></i><span class="nav-text">Invalidations</span>@if($adminPendingInvalidationCount > 0)<span class="nav-badge">{{ $adminPendingInvalidationCount > 99 ? '99+' : $adminPendingInvalidationCount }}</span>@endif</a>
            <a href="{{ route('admin.manual.requests') }}" class="{{ request()->routeIs('admin.manual.requests*') ? 'active' : '' }}"><i class="bi bi-journal-check"></i><span class="nav-text">Manual Requests</span>@if($adminPendingManualRequestCount > 0)<span class="nav-badge">{{ $adminPendingManualRequestCount > 99 ? '99+' : $adminPendingManualRequestCount }}</span>@endif</a>
            <a href="{{ route('admin.options') }}" class="{{ request()->routeIs('admin.options*') ? 'active' : '' }}"><i class="bi bi-sliders2"></i><span class="nav-text">Manual Adding of Sections</span></a>
            <a href="{{ route('admin.face_enrollment') }}" class="{{ request()->routeIs('admin.face_enrollment') ? 'active' : '' }}"><i class="bi bi-camera"></i><span class="nav-text">Face Enrollment</span>@if($adminMissingFaceEnrollmentCount > 0)<span class="nav-badge">{{ $adminMissingFaceEnrollmentCount > 99 ? '99+' : $adminMissingFaceEnrollmentCount }}</span>@endif</a>
            <a href="{{ route('admin.audit_logs') }}" class="{{ request()->routeIs('admin.audit_logs') ? 'active' : '' }}"><i class="bi bi-journal-text"></i><span class="nav-text">Audit Logs</span></a>
            <a href="{{ route('admin.sessions') }}" class="{{ request()->routeIs('admin.sessions*') ? 'active' : '' }}"><i class="bi bi-shield-lock"></i><span class="nav-text">Session Monitor</span></a>
            <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}"><i class="bi bi-gear"></i><span class="nav-text">Settings</span></a>
        </nav>
        <div class="sidebar-footer">
            <div class="theme-toggle-wrap">
                <button type="button" class="theme-toggle-btn" id="adminThemeToggle" aria-pressed="false" aria-label="Toggle dark mode">
                    <span class="theme-toggle-label"><i class="bi bi-sun-fill" id="adminThemeIcon"></i><span id="adminThemeLabel">Light mode</span></span>
                    <span class="theme-switch" aria-hidden="true"><span class="theme-switch-thumb"></span></span>
                </button>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST" id="adminLogoutForm">
                @csrf
                <button type="submit" class="btn-logout"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></button>
            </form>
        </div>
    </aside>
    <main class="main-content">
        <div class="page-shell">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            @yield('content')
        </div>
    </main>
</div>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/norsu-dtr-dialogs.js') }}"></script>
<script>
(function () {
    var root = document.body;
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var mobileToggle = document.getElementById('sidebarToggle');
    var collapseBtn = document.getElementById('sidebarCollapseBtn');
    var collapseIcon = document.getElementById('sidebarCollapseIcon');
    var collapseKey = 'norsu-admin-sidebar-collapsed';
    function setCollapsed(collapsed) {
        root.classList.toggle('layout-collapsed', collapsed);
        if (collapseIcon) collapseIcon.className = collapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left';
        localStorage.setItem(collapseKey, collapsed ? '1' : '0');
    }
    function openMobileSidebar(open) {
        if (!sidebar || window.innerWidth > 991) return;
        sidebar.classList.toggle('open', open);
        if (overlay) overlay.classList.toggle('show', open);
    }
    if (localStorage.getItem(collapseKey) === '1' && window.innerWidth > 991) setCollapsed(true);
    if (collapseBtn) collapseBtn.addEventListener('click', function () { setCollapsed(!root.classList.contains('layout-collapsed')); });
    if (mobileToggle) mobileToggle.addEventListener('click', function () { openMobileSidebar(!sidebar.classList.contains('open')); });
    if (overlay) overlay.addEventListener('click', function () { openMobileSidebar(false); });
})();
(function () {
    var toggle = document.getElementById('adminThemeToggle');
    var icon = document.getElementById('adminThemeIcon');
    var label = document.getElementById('adminThemeLabel');
    if (!toggle || !icon || !label) return;
    function applyTheme(theme) {
        var isDark = theme === 'dark';
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('norsu-theme', theme);
        toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        icon.className = isDark ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill';
        label.textContent = isDark ? 'Dark mode' : 'Light mode';
    }
    toggle.addEventListener('click', function () {
        var current = document.documentElement.getAttribute('data-theme') || 'light';
        applyTheme(current === 'dark' ? 'light' : 'dark');
    });
    applyTheme(document.documentElement.getAttribute('data-theme') || 'light');
})();
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register("{{ asset('sw.js') }}").catch(function () {});
}
</script>
@stack('scripts')
</body>
</html>
