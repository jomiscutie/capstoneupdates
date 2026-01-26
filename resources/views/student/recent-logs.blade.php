    <!DOCTYPE html>
<html>
<head>
    <title>Recent Attendance Logs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">

    <h2>Recent Attendance Logs</h2>
    <a href="{{ route('student.dashboard') }}" class="btn btn-secondary mb-3">Back to Dashboard</a>

    @if($logs->isEmpty())
        <div class="alert alert-info">No attendance logs found.</div>
    @else
        <table class="table table-striped table-bordered">
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
                    <td>{{ \Carbon\Carbon::parse($log->date)->format('F d, Y') }}</td>
                    <td>{{ $log->time_in ?? '-' }}</td>
                    <td>{{ $log->time_out ?? '-' }}</td>
                    <td>{{ $log->hours_rendered ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
</body>
</html>
