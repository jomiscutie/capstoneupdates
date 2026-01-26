<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Monthly Attendance Report - {{ $student->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #357ABD;
        }
        
        .header h1 {
            color: #357ABD;
            font-size: 24pt;
            margin-bottom: 10px;
        }
        
        .header h2 {
            color: #666;
            font-size: 16pt;
            font-weight: normal;
        }
        
        .info-section {
            margin-bottom: 25px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .info-label {
            display: table-cell;
            width: 35%;
            font-weight: bold;
            color: #555;
        }
        
        .info-value {
            display: table-cell;
            color: #333;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        
        .stat-box {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
        }
        
        .stat-box:last-child {
            margin-right: 0;
        }
        
        .stat-value {
            font-size: 24pt;
            font-weight: bold;
            color: #357ABD;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 10pt;
            color: #666;
            text-transform: uppercase;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        
        thead {
            background: #357ABD;
            color: white;
        }
        
        th {
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
            border: 1px solid #2c5ca8;
        }
        
        td {
            padding: 10px 8px;
            border: 1px solid #ddd;
            font-size: 9pt;
        }
        
        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        tbody tr:hover {
            background: #e9ecef;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }
        
        .summary-section {
            margin-top: 25px;
            padding: 15px;
            background: #e7f3ff;
            border-left: 4px solid #357ABD;
            border-radius: 5px;
        }
        
        .summary-section h3 {
            color: #357ABD;
            margin-bottom: 10px;
            font-size: 14pt;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>MONTHLY ATTENDANCE REPORT</h1>
        <h2>{{ $month }}</h2>
    </div>
    
    <!-- Student Information -->
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Student Name:</div>
            <div class="info-value">{{ $student->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Student Number:</div>
            <div class="info-value">{{ $student->student_no }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Program/Course:</div>
            <div class="info-value">{{ $student->course }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Email:</div>
            <div class="info-value">{{ $student->email }}</div>
        </div>
        @if($coordinator)
        <div class="info-row">
            <div class="info-label">Prepared By:</div>
            <div class="info-value">{{ $coordinator->name }} ({{ $coordinator->major }})</div>
        </div>
        @endif
    </div>
    
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-value">{{ $presentDays }}</div>
            <div class="stat-label">Present Days</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $absentDays }}</div>
            <div class="stat-label">Absent Days</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $lateArrivals }}</div>
            <div class="stat-label">Late Arrivals</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $totalHours }}h {{ $totalMinutes }}m</div>
            <div class="stat-label">Total Hours</div>
        </div>
    </div>
    
    <!-- Attendance Details Table -->
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Morning Time In</th>
                <th>Afternoon Time In</th>
                <th>Time Out</th>
                <th>Status</th>
                <th>Hours</th>
            </tr>
        </thead>
        <tbody>
            @if($attendances->count() > 0)
                @foreach($attendances as $attendance)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('M d, Y') }}</td>
                        <td>
                            @if($attendance->time_in)
                                @if($attendance->is_late)
                                    <span class="badge badge-warning">{{ $attendance->time_in }}</span>
                                @else
                                    {{ $attendance->time_in }}
                                @endif
                            @else
                                <span style="color: #999;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($attendance->afternoon_time_in)
                                @if($attendance->afternoon_is_late)
                                    <span class="badge badge-warning">{{ $attendance->afternoon_time_in }}</span>
                                @else
                                    {{ $attendance->afternoon_time_in }}
                                @endif
                            @else
                                <span style="color: #999;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($attendance->time_out)
                                {{ $attendance->time_out }}
                            @else
                                <span style="color: #999;">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statuses = [];
                                if ($attendance->time_in) {
                                    if ($attendance->is_late) {
                                        $statuses[] = '<span class="badge badge-warning">Morning Late</span>';
                                    } else {
                                        $statuses[] = '<span class="badge badge-success">Morning On Time</span>';
                                    }
                                }
                                if ($attendance->afternoon_time_in) {
                                    if ($attendance->afternoon_is_late) {
                                        $statuses[] = '<span class="badge badge-warning">Afternoon Late</span>';
                                    } else {
                                        $statuses[] = '<span class="badge badge-success">Afternoon On Time</span>';
                                    }
                                }
                            @endphp
                            @if(count($statuses) > 0)
                                {!! implode(' ', $statuses) !!}
                            @else
                                <span class="badge badge-danger">Absent</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $dayMinutes = 0;
                                if ($attendance->time_in && $attendance->time_out) {
                                    $in = \Carbon\Carbon::parse($attendance->time_in);
                                    $out = \Carbon\Carbon::parse($attendance->time_out);
                                    if ($out->format('H') < 12) {
                                        $dayMinutes += abs($out->diffInMinutes($in));
                                    }
                                }
                                if ($attendance->afternoon_time_in && $attendance->time_out) {
                                    $in = \Carbon\Carbon::parse($attendance->afternoon_time_in);
                                    $out = \Carbon\Carbon::parse($attendance->time_out);
                                    $dayMinutes += abs($out->diffInMinutes($in));
                                }
                                $dayHours = floor($dayMinutes / 60);
                                $dayMins = $dayMinutes % 60;
                            @endphp
                            @if($dayMinutes > 0)
                                {{ $dayHours }}h {{ $dayMins }}m
                            @else
                                <span style="color: #999;">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" class="no-data">No attendance records found for this month.</td>
                </tr>
            @endif
        </tbody>
    </table>
    
    <!-- Summary -->
    <div class="summary-section">
        <h3>Summary</h3>
        <p><strong>Total Working Days:</strong> {{ $totalDays }} days</p>
        <p><strong>Present Days:</strong> {{ $presentDays }} days ({{ number_format(($presentDays / $totalDays) * 100, 1) }}%)</p>
        <p><strong>Absent Days:</strong> {{ $absentDays }} days ({{ number_format(($absentDays / $totalDays) * 100, 1) }}%)</p>
        <p><strong>Late Arrivals:</strong> {{ $lateArrivals }} occurrences</p>
        <p><strong>Total Hours Rendered:</strong> {{ $totalHours }} hours and {{ $totalMinutes }} minutes</p>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p>Generated on {{ $generatedAt }}</p>
        <p>NORSU OJT Digital Time Record System</p>
    </div>
</body>
</html>





