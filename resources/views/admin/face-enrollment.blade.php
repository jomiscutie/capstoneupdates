@extends('layouts.admin')

@section('title', 'Face Enrollment Oversight')

@section('content')
<h1 class="page-title">Missing Face Enrollment</h1>
<p class="page-sub text-center">Students registered without face data and may need coordinator follow-up.</p>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2" role="search">
            <div class="col-md-10">
                <label for="faceEnrollSearchQ" class="visually-hidden">Search students</label>
                <input type="text" id="faceEnrollSearchQ" class="form-control" name="q" value="{{ $q }}" placeholder="Search by name, student no, or course" autocomplete="off" aria-label="Search by name, student no, or course">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary btn-search"><i class="bi bi-search" aria-hidden="true"></i> Search</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Student No</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Verification</th>
                        <th>Registered</th>
                        <th>Action</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td>{{ $student->student_no }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->course }}</td>
                        <td>
                            <span class="badge {{ $student->isVerified() ? 'bg-success' : ($student->isPendingVerification() ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                {{ $student->isVerified() ? 'Verified' : ($student->isPendingVerification() ? 'Pending' : 'Rejected') }}
                            </span>
                        </td>
                        <td>{{ $student->created_at?->format('M d, Y') }}</td>
                        <td><span class="text-muted small">Ask student to use <strong>Settings > Enroll / Re-enroll</strong></span></td>
                        <td>
                            @if($student->isPendingVerification())
                                <span class="text-warning small">Pending coordinator verification. Face enrollment is still required.</span>
                            @elseif($student->isVerified())
                                <span class="text-muted small">Verified account but no face data saved. Request immediate re-enrollment.</span>
                            @else
                                <span class="text-muted small">No face enrollment captured yet (possible no-camera registration).</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-muted">No students with missing face enrollment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $students->links() }}</div>
    </div>
</div>
@endsection
