<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daily Time Record - {{ $student->name }} - {{ $month }}</title>
    <style>
        @page {
            size: 4in 8.5in;
            margin: 0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Serif', 'Times New Roman', Times, serif;
            font-size: 7pt;
            color: #000;
            line-height: 1.12;
            padding: 5mm 4.75mm;
        }
        .dtr-title {
            text-align: center;
            font-size: 9.2pt;
            font-weight: bold;
            letter-spacing: 0.08em;
            margin-bottom: 4px;
        }
        .name-block {
            text-align: center;
            margin-bottom: 5px;
        }
        .name-line {
            border-bottom: 2px solid #000;
            min-height: 15px;
            margin: 0 auto 2px;
            max-width: 94%;
            padding: 1px 4px 0;
        }
        .student-name {
            font-size: 10pt;
            font-weight: bold;
            letter-spacing: 0.02em;
            line-height: 1.15;
        }
        .name-hint {
            font-size: 6.2pt;
            color: #222;
        }
        .month-line {
            text-align: center;
            margin-bottom: 5px;
            font-size: 7.5pt;
        }
        .official-row {
            width: 100%;
            margin-bottom: 4px;
            font-size: 6pt;
            line-height: 1.1;
        }
        .official-row table { width: 100%; border-collapse: collapse; }
        .official-row td { vertical-align: bottom; padding: 1px 0; }
        .official-label { width: 40%; }
        .official-blank { border-bottom: 1px solid #000; min-height: 10px; }
        .dtr-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 5.85pt;
            margin-bottom: 0;
        }
        .dtr-table th,
        .dtr-table td {
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            padding: 0;
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
            height: 10px;
            max-height: 10px;
            line-height: 1;
            font-size: 5.7pt;
        }
        .dtr-table tbody tr.dim td {
            color: #999;
        }
        .dtr-table tbody tr.dtr-total-summary td {
            height: auto;
            border-top: 2px solid #000;
            vertical-align: middle;
            padding: 2px 1px;
            background: #f7f7f7;
        }
        .dtr-table tbody tr.dtr-total-summary .undertime-tail-cell {
            font-size: 5.95pt;
        }
        .dtr-table tbody tr.dtr-total-summary .undertime-total-cell {
            font-weight: bold;
            font-size: 6pt;
            padding: 3px 1px;
        }
        /* Total hours rendered (above certification); one line, compact type */
        .hours-rendered-above-cert {
            margin: 0 0 5px;
            padding: 4px 1.25mm 4px 2.75mm;
            text-align: left;
            line-height: 1.25;
        }
        .hours-rendered-above-cert .total-rendered-inner {
            display: inline-block;
            max-width: 100%;
            padding: 3px 5px;
            border: 1px solid #8a8a8a;
            border-radius: 2px;
            background: #fff;
            font-weight: bold;
            font-size: 7.15pt;
            letter-spacing: 0.025em;
            color: #000;
        }
        .hours-rendered-above-cert .hours-numeric {
            font-weight: bold;
            font-variant-numeric: tabular-nums;
        }
        /* Classic DTR separators and certification block */
        .dtr-double-rule {
            border: 0 solid transparent;
            border-top: 3px double #000;
            margin: 5px 0 6px;
            height: 0;
            clear: both;
            font-size: 0;
            line-height: 0;
        }
        .cert-classic {
            font-size: 6.1pt;
            font-style: italic;
            text-align: justify;
            margin: 0 0 5px;
            padding-left: 2.75mm;
            padding-right: 2.75mm;
            line-height: 1.22;
            hyphens: none;
        }
        .cert-classic-lead {
            font-style: italic;
            font-weight: bold;
            font-variant: normal;
            font-size: inherit;
            letter-spacing: 0.02em;
        }
        .sig-dashed {
            border-bottom: 1px dashed #000;
            min-height: 14px;
            margin: 0 0 2px;
            width: 100%;
        }
        .verify-line {
            font-size: 6.2pt;
            font-style: italic;
            margin: 4px 0 4px;
        }
        .sig-dashed-in-charge {
            border-bottom: 1px dashed #000;
            text-align: right;
            padding: 0 0 1px;
            margin: 0;
            width: 100%;
            line-height: 1.05;
            min-height: 15px;
        }
        .sig-dashed-in-charge .in-charge-name-line {
            display: inline-block;
            font-weight: bold;
            font-size: 6.35pt;
            background-color: #fff;
            padding: 1px 0 0 14px;
        }
        .in-charge-role {
            font-size: 6.1pt;
            font-style: italic;
            font-weight: bold;
            text-align: right;
            margin-top: 2px;
        }
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
                <td class="official-label" rowspan="2" style="vertical-align: middle;">Official hours<br>Arrival&nbsp;&amp;&nbsp;Departure</td>
                <td style="width: 36%;">Regular days</td>
                <td class="official-blank" style="width: 24%;">&nbsp;</td>
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
            <tr class="dtr-total-summary">
                <td colspan="5" class="undertime-tail-cell">&nbsp;</td>
                <td class="undertime-total-cell">{{ $undertimeTotalH !== null ? $undertimeTotalH : '' }}</td>
                <td class="undertime-total-cell">{{ $undertimeTotalM !== null ? $undertimeTotalM : '' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="dtr-double-rule" role="presentation"></div>

    <div class="hours-rendered-above-cert" role="note">
        <div class="total-rendered-inner">
            TOTAL&nbsp;<span class="hours-numeric">{{ (int) $totalHours }}h {{ str_pad((string) (int) $totalMinutes, 2, '0', STR_PAD_LEFT) }}m</span>
        </div>
    </div>

    <p class="cert-classic">
        <span class="cert-classic-lead">I CERTIFY</span> on my honor that the above is a true and correct report of the hours of work performed, record of which was made daily at the time of arrival at and departure from office.
    </p>

    <div class="sig-dashed" aria-hidden="true"></div>

    <div class="dtr-double-rule" role="presentation"></div>

    <p class="verify-line">Verified as to the prescribed office hours.</p>

    <div class="sig-dashed-in-charge">
        <span class="in-charge-name-line">{{ $coordinator->name }}</span>
    </div>
    <div class="in-charge-role">In Charge</div>
</body>
</html>
