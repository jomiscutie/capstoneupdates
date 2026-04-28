<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daily Time Record - {{ $student->name }} - {{ $month }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Serif', 'Times New Roman', Times, serif;
            font-size: 7.3pt;
            color: #000;
            line-height: 1.15;
            padding: 7mm 8mm;
        }
        .dtr-title {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            letter-spacing: 0.08em;
            margin-bottom: 5px;
        }
        .name-block {
            text-align: center;
            margin-bottom: 6px;
        }
        .name-line {
            border-bottom: 2px solid #000;
            min-height: 18px;
            margin: 0 auto 2px;
            max-width: 90%;
            padding: 2px 6px 1px;
        }
        .student-name {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.02em;
            line-height: 1.2;
        }
        .name-hint {
            font-size: 6.8pt;
            color: #222;
        }
        .month-line {
            text-align: center;
            margin-bottom: 6px;
            font-size: 8pt;
        }
        .official-row {
            width: 100%;
            margin-bottom: 5px;
            font-size: 6.8pt;
        }
        .official-row table { width: 100%; border-collapse: collapse; }
        .official-row td { vertical-align: bottom; padding: 2px 0; }
        .official-label { width: 42%; }
        .official-blank { border-bottom: 1px solid #000; min-height: 12px; }
        .dtr-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 6.3pt;
            margin-bottom: 5px;
        }
        .dtr-table th,
        .dtr-table td {
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            padding: 1px 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: clip;
        }
        .dtr-table thead th {
            font-weight: bold;
            background: #fff;
        }
        .col-day { width: 6%; }
        .col-time { width: 11%; }
        .dtr-table tbody td {
            height: 11px;
            max-height: 11px;
            line-height: 1;
        }
        .dtr-table tbody tr.dim td {
            color: #999;
        }
        .cert {
            font-size: 6.6pt;
            font-style: italic;
            text-align: justify;
            margin: 5px 0 6px;
            line-height: 1.2;
        }
        .sig-block { margin-top: 4px; font-size: 7pt; }
        .sig-line {
            border-bottom: 1px solid #000;
            min-height: 13px;
            margin: 4px 0 2px;
            max-width: 70%;
        }
        .sig-line-wide {
            border-bottom: 1px solid #000;
            min-height: 13px;
            margin: 4px 0 2px;
        }
        .sig-label { font-size: 6.4pt; }
        .sig-right { text-align: right; margin-top: 4px; }
        .footer-note {
            text-align: center;
            font-size: 6pt;
            margin-top: 6px;
        }
        .meta-foot {
            margin-top: 6px;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .meta-student-ref {
            font-size: 6.6pt;
            color: #333;
            margin-bottom: 4px;
        }
        .meta-prepared {
            margin: 4px 0;
            padding: 4px 8px;
            border: 1px solid #000;
            background: #fafafa;
        }
        .meta-prepared-label {
            display: block;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 2px;
            color: #000;
        }
        .meta-prepared-name {
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 0.02em;
            color: #000;
        }
        .meta-generated {
            font-size: 5.8pt;
            color: #444;
            margin-top: 2px;
        }
        .total-label { font-weight: bold; }
    </style>
</head>
<body>

    <div class="dtr-title">DAILY TIME RECORD</div>

    <div class="name-block">
        <div class="name-line"><span class="student-name">{{ $student->name }}</span></div>
        <div class="name-hint">(Name)</div>
    </div>

    <div class="month-line">
        For the month of <strong>{{ $monthName }}</strong>, <strong>{{ $yearFull }}</strong>
    </div>

    <div class="official-row">
        <table>
            <tr>
                <td class="official-label" rowspan="2" style="vertical-align: middle;">Prescribed OJT hours for<br>arrival and departure</td>
                <td style="width: 35%;">Regular days</td>
                <td class="official-blank" style="width: 23%;">&nbsp;</td>
            </tr>
            <tr>
                <td>Saturdays</td>
                <td class="official-blank">&nbsp;</td>
            </tr>
        </table>
    </div>

    <table class="dtr-table">
        <thead>
            <tr>
                <th rowspan="2" class="col-day">Day</th>
                <th colspan="2">A.M.</th>
                <th colspan="2">P.M.</th>
                <th colspan="2">UNDER TIME</th>
            </tr>
            <tr>
                <th class="col-time">Arrival</th>
                <th class="col-time">Departure</th>
                <th class="col-time">Arrival</th>
                <th class="col-time">Departure</th>
                <th class="col-time">Hours</th>
                <th class="col-time">Minutes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dtrRows as $row)
            <tr class="{{ $row['in_month'] ? '' : 'dim' }}">
                <td>{{ $row['day'] }}</td>
                <td>{{ $row['in_month'] ? ($row['am_in'] ?? '') : '' }}</td>
                <td>{{ $row['in_month'] ? ($row['am_out'] ?? '') : '' }}</td>
                <td>{{ $row['in_month'] ? ($row['pm_in'] ?? '') : '' }}</td>
                <td>{{ $row['in_month'] ? ($row['pm_out'] ?? '') : '' }}</td>
                <td>{{ $row['in_month'] && $row['ut_h'] !== null ? $row['ut_h'] : '' }}</td>
                <td>{{ $row['in_month'] && $row['ut_m'] !== null ? $row['ut_m'] : '' }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="5" class="total-label" style="text-align: right; padding-right: 6px;">TOTAL</td>
                <td>{{ $undertimeTotalH !== null ? $undertimeTotalH : '' }}</td>
                <td>{{ $undertimeTotalM !== null ? $undertimeTotalM : '' }}</td>
            </tr>
        </tbody>
    </table>

    <p style="font-size: 5.8pt; margin-bottom: 3px;">
        <strong>Note:</strong> A.M. departure uses lunch break out when recorded. Under Time (Hours/Minutes) shows combined late minutes for that day when recorded by the system.
    </p>

    <p class="cert">
        I CERTIFY on my honor that the above is a true and correct report of my OJT attendance hours, recorded through the NORSU OJT Daily Time Record (DTR) system at the time of my morning and afternoon time-in and time-out.
    </p>

    <div class="sig-block">
        <div class="sig-line-wide"></div>
        <div class="sig-label">(Signature of student)</div>
    </div>

    <p style="margin-top: 6px; font-size: 6.8pt;">Verified as to the prescribed OJT attendance hours.</p>

    <div class="sig-right">
        <div class="sig-line" style="margin-left: auto;"></div>
        <div class="sig-label">OJT Coordinator / In Charge</div>
    </div>

    <div class="footer-note">(Refer to your college OJT program guidelines)</div>

    <div class="meta-foot">
        <div class="meta-student-ref">
            Student No. <strong>{{ $student->student_no }}</strong> &middot; {{ $student->course ?? 'Course N/A' }}
        </div>
        @if($coordinator)
        <div class="meta-prepared">
            <span class="meta-prepared-label">Prepared by</span>
            <span class="meta-prepared-name">{{ $coordinator->name }}</span>
        </div>
        @endif
        <div class="meta-generated">
            Generated {{ $generatedAt }} &middot; NORSU OJT DTR
        </div>
    </div>
</body>
</html>
