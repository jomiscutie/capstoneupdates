@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
    .page-title { font-size: 1.5rem; font-weight: 600; color: var(--dtr-text); margin-bottom: 0.35rem; text-align: center; }
    .page-sub { font-size: 0.9rem; color: var(--dtr-muted); margin: 0 auto 1.5rem; text-align: center; max-width: 760px; }
    .info-alert { background: var(--dtr-surface-soft); border-left: none; border-radius: 6px; padding: 0.9rem 1.1rem; margin-bottom: 1.5rem; font-size: 0.9rem; text-align: center; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.95rem; margin-bottom: 1.5rem; }
    .stat-card {
        background: var(--dtr-card-bg);
        padding: 1rem 1rem 0.9rem;
        border-radius: 10px;
        border: 1px solid var(--dtr-border-soft);
        box-shadow: none;
        min-height: 138px;
    }
    .stat-card .stat-icon { font-size: 1.1rem; margin-bottom: 0.55rem; line-height: 1; }
    .stat-card .stat-label {
        font-size: 0.78rem;
        color: var(--dtr-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
        margin-bottom: 0.2rem;
    }
    .stat-card .sub-label {
        font-size: 0.78rem;
        color: var(--dtr-muted);
        margin-bottom: 0.7rem;
        line-height: 1.25;
    }
    .stat-card .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dtr-text);
        font-variant-numeric: tabular-nums;
        line-height: 1;
    }
    .stat-card.primary .stat-icon { color: var(--dtr-primary); }
    .stat-card.success .stat-icon { color: #059669; }
    .stat-card.danger .stat-icon { color: #dc2626; }
    .stat-card.warning .stat-icon { color: #d97706; }
    .actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .action-card { background: var(--dtr-card-bg); padding: 1.2rem; border-radius: 10px; border: 1px solid var(--dtr-border-soft); box-shadow: var(--dtr-shadow-soft); transition: border-color 0.15s ease; }
    .action-card:hover { border-color: rgba(37,99,235,0.18); }
    .action-card h3 { font-size: 1rem; font-weight: 600; color: var(--dtr-text); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem; }
    .action-card p { font-size: 0.85rem; color: var(--dtr-muted); margin-bottom: 1rem; line-height: 1.45; }
</style>
@endpush

@section('content')
<h1 class="page-title">Admin Dashboard</h1>
<p class="page-sub text-center">System-wide oversight for coordinators, students, attendance, and role separation.</p>

<div class="info-alert">
    <strong>Admin Oversight:</strong> Manage coordinators, student lifecycle, invalidations, and auditability from one panel.
</div>

<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
        <div class="stat-label">Coordinators</div>
        <div class="sub-label">{{ $activeCoordinators }} active</div>
        <div class="stat-value">{{ $totalCoordinators }}</div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon"><i class="bi bi-mortarboard-fill"></i></div>
        <div class="stat-label">Students</div>
        <div class="sub-label">{{ $verifiedStudents }} verified</div>
        <div class="stat-value">{{ $totalStudents }}</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon"><i class="bi bi-person-check-fill"></i></div>
        <div class="stat-label">Pending Verification</div>
        <div class="sub-label">Awaiting coordinator review</div>
        <div class="stat-value">{{ $pendingStudents }}</div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
        <div class="stat-label">Present Today</div>
        <div class="sub-label">Across all programs</div>
        <div class="stat-value">{{ $presentToday }}</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-icon"><i class="bi bi-shield-exclamation"></i></div>
        <div class="stat-label">Pending Invalidations</div>
        <div class="sub-label">Awaiting admin decision</div>
        <div class="stat-value">{{ $pendingInvalidations }}</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon"><i class="bi bi-journal-check"></i></div>
        <div class="stat-label">Pending Manual Requests</div>
        <div class="sub-label">Awaiting admin verification</div>
        <div class="stat-value">{{ $pendingManualRequests }}</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-icon"><i class="bi bi-camera-video-off-fill"></i></div>
        <div class="stat-label">Missing Face Enrollment</div>
        <div class="sub-label">Registered without face data</div>
        <div class="stat-value">{{ $missingFaceEnrollment }}</div>
    </div>
    <div class="stat-card primary">
        <div class="stat-icon"><i class="bi bi-journal-text"></i></div>
        <div class="stat-label">Audit Events Today</div>
        <div class="sub-label">Security and governance logs</div>
        <div class="stat-value">{{ $auditEventsToday }}</div>
    </div>
</div>

<div class="actions-grid">
    <div class="action-card">
        <h3><i class="bi bi-journal-check"></i> Manual Requests</h3>
        <p>Review logbook/power interruption requests from students.</p>
        <a href="{{ route('admin.manual.requests') }}" class="btn-minimal btn-warning">
            <i class="bi bi-journal-check"></i>
            {{ $pendingManualRequests > 0 ? 'Pending ('.$pendingManualRequests.')' : 'Open Manual Requests' }}
        </a>
    </div>
    <div class="action-card">
        <h3><i class="bi bi-shield-exclamation"></i> Invalidations</h3>
        <p>Approve or reject invalidation requests from coordinators.</p>
        <a href="{{ route('admin.invalidations') }}" class="btn-minimal btn-danger">
            <i class="bi bi-shield-exclamation"></i> Review Invalidations
        </a>
    </div>
    <div class="action-card">
        <h3><i class="bi bi-archive"></i> Archived Students</h3>
        <p>Restore archived records or permanently remove when needed.</p>
        <a href="{{ route('admin.students.archived') }}" class="btn-minimal">
            <i class="bi bi-archive"></i> Open Archived Roster
        </a>
    </div>
    <div class="action-card">
        <h3><i class="bi bi-journal-text"></i> Audit Logs</h3>
        <p>Track all sensitive actions with actor/target identifiers.</p>
        <a href="{{ route('admin.audit_logs') }}" class="btn-minimal btn-success">
            <i class="bi bi-journal-text"></i> View Audit Trail
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3">Students By Course</h2>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Total Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($studentsByCourse as $row)
                                <tr>
                                    <td>{{ $row->course ?: 'Unassigned' }}</td>
                                    <td>{{ $row->total }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-muted">No student records available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3">Recent Coordinators</h2>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Program</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCoordinators as $coordinator)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $coordinator->name }}</div>
                                        <div class="text-muted small">{{ $coordinator->email }}</div>
                                    </td>
                                    <td>{{ $coordinator->major ?: 'Not set' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-muted">No coordinator accounts yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
