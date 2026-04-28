@extends('layouts.student')

@section('title', 'Student Dashboard')

@push('styles')
<style>
    .page-title { text-align: center; }
    .page-sub { text-align: center; }
    .dashboard-card,
    .time-log-card,
    .attendance-card {
        background: var(--dtr-card-bg);
        border-color: var(--dtr-border-soft);
        color: var(--dtr-text);
    }
    .time-log-table thead th,
    .recent-logs-table thead th,
    .table thead th {
        color: var(--dtr-heading);
        border-bottom-color: var(--dtr-border-strong);
    }
    .time-log-table tbody td,
    .recent-logs-table tbody td,
    .table tbody td {
        border-bottom-color: var(--dtr-row-divider);
    }
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
    /* Single flex row: icon + one text block (avoids BS5 .alert flex child gaps) */
    .alert-face-enrollment-missing {
        display: flex;
        flex-direction: row;
        align-items: flex-start; /* override theme .alert { align-items: center } for multi-line copy */
        gap: 0.6rem;
        border-radius: 12px;
        border-left: 4px solid #dc2626;
        border: 1px solid rgba(220, 38, 38, 0.35);
        background: rgba(220, 38, 38, 0.08);
        color: #7f1d1d;
        font-size: 0.92rem;
        line-height: 1.45;
        text-align: left;
        margin-bottom: 1rem;
    }
    .alert-face-enrollment-missing .fe-icon {
        flex-shrink: 0;
        line-height: 1;
        margin-top: 0.1rem;
        color: #dc2626;
        font-size: 1.1rem;
    }
    .alert-face-enrollment-missing .fe-body {
        flex: 1;
        min-width: 0;
    }
    .alert-face-enrollment-missing .fe-title {
        display: block;
        color: #991b1b;
        font-weight: 700;
        margin: 0 0 0.35rem 0;
        line-height: 1.3;
    }
    .alert-face-enrollment-missing .fe-text {
        margin: 0;
    }
    .alert-face-enrollment-missing a {
        color: #1d4ed8;
        font-weight: 600;
        text-decoration: underline;
        text-underline-offset: 2px;
    }
    html[data-theme="dark"] .alert-face-enrollment-missing {
        border-color: rgba(248, 113, 113, 0.45);
        background: rgba(239, 68, 68, 0.14);
        color: #fecaca;
    }
    html[data-theme="dark"] .alert-face-enrollment-missing .fe-icon { color: #f87171; }
    html[data-theme="dark"] .alert-face-enrollment-missing .fe-title { color: #fecaca; }
    html[data-theme="dark"] .alert-face-enrollment-missing a { color: #93c5fd; }
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
    .manual-request-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 0.75rem;
        margin-bottom: 0.9rem;
    }
    .manual-request-status {
        display: inline-flex;
        align-items: center;
        padding: 0.22rem 0.58rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .manual-request-status.status-pending {
        background: rgba(245, 158, 11, 0.16);
        color: #b45309;
    }
    .manual-request-status.status-approved {
        background: rgba(16, 185, 129, 0.16);
        color: #047857;
    }
    .manual-request-status.status-rejected {
        background: rgba(239, 68, 68, 0.14);
        color: #b91c1c;
    }
    .manual-request-history td {
        vertical-align: top;
        font-size: 0.84rem;
    }
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
    #verificationStatus.flash-alert {
        animation: faceMismatchFlash 0.22s ease-in-out 0s 4;
    }
    @keyframes faceMismatchFlash {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-3px); }
        75% { transform: translateX(3px); }
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
        @php
            $showLunchBreakButton = false;
            $timeOutCoolingDown = !empty($timeOutUnlockAtIso ?? null);
            $canTimeOut = false;
            $showTimeOutLockedHint = true;
        @endphp
        <h1 class="page-title">Dashboard</h1>
        <p class="page-sub">Welcome, {{ auth()->guard('student')->user()->name }}</p>

        @if(empty(optional(auth()->guard('student')->user())->face_encoding))
            <div class="alert alert-face-enrollment-missing" role="alert">
                <i class="bi bi-camera-video-off fe-icon" aria-hidden="true"></i>
                <div class="fe-body">
                    <span class="fe-title">Face enrollment is missing</span>
                    <p class="fe-text">
                        Your account may have been registered on a device without camera access. Complete face enrollment in
                        <a href="{{ route('student.settings') }}">Settings</a>
                        before using camera verification for attendance.
                    </p>
                </div>
            </div>
        @endif
        
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

            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i>
                Attendance capture is now kiosk-only. Please proceed to your designated kiosk station for
                <strong>Time In</strong>, <strong>Lunch / break out</strong>, and <strong>Time Out</strong>.
                This dashboard is for tracking and records viewing only.
            </div>
        </div>

        <div class="card-section">
            <div class="card-header">
                <i class="bi bi-journal-medical"></i>
                <h4>Manual Attendance Handling</h4>
            </div>
            <p class="text-muted small mt-0 mb-3">
                Students can no longer submit manual attendance from this page. For corrections, coordinate directly
                with your assigned coordinator or admin for override/invalidation workflow.
            </p>
            @if(isset($manualRequests) && $manualRequests->count() > 0)
                <div class="table-container mt-3 manual-request-history">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th>Coordinator/Admin note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($manualRequests as $requestRow)
                                <tr>
                                    <td>{{ $requestRow->attendance_date?->format('M d, Y') }}</td>
                                    <td>
                                        <span class="manual-request-status status-{{ $requestRow->status }}">
                                            {{ ucfirst($requestRow->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $requestRow->reason }}</td>
                                    <td>
                                        @if($requestRow->coordinator_note)
                                            <div>{{ $requestRow->coordinator_note }}</div>
                                            @if($requestRow->reviewed_at)
                                                <div class="small text-muted mt-1">
                                                    {{ $requestRow->reviewer?->name ? 'Coordinator: '.$requestRow->reviewer->name : 'Reviewed by admin' }}
                                                </div>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Today's Attendance Summary -->
        <div class="card-section">
            <div class="card-header">
                <i class="bi bi-calendar-check"></i>
                <h4>Today's Attendance</h4>
            </div>
            @if(isset($attendance) && $attendance)
                <div class="attendance-summary-grid">
                    <div class="summary-item">
                        <div class="label">Morning Time In</div>
                        <div class="value">
                            @if($attendance->time_in)
                                <span class="badge bg-success">{{ $attendance->time_in_12 }}</span>
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
                                <span class="badge bg-success">{{ $attendance->afternoon_time_in_12 }}</span>
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
                                $hoursRendered = $attendance->hours_rendered_display ?? '';
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

</script>
@endpush

