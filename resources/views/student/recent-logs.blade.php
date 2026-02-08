@extends('layouts.student')

@section('title', 'Recent Attendance Logs')

@section('content')
    <a href="{{ route('student.dashboard') }}" class="back-link d-inline-block mb-2">
        <i class="bi bi-arrow-left"></i> Dashboard
    </a>
    <h1 class="page-title">Recent Attendance Logs</h1>
    <p class="page-sub mb-3">Your attendance history</p>

    <div class="card-section">
        @if($logs->isEmpty())
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i>No attendance logs found.
            </div>
        @else
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Hours Rendered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td><i class="bi bi-calendar3 me-2"></i>{{ \Carbon\Carbon::parse($log->date)->format('F d, Y') }}</td>
                                <td>{{ $log->time_in ?? '-' }}</td>
                                <td>{{ $log->time_out ?? '-' }}</td>
                                <td>{{ $log->hours_rendered ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
