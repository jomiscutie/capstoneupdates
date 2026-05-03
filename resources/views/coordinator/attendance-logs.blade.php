@extends('layouts.coordinator')

@section('title', 'Attendance Logs')

@push('styles')
<style>
    .dtr-attendance {
        --attendance-text: var(--dtr-text);
        --attendance-muted: var(--dtr-muted);
        --attendance-border: var(--dtr-border-soft);
        --attendance-surface: var(--dtr-card-bg);
        --attendance-heading: var(--dtr-text);
        --attendance-surface-soft: var(--dtr-surface-soft);
        --attendance-hover: var(--dtr-hover-bg);
    }
    .dtr-attendance .back-link {
        display: inline-flex; align-items: center; gap: 0.35rem;
        font-size: 0.875rem; color: var(--attendance-muted); text-decoration: none;
        margin-bottom: 0.75rem; font-weight: 500; letter-spacing: 0.01em;
        transition: color 0.2s ease;
    }
    .dtr-attendance .back-link:hover { color: var(--dtr-primary); }
    .dtr-attendance .page-title {
        font-size: 1.5rem; font-weight: 600; color: var(--attendance-heading);
        margin-bottom: 0.25rem; letter-spacing: -0.02em; line-height: 1.3; text-align: center;
    }
    .dtr-attendance .page-sub {
        font-size: 0.875rem; color: var(--attendance-muted);
        margin: 0 auto 1.5rem; letter-spacing: 0.01em; text-align: center; max-width: 760px;
    }
    .dtr-attendance .stats-box {
        background: var(--attendance-surface);
        border: 1px solid var(--attendance-border);
        border-radius: 12px;
        box-shadow: var(--dtr-shadow-soft);
        padding: 1.25rem 1rem;
        text-align: center;
        margin-bottom: 1rem;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .dtr-attendance .stats-box:hover { box-shadow: var(--dtr-shadow-soft); }
    .dtr-attendance .stats-box h4 {
        font-size: 0.6875rem; font-weight: 600; color: var(--attendance-muted);
        text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;
    }
    .dtr-attendance .stats-box .fs-3 { font-variant-numeric: tabular-nums; color: var(--attendance-text); font-weight: 600; }
    .dtr-attendance .stats-box.stats-warning h4,
    .dtr-attendance .stats-box.stats-warning .fs-3 { color: #b45309; }
    .dtr-attendance .card {
        border-radius: 12px;
        padding: 1.5rem 1.75rem;
        border: 1px solid var(--attendance-border);
        box-shadow: var(--dtr-shadow-soft);
        background: var(--attendance-surface);
    }
    .dtr-attendance .card h4 {
        font-size: 1rem; font-weight: 600; color: var(--attendance-heading);
        margin-bottom: 1rem; letter-spacing: -0.01em;
    }
    .dtr-attendance .table-responsive {
        border-radius: 10px;
        overflow-x: auto;
        border: 1px solid var(--attendance-border);
        background: var(--attendance-surface);
        -webkit-overflow-scrolling: touch;
    }
    /* lg+: table stays within card width — no sideways scroll inside the logs card */
    .dtr-attendance .coord-logs-table-wrap {
        border-radius: 10px;
        overflow-x: visible;
        border: 1px solid var(--attendance-border);
        background: var(--attendance-surface);
    }
    .dtr-attendance .coord-attendance-log-cards {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .dtr-attendance .coord-attendance-log-card {
        border: 1px solid var(--attendance-border);
        border-radius: 10px;
        padding: 0.85rem 1rem;
        background: var(--attendance-surface);
        box-shadow: var(--dtr-shadow-soft);
    }
    .dtr-attendance .coord-attendance-log-card .coord-log-card-title {
        font-weight: 600;
        font-size: 0.9375rem;
        color: var(--attendance-heading);
        margin-bottom: 0.65rem;
        letter-spacing: -0.02em;
    }
    .dtr-attendance .coord-attendance-log-card .coord-log-card-metrics {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.55rem 0.85rem;
        font-size: 0.8125rem;
        color: var(--attendance-text);
    }
    .dtr-attendance .coord-attendance-log-card .coord-log-slot-span2 {
        grid-column: 1 / -1;
    }
    .dtr-attendance .coord-attendance-log-card .coord-log-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--attendance-muted);
        margin-bottom: 0.2rem;
    }
    .dtr-attendance .coord-attendance-log-card .coord-log-snaps-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        align-items: center;
        justify-content: flex-start;
        margin-top: 0.5rem;
    }
    .dtr-attendance .coord-attendance-log-card .coord-log-actions-row {
        margin-top: 0.65rem;
        padding-top: 0.65rem;
        border-top: 1px solid var(--attendance-border);
        display: flex;
        justify-content: center;
    }
    /* Single-student log table: compact like student Recent logs (fit width without horizontal scroll) */
    .dtr-attendance .table-coordinator-logs {
        width: 100%;
        min-width: 0;
        max-width: 100%;
        margin-bottom: 0;
        table-layout: fixed;
    }
    .dtr-attendance .table-coordinator-logs thead th,
    .dtr-attendance .table-coordinator-logs tbody td {
        padding: 0.45rem 0.3rem;
        font-size: 0.8125rem;
        vertical-align: middle;
    }
    .dtr-attendance .table-coordinator-logs thead th {
        font-size: 0.625rem;
        line-height: 1.2;
        letter-spacing: 0.06em;
        hyphens: auto;
        text-align: center;
    }
    .dtr-attendance .table-coordinator-logs thead th:first-child { text-align: left; }
    .dtr-attendance .table-coordinator-logs .dtr-time {
        text-align: center;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    .dtr-attendance .table-coordinator-logs .hours-cell,
    .dtr-attendance .table-coordinator-logs .status-cell,
    .dtr-attendance .table-coordinator-logs .snapshots-cell { text-align: center; }
    .dtr-attendance .table-coordinator-logs .snapshots-cell .d-flex { justify-content: center; }
    .dtr-attendance .table-coordinator-logs .actions-cell {
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }
    /* Keep invalidate action on one line; column must be wide enough for fixed-layout table */
    .dtr-attendance .table-coordinator-logs .actions-cell .btn-invalidate-open {
        padding: 0.48rem 0.52rem;
        font-size: 0.8125rem;
        white-space: nowrap;
        max-width: none;
        justify-content: center;
        align-items: center;
        gap: 0;
        flex-wrap: nowrap;
        min-width: 2.25rem;
    }
    .dtr-attendance .table-coordinator-logs .actions-cell .btn-invalidate-open .bi {
        flex-shrink: 0;
        line-height: 1;
        font-size: 1.1rem;
    }
    /*
     * Column widths (% only — no min-width on <col>; min-width fights “no sideways scroll”).
     */
    .dtr-attendance .table-coordinator-logs col.col-w-date { width: 9%; }
    .dtr-attendance .table-coordinator-logs col.col-w-time { width: 10%; }
    .dtr-attendance .table-coordinator-logs col.col-w-hours { width: 11%; }
    .dtr-attendance .table-coordinator-logs col.col-w-snaps { width: auto; }
    .dtr-attendance .table-coordinator-logs col.col-w-act { width: 9%; }
    .dtr-attendance .table-coordinator-logs tbody td { min-width: 0; word-wrap: break-word; }
    .dtr-attendance .table-coordinator-logs .snapshots-cell .btn-snapshot {
        padding: 0.32rem 0.5rem;
        font-size: 0.75rem;
        gap: 0.25rem;
    }
    .dtr-attendance .table-coordinator-logs .snapshots-cell .btn-snapshot i {
        font-size: 0.8rem;
    }
    .dtr-attendance .table thead th {
        background: var(--attendance-surface-soft);
        color: var(--dtr-heading);
        font-weight: 600;
        font-size: 0.6875rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 0.875rem 1.125rem;
        border-bottom: 1px solid var(--attendance-border);
    }
    .dtr-attendance .table tbody td {
        padding: 0.875rem 1.125rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--attendance-border);
        font-size: 0.875rem;
        color: var(--attendance-text);
    }
    .dtr-attendance .table tbody tr:last-child td { border-bottom: none; }
    .dtr-attendance .table tbody tr:hover { background: var(--attendance-hover); }
    .dtr-attendance .alert { border-radius: 10px; border: 1px solid var(--attendance-border); }
    .dtr-attendance .alert-info {
        background: var(--attendance-surface-soft);
        color: var(--attendance-text);
        border-color: var(--attendance-border);
    }
    .dtr-attendance .alert-info i {
        color: var(--dtr-primary);
    }
    .dtr-attendance .search-wrap { margin-bottom: 1rem; }
    /* One toolbar row: period controls + search + submit (wraps only on very narrow screens) */
    .dtr-attendance .search-row.coord-logs-toolbar {
        display: flex;
        flex-flow: row wrap;
        align-items: center;
        gap: 0.65rem 0.75rem;
        width: 100%;
        max-width: 100%;
    }
    .dtr-attendance .coord-toolbar-period {
        display: flex;
        flex-flow: row wrap;
        align-items: center;
        gap: 0.5rem 0.65rem;
        flex-shrink: 0;
    }
    .dtr-attendance .coord-toolbar-period .filter-tabs {
        flex-shrink: 0;
    }
    .dtr-attendance .coord-toolbar-input {
        min-height: 40px;
        height: 40px;
        box-sizing: border-box;
        padding: 0.35rem 0.65rem;
        font-size: 0.875rem;
        border: 1px solid var(--dtr-input-border);
        border-radius: 10px;
        background: var(--dtr-input-bg);
        color: var(--attendance-text);
    }
    .dtr-attendance .coord-toolbar-input:focus {
        border-color: var(--dtr-primary);
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--dtr-primary) 22%, transparent);
        outline: none;
        background: var(--dtr-input-bg);
    }
    html[data-theme="dark"] .dtr-attendance .coord-toolbar-input:focus {
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--dtr-primary) 28%, transparent);
    }
    .dtr-attendance .search-inner.coord-toolbar-search-inner {
        position: relative;
        flex: 1 1 200px;
        min-width: min(100%, 160px);
        min-height: 40px;
    }
    .dtr-attendance .coord-logs-toolbar .search-input {
        width: 100%;
        min-height: 40px;
        height: 40px;
        box-sizing: border-box;
        padding: 0.4rem 2.25rem 0.4rem 2.5rem;
        font-size: 0.875rem;
        border: 1px solid var(--dtr-input-border);
        border-radius: 10px;
        background: var(--dtr-input-bg);
        color: var(--attendance-text);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .dtr-attendance .coord-logs-toolbar .search-input::placeholder { color: var(--attendance-muted); }
    .dtr-attendance .coord-logs-toolbar .search-input:focus {
        outline: none;
        border-color: var(--dtr-primary);
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--dtr-primary) 22%, transparent);
        background: var(--dtr-input-bg);
    }
    .dtr-attendance .search-icon {
        position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%);
        color: var(--attendance-muted); font-size: 0.9rem; pointer-events: none;
    }
    .dtr-attendance .search-clear {
        position: absolute; right: 0.2rem; top: 50%; transform: translateY(-50%);
        width: 24px; height: 24px; border: none; border-radius: 6px;
        background: transparent; color: var(--attendance-muted);
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; transition: color 0.2s; text-decoration: none; font-size: 0.8125rem;
    }
    .dtr-attendance .search-clear:hover { background: transparent; color: var(--attendance-text); }
    .dtr-attendance .search-hint { font-size: 0.8125rem; color: var(--attendance-muted); margin-top: 0.5rem; }
    .dtr-attendance .filter-tabs {
        position: relative;
        display: inline-flex;
        align-items: stretch;
        gap: 0;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid var(--attendance-border);
        background: var(--attendance-surface-soft);
        min-height: 40px;
    }
    .dtr-attendance .filter-tabs label {
        margin: 0;
        cursor: pointer;
        padding: 0 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        min-height: 40px;
        color: var(--attendance-muted);
        transition: color 0.2s, background 0.2s;
        letter-spacing: 0.02em;
    }
    .dtr-attendance .filter-tabs input { position: absolute; opacity: 0; }
    .dtr-attendance .filter-tabs input:checked + label {
        background: var(--attendance-surface); color: var(--dtr-primary);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
    }
    .dtr-attendance .filter-panel { display: none; }
    .dtr-attendance .coord-toolbar-period .filter-panel.active {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .dtr-attendance .card-body form.row { align-items: flex-end; }
    .dtr-attendance .card-body form.row.g-3 > .col-auto > .btn-primary.btn-sm { box-sizing: border-box; min-height: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; padding: 0 0.75rem; font-weight: 600; font-size: 0.8125rem; border-radius: 10px; letter-spacing: 0.01em; }
    .dtr-attendance .btn-search.coord-toolbar-submit {
        letter-spacing: 0.01em;
        min-height: 40px;
        height: 40px;
        padding: 0 1.1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        flex-shrink: 0;
    }
    .dtr-attendance .view-student-bar {
        display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem;
        padding: 0.75rem 1.25rem; margin-bottom: 1.25rem;
        background: var(--attendance-surface-soft); border: 1px solid var(--attendance-border);
        border-radius: 10px; font-size: 0.875rem;
        letter-spacing: 0.01em;
    }
    .dtr-attendance .view-student-bar .view-student-label { color: var(--attendance-text); font-weight: 500; }
    .dtr-attendance .view-student-bar .view-student-name { color: var(--dtr-primary); font-weight: 600; }
    .dtr-attendance .view-student-bar .btn-show-all {
        padding: 0.375rem 0.75rem; font-size: 0.8125rem; font-weight: 600;
        border-radius: 8px; letter-spacing: 0.02em;
        flex-shrink: 0; display: inline-flex; align-items: center;
    }
    .dtr-attendance .btn-view-log {
        padding: 0.375rem 0.75rem; font-size: 0.8125rem; font-weight: 500;
        border-radius: 8px; white-space: nowrap; letter-spacing: 0.02em;
    }
    .dtr-attendance .student-list { list-style: none; padding: 0; margin: 0; }
    .dtr-attendance .student-list li {
        display: flex; align-items: center; flex-wrap: nowrap; gap: 1rem;
        padding: 0.875rem 0; border-bottom: 1px solid var(--attendance-border);
        transition: background 0.2s ease;
    }
    .dtr-attendance .student-list li:last-child { border-bottom: none; }
    .dtr-attendance .student-list li:hover { background: var(--attendance-hover); padding-left: 0.5rem; padding-right: 0.5rem; margin: 0 -0.5rem; border-radius: 8px; }
    .dtr-attendance .student-list .student-name {
        font-weight: 600; color: var(--attendance-text); width: 200px; min-width: 200px; flex-shrink: 0;
        font-size: 0.9375rem; letter-spacing: 0.01em;
    }
    .dtr-attendance .student-list .student-no {
        color: var(--attendance-muted); font-size: 0.875rem;
        font-variant-numeric: tabular-nums; flex: 1; min-width: 0;
    }
    .dtr-attendance .student-list .btn-view-attendance {
        padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600;
        border-radius: 10px; letter-spacing: 0.02em;
        flex-shrink: 0; margin-left: auto; display: inline-flex; align-items: center; justify-content: center;
    }
    .dtr-attendance .text-center.py-4.text-muted .fs-2 { color: var(--attendance-muted); opacity: 0.45; }
    .dtr-attendance .text-muted.mb-0 { font-size: 0.9375rem; color: var(--attendance-muted); }
    /* Invalidation modal + trigger — theme tokens (light / dark) */
    .invalidate-attendance-modal .modal-content {
        border-radius: 14px;
        border: 1px solid var(--dtr-border-soft);
        background: var(--dtr-card-bg);
        color: var(--dtr-text);
        box-shadow: var(--dtr-shadow-strong, 0 18px 50px rgba(15, 23, 42, 0.2));
        overflow: hidden;
    }
    .invalidate-attendance-modal .modal-header {
        border-bottom: 1px solid var(--dtr-border-soft);
        padding: 1rem 1.15rem;
        gap: 0.75rem;
        align-items: flex-start;
    }
    .invalidate-attendance-modal .invalidate-modal-head {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        flex: 1;
        min-width: 0;
    }
    .invalidate-attendance-modal .invalidate-modal-icon {
        flex: 0 0 auto;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        color: #dc2626;
        background: transparent;
        border: 1.5px solid color-mix(in srgb, #dc2626 42%, var(--dtr-border-soft));
    }
    html[data-theme="dark"] .invalidate-attendance-modal .invalidate-modal-icon {
        color: #fecaca;
        background: transparent;
        border-color: color-mix(in srgb, #f87171 45%, var(--dtr-border-soft));
    }
    .invalidate-attendance-modal .invalidate-modal-head .modal-title {
        color: var(--dtr-heading);
        font-weight: 700;
        font-size: 1.0625rem;
        letter-spacing: -0.02em;
        line-height: 1.3;
    }
    .invalidate-attendance-modal .invalidate-modal-sub {
        color: var(--dtr-muted) !important;
        font-size: 0.835rem !important;
        line-height: 1.45;
        margin-top: 0.2rem !important;
    }
    .invalidate-attendance-modal .modal-body .form-label {
        color: var(--dtr-heading);
        font-weight: 600;
        font-size: 0.78rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        margin-bottom: 0.28rem;
    }
    /* Typing shell: soft capsule around the textarea (no harsh box-in-box) */
    .invalidate-attendance-modal .invalidate-reason-bubble {
        padding: 0.72rem 0.82rem;
        border-radius: 12px;
        background: color-mix(in srgb, var(--dtr-surface-soft) 94%, transparent);
        border: 1px solid var(--dtr-border-soft);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .invalidate-attendance-modal .invalidate-reason-bubble:focus-within {
        border-color: color-mix(in srgb, var(--dtr-primary) 42%, var(--dtr-input-border));
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--dtr-primary) 16%, transparent);
    }
    .invalidate-attendance-modal .invalidate-reason-textarea.form-control {
        background: transparent !important;
        border: none !important;
        border-radius: 0;
        box-shadow: none !important;
        padding: 0.2rem 0 !important;
        min-height: 7.75rem;
        font-size: 0.9075rem;
        line-height: 1.5;
        color: var(--dtr-text);
        resize: vertical;
    }
    .invalidate-attendance-modal .invalidate-reason-textarea.form-control::placeholder {
        color: var(--dtr-muted);
        opacity: 0.88;
    }
    .invalidate-attendance-modal .invalidate-reason-textarea.form-control:focus {
        outline: none;
        box-shadow: none !important;
    }
    .invalidate-attendance-modal .modal-footer {
        border-top: 1px solid var(--dtr-border-soft);
        padding: 0.95rem 1.15rem 1.05rem;
        gap: 0.65rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .dtr-attendance .btn-invalidate-open {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        font-weight: 600;
        font-size: 0.8125rem;
        padding: 0.5rem 0.62rem;
        min-height: 2.25rem;
        min-width: 2.25rem;
        border-radius: 10px;
        border: 1px solid color-mix(in srgb, #dc2626 42%, var(--dtr-border-soft));
        background: color-mix(in srgb, #dc2626 10%, var(--dtr-card-bg));
        color: #b91c1c !important;
        white-space: nowrap;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }
    .dtr-attendance .btn-invalidate-open:hover {
        background: color-mix(in srgb, #dc2626 18%, var(--dtr-card-bg));
        border-color: color-mix(in srgb, #dc2626 55%, var(--dtr-border-soft));
        color: #991b1b !important;
    }
    html[data-theme="dark"] .dtr-attendance .btn-invalidate-open {
        color: #fecaca !important;
        border-color: rgba(248, 113, 113, 0.45);
        background: rgba(248, 113, 113, 0.1);
    }
    html[data-theme="dark"] .dtr-attendance .btn-invalidate-open:hover {
        background: rgba(248, 113, 113, 0.16);
        border-color: rgba(252, 165, 165, 0.55);
        color: #fecaca !important;
    }
    html[data-theme="dark"] .invalidate-attendance-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    html[data-theme="dark"] .dtr-attendance .stats-box {
        background: var(--dtr-card-bg);
        border-color: var(--dtr-border-soft);
    }
    html[data-theme="dark"] .dtr-attendance .card {
        background: var(--dtr-card-bg);
        border-color: var(--dtr-border-soft);
    }
    html[data-theme="dark"] .dtr-attendance .table thead th {
        background: var(--dtr-surface-soft);
        color: var(--dtr-heading);
    }
    html[data-theme="dark"] .dtr-attendance .table tbody tr:hover {
        background: var(--dtr-hover-bg);
    }

    /* Verification snapshot buttons — clean, modern, theme-aware */
    .dtr-attendance .btn-snapshot {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.75rem;
        border-radius: 8px;
        font-size: 0.8125rem;
        font-weight: 600;
        letter-spacing: 0.01em;
        text-decoration: none;
        white-space: nowrap;
        border: 1px solid color-mix(in srgb, var(--dtr-primary) 35%, var(--dtr-border-soft) 65%);
        color: color-mix(in srgb, var(--dtr-primary) 85%, var(--dtr-text) 15%);
        background:
            linear-gradient(180deg,
                color-mix(in srgb, var(--dtr-primary) 12%, var(--dtr-card-bg) 88%) 0%,
                color-mix(in srgb, var(--dtr-primary) 8%, var(--dtr-card-bg) 92%) 100%);
        box-shadow:
            0 1px 2px color-mix(in srgb, #0f172a 6%, transparent),
            0 0 0 1px color-mix(in srgb, #fff 50%, transparent) inset;
        transition: all 0.15s ease;
    }
    .dtr-attendance .btn-snapshot i {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    .dtr-attendance .btn-snapshot:hover {
        color: color-mix(in srgb, var(--dtr-primary) 95%, var(--dtr-heading) 5%);
        border-color: color-mix(in srgb, var(--dtr-primary) 55%, var(--dtr-border-soft) 45%);
        background:
            linear-gradient(180deg,
                color-mix(in srgb, var(--dtr-primary) 22%, var(--dtr-card-bg) 78%) 0%,
                color-mix(in srgb, var(--dtr-primary) 12%, var(--dtr-card-bg) 88%) 100%);
        box-shadow:
            0 2px 6px color-mix(in srgb, var(--dtr-primary) 18%, transparent),
            0 0 0 1px color-mix(in srgb, #fff 35%, transparent) inset;
        transform: translateY(-1px);
    }
    .dtr-attendance .btn-snapshot:active {
        transform: translateY(0);
    }
    html[data-theme="dark"] .dtr-attendance .btn-snapshot {
        color: #7dd3fc;
        border-color: color-mix(in srgb, var(--dtr-primary) 45%, #334155 55%);
        background:
            linear-gradient(180deg,
                color-mix(in srgb, var(--dtr-primary) 28%, #0f172a 72%) 0%,
                color-mix(in srgb, var(--dtr-primary) 16%, #0c1222 84%) 100%);
        box-shadow:
            0 2px 8px color-mix(in srgb, #000 40%, transparent),
            0 0 0 1px color-mix(in srgb, #fff 6%, transparent) inset;
    }
    html[data-theme="dark"] .dtr-attendance .btn-snapshot:hover {
        color: #bae6fd;
        border-color: color-mix(in srgb, var(--dtr-primary) 65%, #334155 35%);
        background:
            linear-gradient(180deg,
                color-mix(in srgb, var(--dtr-primary) 38%, #0f172a 62%) 0%,
                color-mix(in srgb, var(--dtr-primary) 22%, #0c1222 78%) 100%);
        box-shadow:
            0 4px 12px color-mix(in srgb, var(--dtr-primary) 15%, transparent),
            0 0 0 1px color-mix(in srgb, #fff 8%, transparent) inset;
    }
</style>
@endpush

@section('content')
@php
    $coordinator = auth()->guard('coordinator')->user();
    $assignedProgramsList = isset($assignedPrograms) ? collect($assignedPrograms) : collect();
    $major = $assignedProgramsList->implode(' · ');
    $major = $major !== '' ? $major : ($coordinator->major ?? null);
@endphp
<div class="dtr-attendance">
    <h1 class="page-title">Attendance Logs</h1>
    <p class="page-sub">Attendance Management @if(($filter ?? 'month') === 'week' && !empty($weekLabel)) — {{ $weekLabel }} @else — {{ now()->format('F Y') }}@endif @if($major) · {{ $major }}@endif</p>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="stats-box">
                <h4>Total Students</h4>
                <p class="fs-3 fw-bold mb-0">{{ $totalStudents ?? '-' }}</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-box">
                <h4>Present Today</h4>
                <p class="fs-3 fw-bold mb-0">{{ $presentToday ?? '-' }}</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-box">
                <h4>Absent Today</h4>
                <p class="fs-3 fw-bold mb-0">{{ $absentToday ?? '-' }}</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-box">
                <h4>Total Logs</h4>
                <p class="fs-3 fw-bold mb-0">{{ $logs->count() ?? 0 }}</p>
            </div>
        </div>
    </div>

    @if($assignedProgramsList->isNotEmpty())
    <div class="alert alert-info py-2 mb-3">
        <i class="bi bi-info-circle me-2"></i>
        Showing logs for
        @foreach($assignedProgramsList as $i => $prog)
            @if($i > 0), @endif
            <strong>{{ $prog }}</strong>
        @endforeach
        @if($assignedProgramsList->count() > 1)
            (students rostered across your assigned programs).
        @else
            only.
        @endif
    </div>
    @elseif($major)
    <div class="alert alert-info py-2 mb-3">
        <i class="bi bi-info-circle me-2"></i>Showing logs for <strong>{{ $major }}</strong> only.
    </div>
    @endif

    @if(!empty($viewStudent))
    <div class="view-student-bar">
        <span class="view-student-label"><i class="bi bi-person-video2 me-1"></i>Showing attendance for</span>
        <span class="view-student-name">{{ $viewStudent->name }} ({{ $viewStudent->student_no ?? '—' }})</span>
        <a href="{{ route('coordinator.attendance.logs', array_filter(['month' => request('month', now()->format('Y-m')), 'filter' => $filter ?? 'month', 'week_start' => ($filter ?? '') === 'week' ? ($weekStartInput ?? $weekInput ?? null) : null, 'week_end' => ($filter ?? '') === 'week' ? ($weekEndInput ?? $weekStartInput ?? $weekInput ?? null) : null])) }}" class="btn btn-sm btn-outline-primary btn-show-all ms-2">Show all</a>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <h4 class="mb-3">Logs @if(($filter ?? 'month') === 'week' && !empty($weekLabel))<span class="text-muted fw-normal">({{ $weekLabel }})</span>@elseif($major)<span class="text-muted fw-normal">({{ $major }})</span>@endif</h4>
            {{-- Period + search on one row (wraps on narrow viewports) --}}
            <div class="search-wrap mb-3">
                <form action="{{ route('coordinator.attendance.logs') }}" method="GET" class="search-row coord-logs-toolbar" role="search">
                    <div class="coord-toolbar-period">
                        <div class="filter-tabs" role="group" aria-label="View by month or week">
                            <input type="radio" name="filter" id="filterMonth" value="month" {{ ($filter ?? 'month') === 'month' ? 'checked' : '' }}>
                            <label for="filterMonth">Month</label>
                            <input type="radio" name="filter" id="filterWeek" value="week" {{ ($filter ?? '') === 'week' ? 'checked' : '' }}>
                            <label for="filterWeek">Week</label>
                        </div>
                        <div class="filter-panel {{ ($filter ?? 'month') === 'month' ? 'active' : '' }}" data-filter="month">
                            <input type="month" id="monthSelect" name="month" class="form-control form-control-sm coord-toolbar-input" value="{{ request('month', now()->format('Y-m')) }}" style="width: 9.5rem; max-width: 100%;" aria-label="Month">
                        </div>
                        <div class="filter-panel {{ ($filter ?? '') === 'week' ? 'active' : '' }}" data-filter="week">
                            <input type="week" id="weekStartSelect" name="week_start" class="form-control form-control-sm coord-toolbar-input" value="{{ $weekStartInput ?? $weekInput ?? '' }}" style="width: 10.5rem; max-width: 100%;" aria-label="Start week" title="Start week">
                            <input type="week" id="weekEndSelect" name="week_end" class="form-control form-control-sm coord-toolbar-input" value="{{ $weekEndInput ?? $weekStartInput ?? $weekInput ?? '' }}" style="width: 10.5rem; max-width: 100%;" aria-label="End week" title="End week">
                        </div>
                    </div>
                    <div class="search-inner coord-toolbar-search-inner">
                        <i class="bi bi-search search-icon" aria-hidden="true"></i>
                        <input type="text"
                               name="q"
                               class="search-input"
                               placeholder="Name or student number…"
                               value="{{ old('q', $search ?? '') }}"
                               autocomplete="off"
                               aria-label="Search by name or student number">
                        @if(!empty($search ?? ''))
                        <a href="{{ route('coordinator.attendance.logs', array_filter(['month' => request('month', now()->format('Y-m')), 'filter' => $filter ?? 'month', 'week_start' => ($filter ?? '') === 'week' ? ($weekStartInput ?? $weekInput ?? null) : null, 'week_end' => ($filter ?? '') === 'week' ? ($weekEndInput ?? $weekStartInput ?? $weekInput ?? null) : null])) }}" class="search-clear" title="Clear search" aria-label="Clear search">
                            <i class="bi bi-x-lg"></i>
                        </a>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary btn-search coord-toolbar-submit">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                </form>
                <p class="search-hint mb-0">Select time period and search by name or student number.</p>
            </div>

        @if(!empty($viewStudent))
            {{-- Viewing one student: lg+ uses a width-fit grid; smaller viewports use cards (no horizontal scroll). --}}
            @if($logs->count() > 0)
            <div class="d-none d-lg-block coord-logs-table-inner">
                <div class="coord-logs-table-wrap">
                    <table class="table align-middle table-coordinator-logs table-dtr-layout mb-0">
                        <colgroup>
                            <col class="col-w-date">
                            <col class="col-w-time">
                            <col class="col-w-time">
                            <col class="col-w-time">
                            <col class="col-w-time">
                            <col class="col-w-time">
                            <col class="col-w-hours">
                            <col class="col-w-snaps">
                            <col class="col-w-act">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Morning<br>Time In</th>
                                <th>Lunch<br>Break Out</th>
                                <th>Afternoon<br>Time In</th>
                                <th>Time<br>Out</th>
                                <th>Status</th>
                                <th>Hours<br>Rendered</th>
                                <th>Verification<br>Snapshots</th>
                                <th class="actions-cell text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                @php $__snapItems = $log->coordinatorVerificationSnapshotItems(); @endphp
                                <tr>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($log->date)->format('m/d/y') }}</td>
                                    <td class="dtr-time">
                                        @if($log->time_in)
                                            <span class="badge bg-success">{{ $log->time_in_12 }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="dtr-time">
                                        @if($log->lunch_break_out)
                                            <span class="badge bg-info">{{ $log->lunch_break_out_12 }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="dtr-time">
                                        @if($log->afternoon_time_in)
                                            <span class="badge bg-success">{{ $log->afternoon_time_in_12 }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="dtr-time">
                                        @if($log->time_out)
                                            <span class="badge bg-info">{{ $log->time_out_12 }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="status-cell">
                                        @if($log->time_in || $log->afternoon_time_in)
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Present</span>
                                        @else
                                            <span class="badge bg-secondary">No time in</span>
                                        @endif
                                    </td>
                                    <td class="hours-cell">
                                        {{ $log->hoursRenderedDisplayForCoordinatorLogs() ?? '-' }}
                                    </td>
                                    <td class="snapshots-cell">
                                        @if(count($__snapItems) > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($__snapItems as $__snap)
                                                    <a href="{{ route('coordinator.attendance.verification_snapshot', [$log, $__snap['type']]) }}" target="_blank" rel="noopener" class="btn btn-snapshot"><i class="bi bi-camera-fill"></i> {{ $__snap['label'] }}</a>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="actions-cell align-middle">
                                        <button type="button"
                                                class="btn btn-sm btn-invalidate-open"
                                                data-bs-toggle="modal"
                                                data-bs-target="#invalidateAttendanceModal"
                                                data-invalidate-action="{{ route('coordinator.attendance.invalidate', $log) }}"
                                                title="Request invalidation — ask admin review to exclude this record from reports"
                                                aria-label="Request invalidation for admin review to exclude this record from reports">
                                            <i class="bi bi-shield-exclamation flex-shrink-0" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="d-lg-none coord-attendance-log-cards" role="region" aria-label="Attendance log entries">
                @foreach($logs as $log)
                    @php
                        $__hours = $log->hoursRenderedDisplayForCoordinatorLogs();
                        $__snapItems = $log->coordinatorVerificationSnapshotItems();
                        $__dateLabel = \Carbon\Carbon::parse($log->date)->format('M j, Y');
                    @endphp
                    <article class="coord-attendance-log-card">
                        <div class="coord-log-card-title">{{ $__dateLabel }}</div>
                        <div class="coord-log-card-metrics">
                            <div>
                                <span class="coord-log-label">Morning in</span>
                                @if($log->time_in)
                                    <span class="badge bg-success">{{ $log->time_in_12 }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                            <div>
                                <span class="coord-log-label">Lunch out</span>
                                @if($log->lunch_break_out)
                                    <span class="badge bg-info">{{ $log->lunch_break_out_12 }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                            <div>
                                <span class="coord-log-label">Afternoon in</span>
                                @if($log->afternoon_time_in)
                                    <span class="badge bg-success">{{ $log->afternoon_time_in_12 }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                            <div>
                                <span class="coord-log-label">Time out</span>
                                @if($log->time_out)
                                    <span class="badge bg-info">{{ $log->time_out_12 }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                            <div>
                                <span class="coord-log-label">Status</span>
                                @if($log->time_in || $log->afternoon_time_in)
                                    <span class="badge bg-success">Present</span>
                                @else
                                    <span class="badge bg-secondary">No time in</span>
                                @endif
                            </div>
                            <div>
                                <span class="coord-log-label">Hours</span>
                                <span>{{ $__hours ?? '—' }}</span>
                            </div>
                            <div class="coord-log-slot-span2">
                                <span class="coord-log-label">Verification snapshots</span>
                                @if(count($__snapItems) > 0)
                                    <div class="coord-log-snaps-row">
                                        @foreach($__snapItems as $__snap)
                                            <a href="{{ route('coordinator.attendance.verification_snapshot', [$log, $__snap['type']]) }}" target="_blank" rel="noopener" class="btn btn-snapshot"><i class="bi bi-camera-fill"></i> {{ $__snap['label'] }}</a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">None</span>
                                @endif
                            </div>
                        </div>
                        <div class="coord-log-actions-row">
                            <button type="button"
                                    class="btn btn-sm btn-invalidate-open justify-content-center"
                                    data-bs-toggle="modal"
                                    data-bs-target="#invalidateAttendanceModal"
                                    data-invalidate-action="{{ route('coordinator.attendance.invalidate', $log) }}"
                                    title="Request invalidation — ask admin review to exclude this record from reports"
                                    aria-label="Request invalidation for admin review to exclude this record from reports">
                                <i class="bi bi-shield-exclamation flex-shrink-0" aria-hidden="true"></i>
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
            @else
            <p class="text-muted mb-0">No attendance logs for this student for {{ ($filter ?? 'month') === 'week' ? 'the selected weeks' : 'this month' }}.</p>
            @endif
        @else
            {{-- List of students (no repeating names) with View attendance button --}}
            @if(($studentsWithLogs ?? collect())->count() > 0)
            <p class="text-muted small mb-2">Select a student to view their attendance logs for this period.</p>
            <ul class="student-list">
                @foreach($studentsWithLogs as $student)
                @php
                    $viewParams = array_filter([
                        'student_id' => $student->id ?? null,
                        'month' => request('month', now()->format('Y-m')),
                        'filter' => $filter ?? 'month',
                        'week_start' => ($filter ?? '') === 'week' ? ($weekStartInput ?? $weekInput ?? null) : null,
                        'week_end' => ($filter ?? '') === 'week' ? ($weekEndInput ?? $weekStartInput ?? $weekInput ?? null) : null,
                    ]);
                @endphp
                <li>
                    <span class="student-name">{{ $student->name ?? '-' }}</span>
                    <span class="student-no">{{ $student->student_no ?? '—' }}</span>
                    <a href="{{ route('coordinator.attendance.logs', $viewParams) }}" class="btn btn-primary btn-sm btn-view-attendance">
                        <i class="bi bi-person-lines-fill me-1"></i>View attendance
                    </a>
                </li>
                @endforeach
            </ul>
            @else
            @if(!empty($search ?? ''))
            <div class="text-center py-4 text-muted">
                <i class="bi bi-search d-block fs-2 mb-2" style="color: var(--dtr-muted); opacity: 0.45;"></i>
                <p class="mb-0 fw-medium">No logs match "{{ e($search) }}"</p>
                <p class="small mt-1 mb-0">Try a different name or student number, or <a href="{{ route('coordinator.attendance.logs', array_filter(['month' => request('month', now()->format('Y-m')), 'filter' => $filter ?? 'month', 'week_start' => ($filter ?? '') === 'week' ? ($weekStartInput ?? $weekInput ?? null) : null, 'week_end' => ($filter ?? '') === 'week' ? ($weekEndInput ?? $weekStartInput ?? $weekInput ?? null) : null])) }}">clear the search</a>.</p>
            </div>
            @else
            <p class="text-muted mb-0">No attendance logs for {{ ($filter ?? 'month') === 'week' ? 'the selected weeks' : 'this month' }}.</p>
            @endif
            @endif
        @endif
        </div>
    </div>

    <div class="modal fade invalidate-attendance-modal" id="invalidateAttendanceModal" tabindex="-1" aria-labelledby="invalidateAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="invalidateAttendanceForm" method="POST" action="">
                    @csrf
                    <div class="modal-header">
                        <div class="invalidate-modal-head">
                            <span class="invalidate-modal-icon" aria-hidden="true"><i class="bi bi-shield-exclamation"></i></span>
                            <div class="min-w-0">
                                <h5 class="modal-title" id="invalidateAttendanceModalLabel">Request invalidation</h5>
                                <p class="small mb-0 invalidate-modal-sub">Admin approval is required before this record can be excluded from official reports. Describe the issue clearly.</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label for="invalidateReasonInput" class="form-label">Reason <span class="text-danger">*</span></label>
                        <div class="invalidate-reason-bubble mt-1">
                            <textarea id="invalidateReasonInput"
                                      name="reason"
                                      class="form-control invalidate-reason-textarea"
                                      rows="4"
                                      maxlength="1000"
                                      required
                                      placeholder="e.g. Incorrect time-in, duplicate entry, wrong student…"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn dtr-mbtn dtr-mbtn--cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit"
                                class="btn dtr-mbtn dtr-mbtn--rose"
                                data-norsu-confirm="Submit this invalidation request? An admin will review it before the record is excluded from reports."
                                data-norsu-variant="warning">
                            <i class="bi bi-send" aria-hidden="true"></i> Submit request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var invModal = document.getElementById('invalidateAttendanceModal');
    if (invModal) {
        invModal.addEventListener('show.bs.modal', function (ev) {
            var btn = ev.relatedTarget;
            var form = document.getElementById('invalidateAttendanceForm');
            if (!form || !btn || typeof btn.getAttribute !== 'function') return;
            var action = btn.getAttribute('data-invalidate-action');
            if (action) form.setAttribute('action', action);
            var ta = document.getElementById('invalidateReasonInput');
            if (ta) {
                ta.value = '';
                ta.classList.remove('is-invalid');
            }
        });
    }
})();
(function() {
    var filterMonth = document.getElementById('filterMonth');
    var filterWeek = document.getElementById('filterWeek');
    var panels = document.querySelectorAll('.dtr-attendance .filter-panel');
    var weekStartInput = document.getElementById('weekStartSelect');
    var weekEndInput = document.getElementById('weekEndSelect');
    function currentIsoWeek() {
        var now = new Date();
        var start = new Date(now);
        start.setDate(now.getDate() - now.getDay() + (now.getDay() === 0 ? -6 : 1));
        var y = start.getFullYear();
        var w = Math.ceil((((start - new Date(y, 0, 1)) / 86400000) + 1) / 7);
        return y + '-W' + String(w).padStart(2, '0');
    }
    function updatePanels() {
        var isWeek = filterWeek && filterWeek.checked;
        panels.forEach(function(p) {
            p.classList.toggle('active', p.getAttribute('data-filter') === (isWeek ? 'week' : 'month'));
        });
        if (isWeek) {
            var fallbackWeek = currentIsoWeek();
            if (weekStartInput && !weekStartInput.value) {
                weekStartInput.value = fallbackWeek;
            }
            if (weekEndInput && !weekEndInput.value) {
                weekEndInput.value = weekStartInput && weekStartInput.value ? weekStartInput.value : fallbackWeek;
            }
        }
    }
    if (weekStartInput && weekEndInput) {
        weekStartInput.addEventListener('change', function() {
            if (!weekEndInput.value || weekEndInput.value < weekStartInput.value) {
                weekEndInput.value = weekStartInput.value;
            }
        });
    }
    if (filterMonth) filterMonth.addEventListener('change', updatePanels);
    if (filterWeek) filterWeek.addEventListener('change', updatePanels);
    updatePanels();
})();
</script>
@endpush
@endsection
