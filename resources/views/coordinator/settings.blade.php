@extends('layouts.coordinator')

@section('title', 'Settings')

@push('styles')
<style>
    .settings-page .back-link { margin-bottom: 0.5rem; }
    .settings-page .page-title { font-size: 1.5rem; font-weight: 600; color: var(--dtr-text); margin-bottom: 0.25rem; text-align: center; }
    .settings-page .page-sub { font-size: 0.9rem; color: var(--dtr-muted); margin: 0 auto 1.5rem; text-align: center; max-width: 760px; }
    .settings-page .card-body { padding: 1.5rem; }
    .settings-page .section-title { font-size: 1rem; font-weight: 600; color: var(--dtr-text); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .settings-page .section-title i { color: var(--dtr-primary); }
    .settings-page .table thead th { background: var(--dtr-surface-soft); color: var(--dtr-muted); font-weight: 600; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 1rem; border-bottom: 1px solid var(--dtr-border-soft); }
    .settings-page .table tbody td { padding: 0.875rem 1rem; vertical-align: middle; border-bottom: 1px solid var(--dtr-border-soft); }
    .settings-page .table tbody tr:last-child td { border-bottom: none; }
    .settings-page .table tbody tr:hover { background: var(--dtr-hover-bg); }
    .settings-page .coord-settings-students-table tbody tr:hover > td {
        background: var(--dtr-hover-bg) !important;
        color: var(--dtr-text) !important;
    }
    .settings-page .coord-settings-students-table tbody tr.is-selected > td {
        background: rgba(37, 99, 235, 0.1) !important;
        color: var(--dtr-text) !important;
    }
    .settings-page .coord-settings-students-table tbody tr:hover > td *,
    .settings-page .coord-settings-students-table tbody tr.is-selected > td * {
        color: inherit;
    }
    .settings-page .coord-settings-students-table tbody tr:hover > td .text-muted,
    .settings-page .coord-settings-students-table tbody tr:hover > td .small {
        color: var(--dtr-text-soft) !important;
    }
    .settings-page .coord-settings-students-table tbody tr.is-selected > td .text-muted,
    .settings-page .coord-settings-students-table tbody tr.is-selected > td .small {
        color: var(--dtr-text-soft) !important;
    }
    .settings-page .coord-settings-students-table tbody tr > td {
        color: var(--dtr-text);
    }
    .settings-page .coord-settings-students-table tbody tr > td .fw-medium {
        color: var(--dtr-text);
    }
    .settings-page .table tbody tr.is-selected { background: rgba(37, 99, 235, 0.08); }
    .settings-page .empty-state { text-align: center; padding: 2.5rem 1.5rem; color: var(--dtr-muted); }
    .settings-page .empty-state i { font-size: 2.5rem; color: var(--dtr-muted); opacity: 0.45; margin-bottom: 0.75rem; display: block; }
    /* Inline form: required hours – minimal, visible */
    .settings-page .form-control { border: 1px solid var(--dtr-input-border); border-radius: 8px; font-size: 0.9rem; padding: 0.5rem 0.65rem; background: var(--dtr-input-bg); color: var(--dtr-text); }
    .settings-page .form-control:focus { border-color: var(--dtr-primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12); outline: none; }
    .settings-page .btn-sm { padding: 0.5rem 0.75rem; font-size: 0.85rem; font-weight: 600; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; }
    .settings-page .btn-set-password { padding: 0.45rem 0.85rem; font-size: 0.85rem; font-weight: 500; }
    .settings-page .btn-set-password,
    .settings-page .coord-settings-students-table .btn-outline-secondary,
    .settings-page .coord-settings-students-table .btn-student-delete {
        opacity: 1 !important;
    }
    /* Settings list – minimal rows */
    .settings-page .settings-list { list-style: none; padding: 0; margin: 0; }
    .settings-page .settings-list li { display: flex; align-items: center; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid var(--dtr-border-soft); gap: 1rem; }
    .settings-page .settings-list li:last-child { border-bottom: none; padding-bottom: 0; }
    .settings-page .settings-list .setting-label { font-size: 0.95rem; color: var(--dtr-text); font-weight: 500; }
    .settings-page .settings-list .setting-desc { font-size: 0.8rem; color: var(--dtr-muted); margin-top: 0.15rem; }
    .settings-page .settings-list .btn-outline-primary { padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; border-radius: 8px; }
    /* Account – original list layout, thinner density (theme tokens) */
    .settings-page .account-card .card-body { padding: 0.65rem 1rem 0.75rem; }
    .settings-page .account-card .section-title {
        margin-bottom: 0.35rem;
        font-size: 0.8125rem;
        font-weight: 600;
        gap: 0.35rem;
    }
    .settings-page .account-card .section-title i { font-size: 0.95em; }
    .settings-page .account-card .settings-list li {
        padding: 0.35rem 0 0;
        gap: 0.65rem;
        align-items: center;
        border-bottom: none;
    }
    .settings-page .account-card .settings-list .setting-label { font-size: 0.8125rem; font-weight: 600; }
    .settings-page .account-card .settings-list .setting-desc {
        font-size: 0.7rem;
        margin-top: 0.06rem;
        line-height: 1.35;
    }
    .settings-page .account-card .btn-account-password {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.38rem 0.9rem;
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1.4;
        font-family: inherit;
        cursor: pointer;
        border-radius: 8px;
        border: 1px solid color-mix(in srgb, var(--dtr-primary) 42%, var(--dtr-input-border));
        color: var(--dtr-primary);
        background: color-mix(in srgb, var(--dtr-primary) 8%, transparent);
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }
    .settings-page .account-card .btn-account-password:hover {
        color: var(--dtr-primary-dark);
        background: color-mix(in srgb, var(--dtr-primary) 14%, transparent);
        border-color: color-mix(in srgb, var(--dtr-primary) 55%, var(--dtr-input-border));
    }
    .settings-page .account-card .btn-account-password:focus-visible {
        outline: 2px solid var(--dtr-primary);
        outline-offset: 2px;
    }
    .settings-page .account-card .btn-account-password i { font-size: 1em; vertical-align: -0.06em; }
    .settings-page .section-tools { display: flex; flex-wrap: wrap; gap: 1rem; align-items: end; justify-content: space-between; margin-bottom: 1rem; }
    .settings-page .search-row { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.65rem; width: 100%; max-width: min(640px, 100%); min-width: 0; }
    .settings-page .search-inner { position: relative; flex: 1; min-width: 220px; }
    .settings-page .search-input {
        width: 100%; padding: 0.4rem 2rem 0.4rem 2rem;
        font-size: 0.875rem;
        border: none;
        border-bottom: 2px solid var(--dtr-input-border);
        border-radius: 0;
        background: transparent;
        color: var(--dtr-text);
        transition: border-color 0.15s ease, background-color 0.15s ease;
    }
    .settings-page .search-input::placeholder { color: var(--dtr-muted); }
    .settings-page .search-input:focus {
        outline: none; border-color: var(--dtr-primary);
        background-color: rgba(37, 99, 235, 0.04);
        box-shadow: none;
    }
    .settings-page .search-icon {
        position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%);
        color: var(--dtr-muted); font-size: 0.9rem; pointer-events: none;
    }
    .settings-page .search-clear {
        position: absolute; right: 0.2rem; top: 50%; transform: translateY(-50%);
        width: 24px; height: 24px; border: none; border-radius: 6px;
        background: transparent; color: var(--dtr-muted);
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; transition: color 0.2s; text-decoration: none; font-size: 0.8125rem;
    }
    .settings-page .search-clear:hover { background: transparent; color: var(--dtr-text); }
    .settings-page .btn-search {
        white-space: nowrap;
        flex-shrink: 0;
        letter-spacing: 0.01em;
    }
    .settings-page .search-hint { font-size: 0.8125rem; color: var(--dtr-muted); margin-top: 0.5rem; }
    .settings-page .bulk-card {
        display: flex; flex-wrap: wrap; gap: 0.75rem 1rem; align-items: end;
        padding: 1rem 1.1rem; margin-bottom: 1rem;
        background: var(--dtr-surface-soft);
        border: 1px solid var(--dtr-border-soft);
        border-radius: 12px;
    }
    .settings-page .bulk-field { display: flex; flex-direction: column; gap: 0.35rem; }
    .settings-page .bulk-field label {
        font-size: 0.72rem; font-weight: 600; color: var(--dtr-muted);
        text-transform: uppercase; letter-spacing: 0.06em; margin: 0;
    }
    .settings-page .bulk-count {
        min-width: 120px; font-size: 0.875rem; font-weight: 600; color: var(--dtr-text);
        padding: 0.6rem 0.85rem; border-radius: 10px; background: var(--dtr-card-bg); border: 1px solid var(--dtr-border-soft);
    }
    .settings-page .bulk-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        justify-content: flex-start;
    }
    .settings-page .selection-col { width: 56px; }
    .settings-page .select-checkbox { width: 1.1rem; height: 1.1rem; accent-color: var(--dtr-primary); cursor: pointer; }
    .settings-page .table-note { font-size: 0.8rem; color: var(--dtr-muted); margin-bottom: 0.85rem; }
    /* Password modals – clean dark modern design */
    .settings-page .modal-backdrop.show { opacity: 0.56; }
    .settings-page #changePasswordModal .modal-dialog,
    .settings-page #setPasswordModal .modal-dialog { max-width: 520px; }
    .settings-page #changePasswordModal .modal-content,
    .settings-page #setPasswordModal .modal-content {
        border: 1px solid #334155;
        border-radius: 12px;
        background: #0b1a36;
        color: #e2e8f0;
        box-shadow: 0 32px 80px -36px rgba(2, 6, 23, 0.9);
        overflow: hidden;
    }
    .settings-page #changePasswordModal .modal-header,
    .settings-page #setPasswordModal .modal-header {
        border-bottom: 1px solid #475569;
        padding: 1rem 1.15rem 0.9rem;
        background: transparent;
        align-items: center;
    }
    .settings-page #changePasswordModal .modal-title,
    .settings-page #setPasswordModal .modal-title {
        font-size: 1.03rem;
        font-weight: 700;
        color: #f8fafc;
        letter-spacing: -0.01em;
    }
    .settings-page #changePasswordModal .btn-close,
    .settings-page #setPasswordModal .btn-close {
        filter: invert(82%) sepia(8%) saturate(407%) hue-rotate(176deg) brightness(95%) contrast(90%);
        opacity: 0.75;
        padding: 0.45rem;
        border-radius: 8px;
    }
    .settings-page #changePasswordModal .btn-close:hover,
    .settings-page #setPasswordModal .btn-close:hover {
        opacity: 1;
        background: rgba(148, 163, 184, 0.12);
    }
    .settings-page #changePasswordModal .modal-body,
    .settings-page #setPasswordModal .modal-body {
        padding: 1rem 1.15rem 1.1rem;
    }
    .settings-page #changePasswordModal .form-label,
    .settings-page #setPasswordModal .form-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: #f1f5f9;
        margin-bottom: 0.45rem;
        text-transform: none;
        letter-spacing: 0;
    }
    .settings-page #changePasswordModal .text-muted,
    .settings-page #setPasswordModal .text-muted {
        color: #cbd5e1 !important;
    }
    .settings-page #changePasswordModal .form-control,
    .settings-page #setPasswordModal .form-control {
        padding: 0.68rem 0.9rem;
        border-radius: 9px;
        border: 1px solid #334155;
        font-size: 0.95rem;
        background: #08152d;
        color: #e2e8f0;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
    }
    .settings-page #changePasswordModal .form-control::placeholder,
    .settings-page #setPasswordModal .form-control::placeholder {
        color: #93a4bd;
        opacity: 1;
    }
    .settings-page #changePasswordModal .form-control:focus,
    .settings-page #setPasswordModal .form-control:focus {
        background: #081a38;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        color: #f8fafc;
        outline: none;
    }
    .settings-page #changePasswordModal .modal-footer,
    .settings-page #setPasswordModal .modal-footer {
        border-top: 1px solid #475569;
        padding: 0.9rem 1.15rem 1rem;
        gap: 0.55rem;
        background: #0b1a36;
    }
    .settings-page #changePasswordModal .modal-footer .btn,
    .settings-page #setPasswordModal .modal-footer .btn {
        padding: 0.54rem 1rem;
        font-size: 0.92rem;
        font-weight: 600;
        border-radius: 10px;
        min-width: 86px;
    }
    .settings-page #changePasswordModal .modal-footer .btn-secondary,
    .settings-page #setPasswordModal .modal-footer .btn-secondary {
        background: #64748b;
        border: 1px solid #64748b;
        color: #f8fafc;
    }
    .settings-page #changePasswordModal .modal-footer .btn-secondary:hover,
    .settings-page #setPasswordModal .modal-footer .btn-secondary:hover {
        background: #5b6c83;
        border-color: #5b6c83;
    }
    .settings-page #changePasswordModal .modal-footer .btn-primary,
    .settings-page #setPasswordModal .modal-footer .btn-primary {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
        box-shadow: 0 10px 24px -14px rgba(37, 99, 235, 0.7);
    }
    .settings-page #changePasswordModal .modal-footer .btn-primary:hover,
    .settings-page #setPasswordModal .modal-footer .btn-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }
    /* Password visibility toggle */
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
    .settings-page #changePasswordModal .password-toggle-btn,
    .settings-page #setPasswordModal .password-toggle-btn {
        color: #93a4bd;
    }
    .settings-page #changePasswordModal .password-toggle-btn:hover,
    .settings-page #setPasswordModal .password-toggle-btn:hover {
        color: #bfdbfe;
        background: rgba(59, 130, 246, 0.14);
    }
    .settings-page #setPasswordModal .password-match-indicator {
        margin-top: 0.38rem;
        font-size: 0.76rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.32rem;
        color: #94a3b8;
        min-height: 1rem;
    }
    .settings-page #setPasswordModal .password-match-indicator.match {
        color: #22c55e;
    }
    .settings-page #setPasswordModal .password-match-indicator.mismatch {
        color: #f87171;
    }
    /* Student table: stable columns; required hours + remove centered (matches admin students) */
    .settings-page .coord-settings-table-wrap {
        border-radius: 12px;
        overflow-x: auto;
    }
    .settings-page .coord-settings-students-table {
        table-layout: fixed;
        width: 100%;
        --bs-table-bg: var(--dtr-card-bg);
        --bs-table-color: var(--dtr-text);
        --bs-table-hover-bg: var(--dtr-hover-bg);
        --bs-table-hover-color: var(--dtr-text);
    }
    .settings-page col.coord-col-check { width: 2.75rem; }
    .settings-page col.coord-col-student-no { width: 7rem; }
    .settings-page col.coord-col-name { width: 20%; }
    .settings-page col.coord-col-course { width: 18%; }
    .settings-page col.coord-col-required { width: 13rem; }
    .settings-page col.coord-col-password { width: 11rem; }
    .settings-page col.coord-col-remove { width: 9.5rem; }
    .settings-page .coord-settings-students-table thead th.coord-col-required,
    .settings-page .coord-settings-students-table tbody td.coord-col-required {
        text-align: center !important;
        vertical-align: middle;
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    .settings-page .coord-settings-students-table thead th.coord-col-remove,
    .settings-page .coord-settings-students-table tbody td.coord-col-remove {
        text-align: center !important;
        vertical-align: middle;
        white-space: nowrap;
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    .settings-page .coord-remove-student-form {
        display: block;
        text-align: center;
        margin: 0;
    }
    /* Destructive remove — bright ghost pill (aligned with admin student list) */
    .settings-page .btn-student-delete {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.38rem;
        padding: 0.4rem 1rem;
        font-size: 0.8125rem;
        font-weight: 600;
        font-family: inherit;
        line-height: 1.25;
        border-radius: 999px;
        border: 1px solid #ff1a1a;
        color: #ff1414;
        background: transparent;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
    }
    .settings-page .btn-student-delete:hover:not(:disabled) {
        background: rgba(255, 24, 24, 0.16);
        border-color: #ff0505;
        color: #ff0000;
    }
    .settings-page .btn-student-delete:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }
    .settings-page .btn-student-delete:focus-visible {
        outline: 2px solid #ff2222;
        outline-offset: 2px;
    }
    html[data-theme="dark"] .settings-page .btn-student-delete {
        color: #ff4d4d;
        border-color: #ff3838;
    }
    html[data-theme="dark"] .settings-page .btn-student-delete:hover:not(:disabled) {
        background: rgba(255, 77, 77, 0.2);
        border-color: #ff6666;
        color: #ff7070;
    }
    html[data-theme="dark"] .settings-page .coord-settings-students-table tbody td {
        background: #0f1a2d !important;
        color: #e5edf7 !important;
    }
    html[data-theme="dark"] .settings-page .coord-settings-students-table tbody tr:hover > td,
    html[data-theme="dark"] .settings-page .coord-settings-students-table tbody tr.is-selected > td {
        background: #1b2538 !important;
        color: #f8fbff !important;
    }
    html[data-theme="dark"] .settings-page .coord-settings-students-table tbody tr:hover > td .text-muted,
    html[data-theme="dark"] .settings-page .coord-settings-students-table tbody tr.is-selected > td .text-muted,
    html[data-theme="dark"] .settings-page .coord-settings-students-table tbody tr:hover > td .small,
    html[data-theme="dark"] .settings-page .coord-settings-students-table tbody tr.is-selected > td .small {
        color: #c9d5e7 !important;
    }
    html[data-theme="dark"] .settings-page .coord-settings-students-table .form-control {
        background: #0b1220 !important;
        color: #f8fbff !important;
        border-color: #475569 !important;
    }
    html[data-theme="dark"] .settings-page .coord-settings-students-table .btn-set-password {
        background: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
        opacity: 1 !important;
    }
    html[data-theme="dark"] .settings-page .coord-settings-students-table .btn-outline-secondary {
        background: #0b1220 !important;
        color: #e5edf7 !important;
        border-color: #64748b !important;
        opacity: 1 !important;
    }
    html[data-theme="dark"] .settings-page .coord-settings-students-table .btn-student-delete {
        color: #ff6b6b !important;
        border-color: #ff5a5a !important;
        background: transparent !important;
        opacity: 1 !important;
    }
    @media (max-width: 767.98px) {
        .settings-page .coord-settings-table-wrap {
            overflow-x: visible;
        }
        .settings-page .coord-settings-students-table {
            table-layout: auto;
        }
        .settings-page .coord-settings-students-table .coord-col-course {
            display: none;
        }
        .settings-page .coord-settings-students-table .coord-col-required {
            min-width: 7.25rem;
        }
        .settings-page .coord-settings-students-table .coord-col-password,
        .settings-page .coord-settings-students-table .coord-col-remove {
            min-width: 7rem;
        }
        .settings-page .coord-settings-students-table .btn-set-password,
        .settings-page .coord-settings-students-table .btn-student-delete {
            font-size: 0.78rem;
            padding: 0.36rem 0.66rem;
        }
        .settings-page .coord-settings-students-table .form-control.form-control-sm {
            width: 4.8rem !important;
        }
    }
</style>
@endpush

@section('content')
<div class="settings-page" data-max-batch="{{ \App\Services\StudentDeletionService::MAX_BATCH }}">
    <h1 class="page-title">Settings</h1>
    <p class="page-sub">Account and student configurations.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4 account-card">
        <div class="card-body">
            <h2 class="section-title"><i class="bi bi-person"></i> Account</h2>
            <ul class="settings-list">
                <li>
                    <div>
                        <span class="setting-label">Password</span>
                        <div class="setting-desc">Update your coordinator login password (min 8 characters)</div>
                    </div>
                    <button type="button" class="btn-account-password" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class="bi bi-key me-1" aria-hidden="true"></i>Change password
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="section-title"><i class="bi bi-gear"></i> Student configurations</h2>
            <p class="text-muted small mb-3">Set a temporary password for students or change their required OJT hours. Changes apply only to verified students in your program.</p>
            <div class="section-tools">
                <div>
                    <form action="{{ route('coordinator.settings') }}" method="GET" class="search-row" role="search">
                        <div class="search-inner">
                            <i class="bi bi-search search-icon" aria-hidden="true"></i>
                            <input type="text"
                                   name="q"
                                   class="search-input form-control"
                                   placeholder="Name, student no, or course…"
                                   value="{{ old('q', $search ?? '') }}"
                                   autocomplete="off"
                                   aria-label="Search students in settings">
                            @if(!empty($search ?? ''))
                            <a href="{{ route('coordinator.settings') }}" class="search-clear" title="Clear search" aria-label="Clear search">
                                <i class="bi bi-x-lg"></i>
                            </a>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-primary btn-search">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </form>
                    <p class="search-hint mb-0">Tip: you can use * or % in the search box for simple wildcards.</p>
                </div>
            </div>
            @if($students->count() > 0)
                <form action="{{ route('coordinator.settings.required-hours.bulk') }}" method="POST" id="bulkRequiredHoursForm">
                    @csrf
                    @if(!empty($search ?? ''))
                    <input type="hidden" name="q" value="{{ $search }}">
                    @endif
                    <div class="bulk-card">
                        <div class="bulk-field">
                            <label>Selected students</label>
                            <div class="bulk-count"><span id="selectedCount">0</span> selected</div>
                        </div>
                        <div class="bulk-field">
                            <label for="bulkRequiredHours">Required hours</label>
                            <input type="number" id="bulkRequiredHours" name="required_ojt_hours" value="{{ old('required_ojt_hours', 120) }}" min="1" max="9999" step="0.5" class="form-control" style="width: 8rem;">
                        </div>
                        <div class="bulk-actions">
                            <button type="submit" class="btn btn-primary" id="applyBulkButton">
                                <i class="bi bi-lightning-charge me-1"></i> Apply to selected
                            </button>
                            <button type="button" class="btn-student-delete js-coordinator-batch-delete" id="coordinatorBulkDeleteBtn" disabled aria-disabled="true">
                                <i class="bi bi-trash"></i> Delete selected
                            </button>
                        </div>
                    </div>
                </form>
                <form id="coordinatorDeleteBatchForm" method="POST" action="{{ route('coordinator.settings.students.delete-batch') }}" class="d-none" aria-hidden="true">
                    @csrf
                    @if(!empty($search ?? ''))
                        <input type="hidden" name="q" value="{{ $search }}">
                    @endif
                    <div id="coordinatorDeleteBatchIds"></div>
                </form>
                <p class="table-note">Use the checkboxes to batch update required hours or batch archive. Per-student actions use the row buttons. Archives are soft-deletes and are recorded in the audit log; an admin can restore students from Archived students.</p>
                <div class="table-responsive coord-settings-table-wrap">
                    <table class="table align-middle mb-0 coord-settings-students-table">
                        <colgroup>
                            <col class="coord-col-check">
                            <col class="coord-col-student-no">
                            <col class="coord-col-name">
                            <col class="coord-col-course">
                            <col class="coord-col-required">
                            <col class="coord-col-password">
                            <col class="coord-col-remove">
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="selection-col text-center">
                                    <input type="checkbox" id="selectAllStudents" class="select-checkbox" aria-label="Select all shown students">
                                </th>
                                <th>Student No</th>
                                <th>Name</th>
                                <th class="coord-col-course">Course</th>
                                <th scope="col" class="coord-col-required text-center text-nowrap">Required hours</th>
                                <th>Password</th>
                                <th scope="col" class="coord-col-remove text-center text-nowrap">Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                                @php
                                    $activeAssignment = $student->activeTermAssignment;
                                    $required = (float) $student->requiredHoursForAssignment($activeAssignment);
                                @endphp
                                @php($settingsRowSearch = \Illuminate\Support\Str::lower(implode(' ', [
                                    $student->student_no,
                                    $student->name,
                                    $student->course,
                                ])))
                                <tr data-student-row data-live-row data-live-search="{{ $settingsRowSearch }}" data-live-name="{{ \Illuminate\Support\Str::lower($student->name) }}">
                                    <td class="text-center">
                                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" form="bulkRequiredHoursForm" class="select-checkbox student-select" aria-label="Select {{ $student->name }}">
                                    </td>
                                    <td><span class="fw-medium">{{ $student->student_no }}</span></td>
                                    <td>{{ $student->name }}</td>
                                    <td class="coord-col-course"><span class="text-muted">{{ $student->course }}</span></td>
                                    <td class="coord-col-required text-center">
                                        <form action="{{ route('coordinator.ojt.completion.required-hours', $student) }}" method="POST" class="coord-required-hours-form d-inline-flex align-items-center justify-content-center gap-1 flex-wrap">
                                            @csrf
                                            <input type="number" name="required_ojt_hours" value="{{ $required }}" min="1" max="9999" step="0.5" class="form-control form-control-sm" style="width: 5.5rem;">
                                            <span class="text-muted small">hrs</span>
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Save required hours"><i class="bi bi-check"></i></button>
                                        </form>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-set-password" title="Set temporary password for this student" data-bs-toggle="modal" data-bs-target="#setPasswordModal" data-student-name="{{ e($student->name) }}" data-set-password-url="{{ route('coordinator.student.set-password', $student) }}">
                                            <i class="bi bi-key"></i> Set password
                                        </button>
                                    </td>
                                    <td class="coord-col-remove text-center">
                                        <form method="POST" action="{{ route('coordinator.settings.students.destroy', $student) }}" class="coord-remove-student-form m-0" data-norsu-confirm="Archive student {{ e($student->student_no) }}? They will be hidden from your lists; an admin can restore them from Archived students." data-norsu-variant="danger">
                                            @csrf
                                            @method('DELETE')
                                            @if(!empty($search ?? ''))
                                                <input type="hidden" name="q" value="{{ $search }}">
                                            @endif
                                            <button type="submit" class="btn-student-delete" title="Archive this student (soft delete)">
                                                <i class="bi bi-trash" aria-hidden="true"></i> Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    @if(!empty($search))
                        <i class="bi bi-search"></i>
                        <p class="mb-0 fw-medium">No students match "{{ e($search) }}"</p>
                        <p class="small mt-1 mb-0">Try a different name, student number, or course, or <a href="{{ route('coordinator.settings') }}">clear the search</a>.</p>
                    @else
                        <i class="bi bi-people"></i>
                        <p class="mb-0 fw-medium">No students in your program</p>
                        <p class="small mt-1 mb-0">Verify students under Pending Verification first. They will appear here for you to set passwords and required hours.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('coordinator.settings.password') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="changePasswordModalLabel">Change password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="coord_current_password" class="form-label">Current password</label>
                            <div class="password-toggle-wrap">
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="coord_current_password" name="current_password" required autocomplete="current-password" placeholder="Your current password">
                                <button type="button" class="password-toggle-btn" data-password-toggle aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="coord_password" class="form-label">New password</label>
                            <div class="password-toggle-wrap">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="coord_password" name="password" required minlength="8" autocomplete="new-password" placeholder="Min 8 characters">
                                <button type="button" class="password-toggle-btn" data-password-toggle aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label for="coord_password_confirmation" class="form-label">Confirm new password</label>
                            <div class="password-toggle-wrap">
                                <input type="password" class="form-control" id="coord_password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Same as above">
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

    <div class="modal fade" id="setPasswordModal" tabindex="-1" aria-labelledby="setPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="setPasswordForm" method="POST" action="">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="setPasswordModalLabel"><i class="bi bi-key me-2"></i>Set student password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3" id="setPasswordStudentName">Set a new temporary password for the student. They can log in with it immediately.</p>
                        <div class="mb-3">
                            <label for="setPasswordNew" class="form-label">New password</label>
                            <div class="password-toggle-wrap">
                                <input type="password" class="form-control" id="setPasswordNew" name="password" required minlength="8" autocomplete="new-password" placeholder="Min 8 characters">
                                <button type="button" class="password-toggle-btn" data-password-toggle aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label for="setPasswordConfirm" class="form-label">Confirm password</label>
                            <div class="password-toggle-wrap">
                                <input type="password" class="form-control" id="setPasswordConfirm" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Same as above">
                                <button type="button" class="password-toggle-btn" data-password-toggle aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
                            </div>
                            <div id="setPasswordMatchIndicator" class="password-match-indicator" aria-live="polite"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check me-1"></i>Update password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    var setPasswordNewInput = document.getElementById('setPasswordNew');
    var setPasswordConfirmInput = document.getElementById('setPasswordConfirm');
    var setPasswordMatchIndicator = document.getElementById('setPasswordMatchIndicator');

    function updateSetPasswordMatchIndicator() {
        if (!setPasswordNewInput || !setPasswordConfirmInput || !setPasswordMatchIndicator) return;
        var password = setPasswordNewInput.value || '';
        var confirmation = setPasswordConfirmInput.value || '';
        setPasswordMatchIndicator.classList.remove('match', 'mismatch');

        if (confirmation.length === 0) {
            setPasswordMatchIndicator.innerHTML = '';
            return;
        }

        if (password === confirmation) {
            setPasswordMatchIndicator.classList.add('match');
            setPasswordMatchIndicator.innerHTML = '<i class="bi bi-check-circle-fill"></i><span>Passwords match</span>';
            return;
        }

        setPasswordMatchIndicator.classList.add('mismatch');
        setPasswordMatchIndicator.innerHTML = '<i class="bi bi-x-circle-fill"></i><span>Passwords do not match yet</span>';
    }

    document.getElementById('setPasswordModal').addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        if (btn && btn.dataset.setPasswordUrl) {
            document.getElementById('setPasswordForm').action = btn.dataset.setPasswordUrl;
            var nameEl = document.getElementById('setPasswordStudentName');
            if (nameEl && btn.dataset.studentName) {
                nameEl.textContent = 'Set a new temporary password for ' + btn.dataset.studentName + '. They can log in with it immediately.';
            }
            document.getElementById('setPasswordNew').value = '';
            document.getElementById('setPasswordConfirm').value = '';
            updateSetPasswordMatchIndicator();
        }
    });

    if (setPasswordNewInput && setPasswordConfirmInput) {
        setPasswordNewInput.addEventListener('input', updateSetPasswordMatchIndicator);
        setPasswordConfirmInput.addEventListener('input', updateSetPasswordMatchIndicator);
        updateSetPasswordMatchIndicator();
    }

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

    var bulkForm = document.getElementById('bulkRequiredHoursForm');
    var selectAll = document.getElementById('selectAllStudents');
    var selectedCount = document.getElementById('selectedCount');
    var studentChecks = Array.prototype.slice.call(document.querySelectorAll('.student-select'));
    var applyBulkButton = document.getElementById('applyBulkButton');
    var bulkHoursInput = document.getElementById('bulkRequiredHours');
    var searchInput = document.querySelector('.settings-page .search-input');
    var liveRows = Array.prototype.slice.call(document.querySelectorAll('[data-live-row]'));
    var settingsRoot = document.querySelector('.settings-page');
    var maxBatch = settingsRoot ? parseInt(settingsRoot.getAttribute('data-max-batch') || '40', 10) : 40;
    if (!maxBatch || maxBatch < 1) {
        maxBatch = 40;
    }
    var bulkDeleteBtns = Array.prototype.slice.call(document.querySelectorAll('.js-coordinator-batch-delete'));
    var deleteBatchForm = document.getElementById('coordinatorDeleteBatchForm');
    var deleteBatchIds = document.getElementById('coordinatorDeleteBatchIds');

    function updateSelectionState() {
        var checked = studentChecks.filter(function (box) { return box.checked; });
        if (selectedCount) {
            selectedCount.textContent = checked.length;
        }
        studentChecks.forEach(function (box) {
            var row = box.closest('[data-student-row]');
            if (row) {
                row.classList.toggle('is-selected', box.checked);
            }
        });
        if (selectAll) {
            selectAll.checked = studentChecks.length > 0 && checked.length === studentChecks.length;
            selectAll.indeterminate = checked.length > 0 && checked.length < studentChecks.length;
        }
        if (applyBulkButton) {
            applyBulkButton.disabled = checked.length === 0;
        }
        bulkDeleteBtns.forEach(function (b) {
            b.disabled = checked.length === 0;
            b.setAttribute('aria-disabled', checked.length === 0 ? 'true' : 'false');
        });
    }

    async function submitCoordinatorBatchDelete() {
        var checked = studentChecks.filter(function (box) { return box.checked; });
        if (checked.length === 0) {
            await window.norsuPrompt.alert('Select at least one student first.', { variant: 'warning', title: 'Nothing selected' });
            return;
        }
        if (checked.length > maxBatch) {
            await window.norsuPrompt.alert('You can remove at most ' + maxBatch + ' students per request.', { variant: 'warning', title: 'Too many selected' });
            return;
        }
        var deleteOk = await window.norsuPrompt.confirm(
            'Archive ' + checked.length + ' selected student' + (checked.length === 1 ? '' : 's') + '? They will be hidden until an admin restores them from Archived students.',
            { variant: 'danger', title: 'Archive students', confirmText: 'Yes, archive' }
        );
        if (!deleteOk) {
            return;
        }
        if (!deleteBatchForm || !deleteBatchIds) {
            return;
        }
        deleteBatchIds.innerHTML = '';
        checked.forEach(function (box) {
            var inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'student_ids[]';
            inp.value = box.value;
            deleteBatchIds.appendChild(inp);
        });
        deleteBatchForm.submit();
    }

    bulkDeleteBtns.forEach(function (btn) {
        btn.addEventListener('click', submitCoordinatorBatchDelete);
    });

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            studentChecks.forEach(function (box) {
                box.checked = selectAll.checked;
            });
            updateSelectionState();
        });
    }

    studentChecks.forEach(function (box) {
        box.addEventListener('change', updateSelectionState);
    });

    if (bulkForm) {
        bulkForm.addEventListener('submit', async function (e) {
            if (bulkForm.dataset.promptBypass === '1') {
                bulkForm.dataset.promptBypass = '';
                return;
            }
            e.preventDefault();
            var selected = studentChecks.filter(function (box) { return box.checked; }).length;
            if (selected === 0) {
                await window.norsuPrompt.alert('Select at least one student first.', { variant: 'warning', title: 'Nothing selected' });
                return;
            }
            if (!bulkHoursInput || !bulkHoursInput.value) {
                await window.norsuPrompt.alert('Enter the required hours to apply.', { variant: 'warning', title: 'Missing hours' });
                return;
            }
            var applyOk = await window.norsuPrompt.confirm(
                'Apply ' + bulkHoursInput.value + ' required hours to ' + selected + ' selected student' + (selected === 1 ? '' : 's') + '?',
                { variant: 'warning', title: 'Apply required hours', confirmText: 'Yes, apply' }
            );
            if (!applyOk) {
                return;
            }
            bulkForm.dataset.promptBypass = '1';
            if (typeof bulkForm.requestSubmit === 'function') bulkForm.requestSubmit(); else bulkForm.submit();
        });
    }

    function normalizePattern(s) {
        return (s || '').trim().replace(/%/g, '*');
    }
    function usesWildcardTokens(raw) {
        return /[*?%_]/.test((raw || '').trim());
    }
    function matchesWildcard(text, raw) {
        var p = normalizePattern(raw);
        if (!p) return true;
        var lower = (text || '').toLowerCase();
        var pattern = p.toLowerCase();
        if (pattern.indexOf('*') === -1) {
            return lower.indexOf(pattern) !== -1;
        }
        var parts = pattern.split('*');
        var pos = 0;
        for (var i = 0; i < parts.length; i++) {
            var seg = parts[i];
            if (!seg) continue;
            var j = lower.indexOf(seg, pos);
            if (j === -1) return false;
            pos = j + seg.length;
        }
        return true;
    }
    function levenshtein(a, b) {
        if (a === b) return 0;
        if (!a.length) return b.length;
        if (!b.length) return a.length;
        var v0 = new Array(b.length + 1);
        var v1 = new Array(b.length + 1);
        var i, j;
        for (j = 0; j <= b.length; j++) v0[j] = j;
        for (i = 0; i < a.length; i++) {
            v1[0] = i + 1;
            for (j = 0; j < b.length; j++) {
                var cost = a[i] === b[j] ? 0 : 1;
                v1[j + 1] = Math.min(v1[j] + 1, v0[j + 1] + 1, v0[j] + cost);
            }
            var t = v0; v0 = v1; v1 = t;
        }
        return v0[b.length];
    }
    function fuzzyNameMatch(name, query) {
        query = (query || '').trim().toLowerCase();
        if (!query) return false;
        var n = (name || '').trim().toLowerCase();
        if (!n) return false;
        if (n.indexOf(query) !== -1 || n.replace(/\s+/g, '').indexOf(query.replace(/\s+/g, '')) !== -1) return true;
        var words = n.split(/\s+/);
        for (var i = 0; i < words.length; i++) {
            var w = words[i];
            if (!w) continue;
            if (w.indexOf(query) !== -1 || query.indexOf(w) !== -1) return true;
            var max = Math.max(w.length, query.length, 1);
            if (max <= 255 && /^[\x00-\x7F]*$/.test(w) && /^[\x00-\x7F]*$/.test(query)) {
                if (levenshtein(w, query) <= Math.max(1, Math.floor(max * 0.28))) return true;
            }
        }
        return false;
    }
    function rowMatches(row, q) {
        var hay = row.getAttribute('data-live-search') || '';
        if (matchesWildcard(hay, q)) return true;
        if (usesWildcardTokens(q)) return false;
        return fuzzyNameMatch(row.getAttribute('data-live-name') || '', q);
    }
    function applyLiveFilter() {
        if (!searchInput || !liveRows.length) return;
        var q = searchInput.value || '';
        liveRows.forEach(function (row) {
            row.style.display = rowMatches(row, q) ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyLiveFilter);
        searchInput.form?.addEventListener('submit', function (e) { e.preventDefault(); });
    }

    updateSelectionState();
    applyLiveFilter();
})();
</script>
@endpush
