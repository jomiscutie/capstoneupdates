@extends('layouts.coordinator')

@section('title', 'Absent Today')

@push('styles')
<style>
    .page-title { font-size: 1.5rem; font-weight: 600; color: var(--dtr-text); margin-bottom: 0.35rem; text-align: center; }
    .page-sub { font-size: 0.9rem; color: var(--dtr-muted); margin: 0 auto 1.5rem; text-align: center; max-width: 760px; }
    .back-link { color: var(--dtr-primary); text-decoration: none; font-size: 0.9rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.5rem; }
    .back-link:hover { color: var(--dtr-primary-dark); }
    .absent-card {
        background: var(--dtr-card-bg);
        border-radius: var(--dtr-radius);
        border: 1px solid var(--dtr-border-soft);
        box-shadow: var(--dtr-shadow-soft);
        overflow: hidden;
    }
    .absent-card .card-header {
        padding: 1rem 1.25rem;
        background: var(--dtr-surface-soft);
        border-bottom: 1px solid var(--dtr-border-soft);
        font-weight: 600;
        font-size: 1rem;
        color: var(--dtr-text);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .absent-card .card-header .badge { font-size: 0.8rem; }
    .absent-table { width: 100%; font-size: 0.9rem; }
    .absent-table th {
        text-align: left;
        padding: 0.75rem 1.25rem;
        color: var(--dtr-muted);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.04em;
        border-bottom: 1px solid var(--dtr-border-soft);
        background: var(--dtr-surface-soft);
    }
    .absent-table td { padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--dtr-border-soft); }
    .absent-table tbody tr:hover { background: var(--dtr-hover-bg); }
    .absent-table tbody tr:last-child td { border-bottom: none; }
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: var(--dtr-muted);
    }
    .empty-state i { font-size: 3rem; color: var(--dtr-success); margin-bottom: 1rem; }
</style>
@endpush

@section('content')
    <h1 class="page-title">Not yet timed in today</h1>
    <p class="page-sub">Students in your program who have not recorded a time-in for {{ now()->format('F j, Y') }}.</p>

    <div class="absent-card">
        <div class="card-header">
            <i class="bi bi-person-x"></i>
            Absent / Not yet timed in
            @if($absentTodayStudents->isNotEmpty())
                <span class="badge bg-danger ms-2">{{ $absentTodayStudents->count() }}</span>
            @endif
        </div>
        @if($absentTodayStudents->isEmpty())
            <div class="empty-state">
                <i class="bi bi-check-circle-fill d-block"></i>
                <p class="mb-0 fw-medium">All students have timed in today.</p>
                <p class="small mt-1">No one is absent.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="absent-table table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student No</th>
                            <th>Name</th>
                            <th>Course</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($absentTodayStudents as $index => $s)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $s->student_no }}</td>
                                <td>{{ $s->name }}</td>
                                <td>{{ $s->course ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
