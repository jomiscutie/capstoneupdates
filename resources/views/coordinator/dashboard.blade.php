@extends('layouts.coordinator')

@section('title', 'Coordinator Dashboard')

@push('styles')
<style>
    .page-title { font-size: 1.5rem; font-weight: 600; color: var(--dtr-text); margin-bottom: 0.35rem; }
    .page-sub { font-size: 0.9rem; color: var(--dtr-muted); margin-bottom: 1.5rem; }
    .program-badge { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.75rem; background: #f0f4ff; color: var(--dtr-primary); border-radius: 6px; font-size: 0.8rem; font-weight: 500; margin-top: 0.5rem; }
    .info-alert { background: #f8fafc; border-left: 3px solid var(--dtr-primary); border-radius: 6px; padding: 0.9rem 1.1rem; margin-bottom: 1.5rem; font-size: 0.9rem; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .stat-card { background: var(--dtr-card-bg); padding: 1.2rem; border-radius: 10px; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
    .stat-card .label { font-size: 0.75rem; color: var(--dtr-muted); text-transform: uppercase; letter-spacing: 0.04em; font-weight: 600; margin-bottom: 0.25rem; }
    .stat-card .sub-label { font-size: 0.7rem; color: #94a3b8; margin-bottom: 0.5rem; }
    .stat-card .number { font-size: 1.65rem; font-weight: 700; color: var(--dtr-text); font-variant-numeric: tabular-nums; }
    .stat-card .stat-icon { font-size: 1.2rem; margin-bottom: 0.5rem; }
    .stat-card.primary .stat-icon { color: var(--dtr-primary); }
    .stat-card.success .stat-icon { color: #059669; }
    .stat-card.danger .stat-icon { color: #dc2626; }
    .stat-card.warning .stat-icon { color: #d97706; }
    .actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; }
    .action-card { background: var(--dtr-card-bg); padding: 1.2rem; border-radius: 10px; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 1px 2px rgba(0,0,0,0.04); transition: border-color 0.15s ease; }
    .action-card:hover { border-color: rgba(37,99,235,0.15); }
    .action-card h3 { font-size: 1rem; font-weight: 600; color: var(--dtr-text); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem; }
    .action-card p { font-size: 0.85rem; color: var(--dtr-muted); margin-bottom: 1rem; line-height: 1.45; }
    .action-card .btn-minimal { min-height: 40px; }
</style>
@endpush

@section('content')
    <h1 class="page-title">Dashboard</h1>
    <p class="page-sub">Welcome back, {{ auth()->guard('coordinator')->user()->name }}</p>
    @if(auth()->guard('coordinator')->user()->major)
        <div class="program-badge">
            <i class="bi bi-mortarboard"></i>
            <span>{{ auth()->guard('coordinator')->user()->major }}</span>
        </div>
    @endif

    @if(auth()->guard('coordinator')->user()->major)
    <div class="info-alert">
        <strong></strong> Data shown is for <strong>{{ auth()->guard('coordinator')->user()->major }}</strong> students.
    </div>
    @endif

    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="label">Total Students</div>
            <div class="sub-label">{{ auth()->guard('coordinator')->user()->major ?? 'All Programs' }}</div>
            <div class="number">{{ $totalStudents }}</div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="label">Present Today</div>
            <div class="sub-label">Timed in</div>
            <div class="number">{{ $studentsTimedIn }}</div>
        </div>
        <div class="stat-card danger">
            <div class="stat-icon"><i class="bi bi-x-circle-fill"></i></div>
            <div class="label">Absent Today</div>
            <div class="sub-label">Not timed in</div>
            <div class="number">{{ $studentsNotTimedIn }}</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
            <div class="label">Late Arrivals</div>
            <div class="sub-label">Today</div>
            <div class="number">{{ $lateArrivalsToday ?? 0 }}</div>
        </div>
    </div>

    <div class="actions-grid">
        <div class="action-card">
            <h3><i class="bi bi-person-check"></i> Verify Students</h3>
            <p>Verify students who registered under your program before they can log in.</p>
            <a href="{{ route('coordinator.pending.verification') }}" class="btn-minimal btn-warning">
                <i class="bi bi-person-check"></i>
                {{ ($pendingVerificationCount ?? 0) > 0 ? 'Pending (' . $pendingVerificationCount . ')' : 'Verify Students' }}
            </a>
        </div>
        <div class="action-card">
            <h3><i class="bi bi-clock-history"></i> Attendance Logs</h3>
            <p>View detailed attendance logs for your program.</p>
            <a href="{{ route('coordinator.attendance.logs') }}" class="btn-minimal">
                <i class="bi bi-list-ul"></i> View Logs
            </a>
        </div>
        <div class="action-card">
            <h3><i class="bi bi-patch-check"></i> OJT Completion</h3>
            <p>Confirm completion, set passwords, and download certificates.</p>
            <a href="{{ route('coordinator.ojt.completion') }}" class="btn-minimal btn-success">
                <i class="bi bi-patch-check"></i> OJT Completion
            </a>
        </div>
        <div class="action-card">
            <h3><i class="bi bi-file-earmark-pdf"></i> Generate Report</h3>
            <p>Create and download monthly attendance reports as PDF.</p>
            <a href="{{ route('coordinator.generate.report') }}" class="btn-minimal btn-danger">
                <i class="bi bi-download"></i> Generate Report
            </a>
        </div>
    </div>
@endsection
