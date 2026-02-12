<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Monthly Attendance — {{ $student->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            line-height: 1.5;
            max-width: 210mm;
            margin: 0 auto;
            padding: 18mm 15mm;
        }
        .report-header {
            text-align: center;
            margin-bottom: 22px;
            padding-bottom: 14px;
            border-bottom: 1px solid #333;
        }
        .report-header .logo {
            height: 56px;
            width: auto;
            margin-bottom: 12px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .report-header .title {
            font-size: 14pt;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: 0.02em;
        }
        .report-header .month {
            font-size: 11pt;
            color: #444;
        }
        .info-block {
            margin-bottom: 20px;
        }
        .info-block p {
            margin-bottom: 6px;
            line-height: 1.6;
        }
        .info-block strong {
            font-weight: 600;
            display: inline-block;
            min-width: 100px;
        }
        .stats-block {
            margin-bottom: 22px;
            padding: 12px 0;
            border-top: 1px solid #e0e0e0;
            border-bottom: 1px solid #e0e0e0;
            font-size: 10pt;
        }
        .stats-block span {
            margin-right: 24px;
        }
        .stats-block strong {
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
            font-size: 9pt;
        }
        thead th {
            text-align: left;
            padding: 10px 12px;
            font-weight: 600;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            background: #f5f5f5;
            border-bottom: 2px solid #333;
            border-top: 1px solid #ddd;
        }
        tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #e8e8e8;
            vertical-align: top;
        }
        tbody tr:last-child td {
            border-bottom: 1px solid #ccc;
        }
        .summary-block {
            margin-top: 20px;
            padding: 14px 0;
            border-top: 1px solid #ddd;
        }
        .summary-block p {
            margin-bottom: 8px;
        }
        .summary-block strong {
            font-weight: 600;
        }
        .signature-block {
            margin-top: 32px;
            padding-top: 24px;
        }
        .signature-line {
            width: 200px;
            border-bottom: 1px solid #333;
            margin-bottom: 6px;
            height: 28px;
        }
        .signature-block .name {
            font-size: 10pt;
            font-weight: 600;
        }
        .signature-block .label {
            font-size: 9pt;
            color: #555;
            margin-bottom: 4px;
        }
        .footer-block {
            margin-top: 28px;
            padding-top: 14px;
            border-top: 1px solid #ccc;
            font-size: 9pt;
            color: #555;
            text-align: center;
        }
        .no-data {
            font-style: italic;
            color: #666;
            padding: 16px;
        }
    </style>
</head>
<body>
    <div class="report-header">
        @if(!empty($logoDataUri))
        <img src="{{ $logoDataUri }}" alt="NORSU" class="logo" />
        @endif
        <div class="title">Monthly Attendance Report</div>
        <div class="month">{{ $month }}</div>
    </div>

    <div class="info-block">
        <p><strong>Student:</strong> {{ $student->name }}</p>
        <p><strong>Student No:</strong> {{ $student->student_no }}</p>
        <p><strong>Course:</strong> {{ $student->course ?? '—' }}</p>
        @if($coordinator)
        <p><strong>Prepared by:</strong> {{ $coordinator->name }}</p>
        @endif
    </div>

    <div class="stats-block">
        <span><strong>Present:</strong> {{ $presentDays }} days</span>
        <span><strong>Absent:</strong> {{ $absentDays }} days</span>
        <span><strong>Late:</strong> {{ $lateArrivals }}</span>
        <span><strong>Total hours:</strong> {{ $totalHours }}h {{ $totalMinutes }}m</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time In</th>
                <th>Afternoon In</th>
                <th>Time Out</th>
                <th>Hours</th>
            </tr>
        </thead>
        <tbody>
            @if($attendances->count() > 0)
                @foreach($attendances as $attendance)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('m/d/y') }}</td>
                        <td>{{ $attendance->time_in_12 ? $attendance->time_in_12 . ($attendance->is_late ? ' (Late)' : '') : '—' }}</td>
                        <td>{{ $attendance->afternoon_time_in_12 ? $attendance->afternoon_time_in_12 . ($attendance->afternoon_is_late ? ' (Late)' : '') : '—' }}</td>
                        <td>{{ $attendance->time_out_12 ?? '—' }}</td>
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
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" class="no-data">No attendance records for this month.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="summary-block">
        <p><strong>Total working days:</strong> {{ $totalDays }}</p>
        <p><strong>Present:</strong> {{ $presentDays }} ({{ $totalDays > 0 ? number_format(($presentDays / $totalDays) * 100, 1) : 0 }}%)</p>
        <p><strong>Total hours rendered:</strong> {{ $totalHours }}h {{ $totalMinutes }}m</p>
    </div>

    @if($coordinator)
    <div class="signature-block">
        <div class="label">Prepared by:</div>
        <div class="signature-line"></div>
        <div class="name">{{ $coordinator->name }}</div>
        <div class="label" style="margin-top: 2px; font-size: 8pt;">Coordinator</div>
    </div>
    @endif

    <div class="footer-block">
        Generated {{ $generatedAt }} · NORSU OJT DTR
    </div>
</body>
</html>
