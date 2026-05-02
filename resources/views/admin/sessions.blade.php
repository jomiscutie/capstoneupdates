@extends('layouts.admin')

@section('title', 'Session Monitor')

@section('content')
<h1 class="page-title">Session Monitor</h1>
<p class="page-sub text-center">Security control panel for active user sessions and forced logout actions.</p>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Coordinators</h2>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Session</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($coordinators as $coordinator)
                            <tr>
                                <td>{{ $coordinator->name }}</td>
                                <td>
                                    @if($coordinator->current_session_id)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Offline</span>
                                    @endif
                                </td>
                                <td>
                                    @if($coordinator->current_session_id)
                                    <form method="POST" action="{{ route('admin.sessions.force_logout') }}">
                                        @csrf
                                        <input type="hidden" name="type" value="coordinator">
                                        <input type="hidden" name="id" value="{{ $coordinator->id }}">
                                        <button class="btn btn-sm btn-outline-danger btn-force-logout">Force logout</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-muted">No coordinators found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Students</h2>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Session</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $student)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $student->name }}</div>
                                    <div class="small text-muted">{{ $student->student_no }}</div>
                                </td>
                                <td>
                                    @if($student->current_session_id)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Offline</span>
                                    @endif
                                </td>
                                <td>
                                    @if($student->current_session_id)
                                    <form method="POST" action="{{ route('admin.sessions.force_logout') }}">
                                        @csrf
                                        <input type="hidden" name="type" value="student">
                                        <input type="hidden" name="id" value="{{ $student->id }}">
                                        <button class="btn btn-sm btn-outline-danger btn-force-logout">Force logout</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-muted">No students found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-force-logout {
        min-height: 28px;
        padding: 0.18rem 0.55rem;
        font-size: 0.73rem;
        font-weight: 500;
        border-radius: 8px;
        line-height: 1.15;
        letter-spacing: 0.01em;
        color: #dc2626 !important;
        border-color: rgba(220, 38, 38, 0.55) !important;
        background: rgba(220, 38, 38, 0.08) !important;
    }
    .btn-force-logout:hover,
    .btn-force-logout:focus {
        color: #ffffff !important;
        border-color: #dc2626 !important;
        background: #dc2626 !important;
    }
</style>
@endpush
