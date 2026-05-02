<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Time Logs</title>
    <style>
        @page { margin: 16mm 10mm 14mm 10mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111; font-size: 10px; }
        .top-right { text-align: right; font-size: 10px; margin-bottom: 10px; }
        .title { text-align: center; font-weight: 700; letter-spacing: 0.08em; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #222; padding: 4px 5px; vertical-align: middle; }
        th { font-size: 9px; text-transform: uppercase; }
        .meta th, .meta td { font-size: 9px; }
        .meta th { width: 13%; background: #f3f3f3; text-align: left; }
        .meta td { width: 37%; }
        .logs th { background: #f3f3f3; text-align: center; }
        .logs td { font-size: 9.5px; }
        .center { text-align: center; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <div class="top-right">Page 1 of 1</div>
    <div class="title">TIME LOGS</div>

    <table class="meta" style="margin-bottom: 8px;">
        <tr>
            <th>Employee</th>
            <td>{{ $student->name }}</td>
            <th>Position</th>
            <td>[OJT-STUDENT]</td>
        </tr>
        <tr>
            <th>OFFICE/DEPT.</th>
            <td>{{ $student->assigned_office ?: '[TO BE ASSIGNED]' }}</td>
            <th>Period</th>
            <td>{{ $periodLabel }}</td>
        </tr>
    </table>

    <table class="logs">
        <thead>
            <tr>
                <th style="width: 14%;">Record Id</th>
                <th style="width: 8%;">Day</th>
                <th style="width: 14%;">Date</th>
                <th style="width: 14%;">Time</th>
                <th style="width: 8%;">Mode</th>
                <th style="width: 14%;">Entry Type</th>
                <th style="width: 28%;">Device/Area Reference</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['record_id'] }}</td>
                    <td class="center">{{ $row['day'] }}</td>
                    <td class="center">{{ $row['date'] }}</td>
                    <td class="center">{{ $row['time'] }}</td>
                    <td class="center">{{ $row['mode'] }}</td>
                    <td class="center">{{ $row['entry_type'] }}</td>
                    <td class="center">{{ $row['reference'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">No time logs found for the selected period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
