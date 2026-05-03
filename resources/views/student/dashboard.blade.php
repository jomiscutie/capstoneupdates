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

    /* ---- Student dashboard shell (light / dark adaptive) ---- */
    .stu-dash {
        max-width: 920px;
        margin: 0 auto;
        padding-bottom: 1.5rem;
    }
    .stu-dash > .page-title {
        margin: 0 0 0.35rem;
        font-size: clamp(1.45rem, 3.5vw, 1.75rem);
        font-weight: 800;
        letter-spacing: -0.035em;
        color: var(--dtr-heading);
        text-align: center;
    }
    .stu-dash > .page-sub {
        margin: 0 auto 1.35rem;
        text-align: center;
        font-size: 0.9375rem;
        color: var(--dtr-muted);
        max-width: 36rem;
    }
    .stu-dash .term-status-card {
        margin-bottom: 1.25rem;
    }

    .stu-dash .card-section {
        background: var(--dtr-card-bg);
        border: 1px solid var(--dtr-border-soft);
        border-radius: 16px;
        box-shadow: var(--dtr-shadow-soft);
        padding: 1.2rem 1.25rem;
        margin-bottom: 1.25rem;
    }
    .stu-dash .card-section > .card-header {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0 0 0.9rem;
        margin: 0 0 1rem;
        border-bottom: 1px solid var(--dtr-border-soft);
        border-radius: 0;
        background: transparent !important;
    }
    .stu-dash .card-section > .card-header i {
        font-size: 1.2rem;
        color: var(--dtr-primary);
        flex-shrink: 0;
    }
    .stu-dash .card-section > .card-header h4 {
        margin: 0;
        font-size: 1.0625rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--dtr-heading);
    }

    .stu-dash .alert {
        padding: 0.62rem 0.92rem;
        font-size: 0.878rem;
        line-height: 1.45;
        border-radius: 12px;
        margin-bottom: 0.6rem;
        border-width: 1px;
        box-shadow: none;
    }
    .stu-dash .alert:last-of-type { margin-bottom: 1rem; }
    .stu-dash .alert .btn-close {
        padding: 0.6rem;
    }
    html[data-theme="dark"] .stu-dash .alert-success {
        background: rgba(34, 197, 94, 0.12);
        border-color: rgba(34, 197, 94, 0.38);
        color: #bbf7d0;
    }
    html[data-theme="dark"] .stu-dash .alert-warning:not(.late-alert) {
        background: rgba(245, 158, 11, 0.12);
        border-color: rgba(245, 158, 11, 0.4);
        color: #fde68a;
    }
    html[data-theme="dark"] .stu-dash .alert-danger {
        background: rgba(248, 113, 113, 0.1);
        border-color: rgba(248, 113, 113, 0.4);
        color: #fecaca;
    }
    html[data-theme="dark"] .stu-dash .alert-info {
        background: rgba(59, 130, 246, 0.12);
        border-color: rgba(59, 130, 246, 0.38);
        color: #bfdbfe;
    }

    .stu-dash .attendance-status-notice {
        flex-direction: row;
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 0.5rem;
        margin-bottom: 1rem !important;
    }
    .stu-dash .notice-item.notice-recorded {
        flex: 1 1 auto;
        min-width: min(100%, 200px);
        max-width: 100%;
        padding: 0.5rem 0.75rem;
        border-radius: 10px;
        font-size: 0.8rem;
        line-height: 1.38;
        background: color-mix(in srgb, #d97706 10%, var(--dtr-card-bg));
        border: 1px solid color-mix(in srgb, #d97706 32%, var(--dtr-border-soft));
        color: var(--dtr-text);
    }
    html[data-theme="dark"] .stu-dash .notice-item.notice-recorded {
        background: rgba(251, 191, 36, 0.08);
        border-color: rgba(251, 191, 36, 0.32);
        color: var(--dtr-text);
    }
    .stu-dash .notice-item.notice-recorded i {
        color: #c2410c;
        margin-right: 0.35rem;
    }
    html[data-theme="dark"] .stu-dash .notice-item.notice-recorded i {
        color: #fbbf24;
    }

    .stu-dash .time-display {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 1.15rem;
    }
    @media (max-width: 620px) {
        .stu-dash .time-display { grid-template-columns: 1fr; }
    }
    .stu-dash .time-item {
        padding: 0.92rem 1rem;
        border-radius: 12px;
        background: var(--dtr-surface-soft);
        border: 1px solid var(--dtr-border-soft);
        text-align: left;
        min-height: 4.5rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .stu-dash .time-item .label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--dtr-muted);
        margin-bottom: 0.3rem;
    }
    .stu-dash .time-item .value {
        font-size: 1.0625rem;
        font-weight: 700;
        color: var(--dtr-heading);
        font-variant-numeric: tabular-nums;
        line-height: 1.25;
    }
    .stu-dash .time-item #clock { font-size: 1.2rem; }

    .stu-dash .text-muted.small,
    .stu-dash p.text-muted.small {
        font-size: 0.8125rem;
        line-height: 1.5;
        color: var(--dtr-muted) !important;
    }

    .stu-dash .late-alert {
        border-radius: 12px !important;
        padding: 0.65rem 0.92rem !important;
        margin-bottom: 1rem !important;
    }

    .stu-dash .attendance-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(158px, 1fr));
        gap: 0.75rem;
    }
    .stu-dash .summary-item {
        padding: 0.82rem 0.92rem;
        border-radius: 12px;
        background: var(--dtr-surface-soft);
        border: 1px solid var(--dtr-border-soft);
        min-height: 4.85rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.35rem;
    }
    .stu-dash .summary-item .label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: var(--dtr-muted);
        line-height: 1.2;
    }
    .stu-dash .summary-item .value {
        font-size: 0.95rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        color: var(--dtr-heading);
        line-height: 1.35;
        word-break: break-word;
    }
    .stu-dash .summary-item .badge {
        font-weight: 700;
        font-size: 0.78rem !important;
        padding: 0.35rem 0.55rem !important;
        border-radius: 8px !important;
    }
    .stu-dash .stu-dash-empty-attendance i {
        color: var(--dtr-muted) !important;
        opacity: 0.85;
    }
    .stu-dash-kiosk-hint {
        padding: 0.75rem 0.95rem;
        border-radius: 12px;
        background: var(--dtr-surface-soft);
        border: 1px solid var(--dtr-border-soft);
        line-height: 1.5;
    }
    .stu-dash .stu-dash-manual-form .form-label {
        color: var(--dtr-muted);
        font-weight: 600;
    }
    /* Manual requests modal */
    .stu-manual-requests-modal .modal-content {
        border-radius: 16px;
        border: 1px solid var(--dtr-border-soft);
        background: var(--dtr-card-bg);
        color: var(--dtr-text);
        box-shadow: var(--dtr-shadow-soft, 0 18px 50px rgba(15, 23, 42, 0.18));
        overflow: hidden;
    }
    .stu-manual-requests-modal .modal-header {
        border-bottom: 1px solid var(--dtr-border-soft);
        padding: 1rem 1.15rem;
        align-items: flex-start;
        gap: 0.75rem;
    }
    .stu-manual-requests-modal .manual-req-modal-head {
        display: flex;
        gap: 0.85rem;
        align-items: flex-start;
        flex: 1;
        min-width: 0;
    }
    .stu-manual-requests-modal .manual-req-modal-icon {
        flex-shrink: 0;
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: var(--dtr-primary);
        background: var(--dtr-primary-soft);
        border: 1px solid color-mix(in srgb, var(--dtr-primary) 28%, var(--dtr-border-soft));
    }
    .stu-manual-requests-modal .modal-title {
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--dtr-heading);
        font-size: 1.08rem;
    }
    .stu-manual-requests-modal .manual-req-modal-sub {
        color: var(--dtr-muted) !important;
        font-size: 0.84rem !important;
        line-height: 1.45;
        margin-top: 0.2rem !important;
    }
    .stu-manual-requests-modal .modal-body {
        padding-top: 1rem;
    }
    .stu-manual-requests-modal .manual-req-filter-card {
        padding: 1rem 1.05rem;
        border-radius: 14px;
        background: var(--dtr-surface-soft);
        border: 1px solid var(--dtr-border-soft);
        margin-bottom: 1rem;
    }
    .stu-manual-requests-modal .manual-req-filter-card .form-label {
        color: var(--dtr-heading);
        font-size: 0.8rem;
    }
    .stu-manual-requests-modal .manual-req-month-input {
        max-width: 14rem;
        min-height: 46px;
        border-radius: 12px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        background: var(--dtr-input-bg);
        border: 1.5px solid var(--dtr-input-border);
        color: var(--dtr-text);
    }
    .stu-manual-requests-modal .manual-req-month-input:focus {
        border-color: color-mix(in srgb, var(--dtr-primary) 55%, var(--dtr-input-border));
        box-shadow: 0 0 0 0.2rem color-mix(in srgb, var(--dtr-primary) 18%, transparent);
    }
    .stu-manual-requests-modal .manual-requests-table-wrap {
        border-color: var(--dtr-border-soft) !important;
        background: var(--dtr-card-bg);
    }
    .stu-manual-requests-modal .manual-requests-table-wrap thead th {
        background: var(--dtr-surface-soft);
        color: var(--dtr-heading);
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        border-bottom-color: var(--dtr-border-soft);
        white-space: nowrap;
    }
    .stu-manual-requests-modal .manual-requests-table-wrap tbody td {
        border-color: var(--dtr-border-soft);
        vertical-align: top;
        font-size: 0.875rem;
    }
    html[data-theme="dark"] .stu-manual-requests-modal .manual-requests-table-wrap .table-light {
        --bs-table-bg: var(--dtr-surface-soft);
        --bs-table-color: var(--dtr-heading);
    }
    html[data-theme="dark"] .stu-manual-requests-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    .stu-manual-requests-modal .modal-footer {
        padding: 0.75rem 1rem 1.1rem;
    }
</style>
@endpush

@section('content')
    @if(auth()->guard('student')->check())
        <div class="stu-dash">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-sub">Welcome, {{ auth()->guard('student')->user()->name }}</p>
        
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

        <!-- Today's Time & Date -->
        <div class="card-section">
            <div class="card-header">
                <i class="bi bi-calendar3"></i>
                <h4>Today's Time &amp; Date</h4>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show">
                    <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
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
                    @if(session('error_type'))
                        <strong>Verification:</strong>
                    @endif
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(isset($attendance) && $attendance)
            <div class="attendance-status-notice mb-3">
                @if($attendance->time_in)
                    <div class="notice-item notice-recorded">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <span>Morning time-in recorded today at <strong>{{ $attendance->time_in_12 }}</strong>.</span>
                    </div>
                @endif
                @if($attendance->afternoon_time_in)
                    <div class="notice-item notice-recorded">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <span>Afternoon time-in recorded today at <strong>{{ $attendance->afternoon_time_in_12 }}</strong>.</span>
                    </div>
                @endif
                @if($attendance->lunch_break_out)
                    <div class="notice-item notice-recorded">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <span>Lunch / break out recorded at <strong>{{ $attendance->lunch_break_out_12 }}</strong> (A.M. departure).</span>
                    </div>
                @endif
                @if($attendance->time_out)
                    <div class="notice-item notice-recorded">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <span>Time-out recorded today at <strong>{{ $attendance->time_out_12 }}</strong>.</span>
                    </div>
                @endif
            </div>
            @endif

            <div class="time-display">
                <div class="time-item">
                    <div class="label">Today</div>
                    <div class="value" id="day">-</div>
                </div>
                <div class="time-item">
                    <div class="label">Current time</div>
                    <div class="value" id="clock">-</div>
                </div>
                <div class="time-item">
                    <div class="label">Month &amp; year</div>
                    <div class="value" id="month-year">-</div>
                </div>
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
                <div class="text-center py-4 stu-dash-empty-attendance">
                    <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3 mb-0">No attendance record for today</p>
                </div>
            @endif
        </div>

        @php $maxManualDate = now('Asia/Manila')->format('Y-m-d'); @endphp
        <div class="card-section" id="manual-request-form-card">
            <div class="card-header align-items-start flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                    <i class="bi bi-journal-plus"></i>
                    <h4 class="mb-0">Manual attendance request</h4>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm flex-shrink-0 text-nowrap" data-bs-toggle="modal" data-bs-target="#manualRequestsHistoryModal">
                    <i class="bi bi-list-ul me-1"></i>View recent requests
                </button>
            </div>
            <p class="text-muted small mb-3">Request a manual entry when you missed the kiosk or need a correction. Enter the <strong>date</strong> and at least <strong>one time</strong>. Your coordinator must approve before it appears on your record. You cannot request a date that already has attendance — use your coordinator for invalidations.</p>

            @if($errors->any())
                <div class="alert alert-danger">
                    <strong class="d-block mb-1">Please fix the following:</strong>
                    <ul class="mb-0 ps-3 small">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('student.manual.request') }}" class="stu-dash-manual-form">
                @csrf
                <div class="row g-2 g-md-3">
                    <div class="col-md-6">
                        <label for="manual_attendance_date" class="form-label">Attendance date <span class="text-danger">*</span></label>
                        <input type="date" name="attendance_date" id="manual_attendance_date" class="form-control" required max="{{ $maxManualDate }}" value="{{ old('attendance_date', $maxManualDate) }}">
                    </div>
                </div>
                <div class="row g-2 g-md-3 mt-2 stu-dash-manual-times">
                    <div class="col-6 col-lg-3">
                        <label class="form-label" for="manual_time_in">Morning time in</label>
                        <input type="time" step="60" name="time_in" id="manual_time_in" class="form-control" value="{{ old('time_in') }}">
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="form-label" for="manual_lunch">A.M. departure (lunch)</label>
                        <input type="time" step="60" name="lunch_break_out" id="manual_lunch" class="form-control" value="{{ old('lunch_break_out') }}">
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="form-label" for="manual_pm_in">Afternoon time in</label>
                        <input type="time" step="60" name="afternoon_time_in" id="manual_pm_in" class="form-control" value="{{ old('afternoon_time_in') }}">
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="form-label" for="manual_time_out">Time out</label>
                        <input type="time" step="60" name="time_out" id="manual_time_out" class="form-control" value="{{ old('time_out') }}">
                    </div>
                </div>
                <p class="text-muted small mb-0 mt-1">Times must be in realistic order (morning in → lunch out → afternoon in → out).</p>
                <div class="mt-3">
                    <label for="manual_reason" class="form-label">Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" id="manual_reason" class="form-control" rows="3" required maxlength="1500" placeholder="e.g. Kiosk unavailable, supervised off-site activity, verified by supervisor…">{{ old('reason') }}</textarea>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send-fill me-1"></i>Submit request
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#manualRequestsHistoryModal">
                        <i class="bi bi-inboxes me-1"></i>See your requests
                    </button>
                </div>
            </form>
        </div>

        @php $manualModalMaxMonth = now('Asia/Manila')->format('Y-m'); @endphp
        <div class="modal fade stu-manual-requests-modal" id="manualRequestsHistoryModal" tabindex="-1" aria-labelledby="manualRequestsHistoryModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="manual-req-modal-head">
                            <span class="manual-req-modal-icon" aria-hidden="true"><i class="bi bi-inboxes"></i></span>
                            <div class="min-w-0">
                                <h5 class="modal-title mb-0" id="manualRequestsHistoryModalLabel">Recent manual requests</h5>
                                <p class="manual-req-modal-sub mb-0">Pick a month to see what you submitted and your coordinator’s decision.</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="manual-req-filter-card">
                            <label for="manualRequestsMonthPicker" class="form-label">Calendar month</label>
                            <div class="d-flex flex-wrap align-items-stretch align-items-md-end gap-2">
                                <input type="month"
                                       id="manualRequestsMonthPicker"
                                       class="form-control manual-req-month-input"
                                       max="{{ $manualModalMaxMonth }}"
                                       value="{{ $manualModalMaxMonth }}"
                                       aria-describedby="manualRequestsMonthHelp">
                                <button type="button" class="btn btn-primary manual-req-refresh-btn" id="manualRequestsReloadBtn">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                                </button>
                            </div>
                            <p class="small mb-0 pt-2 mt-3 border-top" style="border-color: var(--dtr-border-soft) !important;">
                                <span class="text-muted">Showing:&nbsp;</span>
                                <span id="manualRequestsMonthLabelBadge" style="color: var(--dtr-heading);" class="fw-semibold">—</span>
                            </p>
                            <p id="manualRequestsMonthHelp" class="text-muted small mb-0 mt-2">Uses the native month picker — works in modern browsers on desktop and mobile.</p>
                        </div>

                        <div id="manualRequestsAlert" class="alert alert-danger small py-2 mb-3 d-none" role="alert"></div>

                        <div id="manualRequestsTableWrap" class="manual-requests-table-wrap rounded-3 d-none">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0 align-middle" id="manualRequestsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Date</th>
                                            <th scope="col">Times</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Details</th>
                                        </tr>
                                    </thead>
                                    <tbody id="manualRequestsTableBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <div id="manualRequestsEmpty" class="text-center py-5 rounded-3 d-none" style="background: var(--dtr-surface-soft); border: 1px dashed var(--dtr-border-soft);">
                            <i class="bi bi-journal-text d-block mb-2" style="font-size: 2.25rem; color: var(--dtr-muted);"></i>
                            <p class="text-muted mb-1 fw-semibold" style="color: var(--dtr-heading) !important;">No requests this month</p>
                            <p class="text-muted small mb-0">Submit a manual attendance request above if you missed the kiosk.</p>
                        </div>

                        <div id="manualRequestsLoading" class="text-center py-5 d-none">
                            <div class="spinner-border text-primary" style="width: 2.5rem; height: 2.5rem;" role="status" aria-label="Loading">
                                <span class="visually-hidden">Loading…</span>
                            </div>
                            <p class="text-muted small mt-2 mb-0">Loading requests…</p>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        </div>
    @else
        <div class="card text-center mt-5 p-5">
            <h2>Welcome, Guest</h2>
            <p>Please <a href="{{ route('login') }}">Login</a> or <a href="{{ route('student.register') }}">Register</a> to access your dashboard.</p>
        </div>
    @endif
@endsection

@push('scripts')
<script>
function updateClock() {
    const now = new Date();
    const clockEl = document.getElementById('clock');
    const dayEl = document.getElementById('day');
    const monthYearEl = document.getElementById('month-year');
    if (clockEl) {
        clockEl.innerText = now.toLocaleTimeString('en-PH', {
            timeZone: 'Asia/Manila',
            hour12: true,
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
        });
    }
    if (dayEl) {
        dayEl.innerText = now.toLocaleDateString('en-PH', {
            timeZone: 'Asia/Manila',
        });
    }
    if (monthYearEl) {
        monthYearEl.innerText = now.toLocaleDateString('en-PH', {
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

(function () {
    var modalEl = document.getElementById('manualRequestsHistoryModal');
    var monthInput = document.getElementById('manualRequestsMonthPicker');
    var reloadBtn = document.getElementById('manualRequestsReloadBtn');
    var tbody = document.getElementById('manualRequestsTableBody');
    var monthBadge = document.getElementById('manualRequestsMonthLabelBadge');
    var alertBox = document.getElementById('manualRequestsAlert');
    var tableWrap = document.getElementById('manualRequestsTableWrap');
    var emptyState = document.getElementById('manualRequestsEmpty');
    var loadingEl = document.getElementById('manualRequestsLoading');
    var url = @json(route('student.manual.requests.json'));
    var abortCtl = null;

    if (!tbody || !modalEl || !monthInput || !monthBadge) {
        return;
    }

    function hideAlert() {
        alertBox.classList.add('d-none');
        alertBox.textContent = '';
    }

    function showAlert(msg) {
        alertBox.textContent = msg;
        alertBox.classList.remove('d-none');
    }

    function setLoading(on) {
        if (!loadingEl || !tableWrap || !emptyState) return;
        if (on) {
            hideAlert();
            loadingEl.classList.remove('d-none');
            tableWrap.classList.add('d-none');
            emptyState.classList.add('d-none');
        } else {
            loadingEl.classList.add('d-none');
        }
    }

    function escapeHtml(s) {
        if (s === null || s === undefined || s === '') return '';
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function statusBadgeClass(st) {
        if (st === 'approved') return 'bg-success';
        if (st === 'rejected') return 'bg-danger';
        return 'bg-warning text-dark';
    }

    function humanStatus(st) {
        if (!st) return 'Pending';
        return st.charAt(0).toUpperCase() + st.slice(1);
    }

    function render(data) {
        hideAlert();
        monthBadge.textContent = data.month_label || data.month || '—';
        var rows = data.requests || [];
        tbody.innerHTML = '';
        if (!rows.length) {
            tableWrap.classList.add('d-none');
            emptyState.classList.remove('d-none');
            return;
        }
        emptyState.classList.add('d-none');
        tableWrap.classList.remove('d-none');

        rows.forEach(function (r) {
            var st = String(r.status || 'pending');
            var tr = document.createElement('tr');
            var details = '<span class="fw-semibold d-block" style="color: var(--dtr-heading);">' + escapeHtml(r.reviewer_name || '—') + '</span>';
            if (r.reason_preview) {
                details += '<span class="d-block text-muted mt-1 small" title="' + escapeHtml(r.reason_full || '') + '">Your reason: ' + escapeHtml(r.reason_preview) + '</span>';
            }
            if (r.coordinator_note) {
                details += '<span class="d-block mt-1 small" style="color: var(--dtr-muted);"><strong>Note:</strong> ' + escapeHtml(r.coordinator_note) + '</span>';
            }
            tr.innerHTML =
                '<td class="text-nowrap">' + escapeHtml(r.attendance_date_display || '—') + '</td>' +
                '<td class="small">' + escapeHtml(r.times_summary || '—') + '</td>' +
                '<td><span class="badge rounded-pill ' + statusBadgeClass(st) + '">' + escapeHtml(humanStatus(st)) + '</span></td>' +
                '<td class="small">' + details + '</td>';
            tbody.appendChild(tr);
        });
    }

    function load() {
        var month = monthInput.value.trim();
        if (!month || !tbody) return;
        if (abortCtl) abortCtl.abort();
        abortCtl = new AbortController();
        setLoading(true);

        fetch(url + '?month=' + encodeURIComponent(month), {
            method: 'GET',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            signal: abortCtl.signal,
        })
            .then(function (res) {
                return res.json().then(function (body) {
                    if (!res.ok) {
                        throw new Error(body.message || 'Could not load requests.');
                    }
                    return body;
                });
            })
            .then(render)
            .catch(function (err) {
                if (err.name === 'AbortError') return;
                tableWrap.classList.add('d-none');
                emptyState.classList.add('d-none');
                showAlert(err.message || 'Something went wrong. Try again.');
            })
            .finally(function () {
                setLoading(false);
            });
    }

    modalEl.addEventListener('shown.bs.modal', function () {
        load();
    });

    monthInput.addEventListener('change', load);
    if (reloadBtn) reloadBtn.addEventListener('click', load);
})();
</script>
@endpush

