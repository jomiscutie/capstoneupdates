<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Certificate of OJT Completion - {{ $student->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            color: #1a1a2e;
            line-height: 1.5;
            padding: 40px 50px;
        }
        .border-frame {
            border: 3px solid #2563eb;
            padding: 35px 45px;
            min-height: 90vh;
            position: relative;
        }
        .inner-gold {
            border: 1px solid #b8860b;
            padding: 30px 40px;
            min-height: 100%;
        }
        .header {
            text-align: center;
            margin-bottom: 28px;
        }
        .header .logo-placeholder {
            width: 80px;
            height: 80px;
            margin: 0 auto 12px;
            border: 2px solid #2563eb;
            border-radius: 50%;
            background: #eff6ff;
            display: table;
        }
        .header .logo-placeholder span {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            font-size: 9pt;
            color: #2563eb;
        }
        .header h1 {
            font-size: 14pt;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 4px;
        }
        .header .sub {
            font-size: 10pt;
            color: #64748b;
        }
        .cert-title {
            text-align: center;
            margin: 25px 0 20px;
        }
        .cert-title h2 {
            font-size: 18pt;
            color: #1e40af;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .cert-title .underline {
            width: 180px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #2563eb, transparent);
            margin: 10px auto 0;
        }
        .body-text {
            text-align: center;
            margin: 22px 0;
            font-size: 12pt;
            line-height: 1.8;
        }
        .body-text .name {
            font-size: 16pt;
            font-weight: bold;
            color: #1e40af;
            margin: 8px 0 12px;
            text-transform: uppercase;
        }
        .body-text .details {
            margin-top: 18px;
            font-size: 11pt;
            color: #475569;
        }
        .stats-row {
            text-align: center;
            margin: 20px 0 25px;
            padding: 15px 25px;
            background: #f0f9ff;
            border-radius: 8px;
            border: 1px solid #bae6fd;
        }
        .stats-row span {
            font-weight: bold;
            color: #0369a1;
        }
        .footer-section {
            margin-top: 35px;
            padding-top: 25px;
            border-top: 1px solid #e2e8f0;
        }
        .signature-block {
            margin-top: 20px;
            text-align: center;
        }
        .signature-line {
            width: 220px;
            border-bottom: 2px solid #1a1a2e;
            margin: 0 auto 6px;
            height: 28px;
        }
        .signature-label {
            font-size: 10pt;
            color: #64748b;
        }
        .date-block {
            text-align: center;
            margin-top: 20px;
            font-size: 10pt;
            color: #64748b;
        }
    </style>
</head>
<body>
<div class="border-frame">
    <div class="inner-gold">
        <div class="header">
            <div class="logo-placeholder"><span>NORSU</span></div>
            <h1>Negros Oriental State University</h1>
            <p class="sub">On-the-Job Training Program</p>
        </div>

        <div class="cert-title">
            <h2>Certificate of Completion</h2>
            <div class="underline"></div>
        </div>

        <p class="body-text">
            This is to certify that
        </p>
        <p class="body-text name">{{ $student->name }}</p>
        <p class="body-text details">
            Student No.: {{ $student->student_no }}<br>
            Program: {{ $termAssignment->course ?? $student->course ?? '-' }}<br>
            Term: {{ $termAssignment->term ?? 'Not set' }}<br>
            Section: {{ $termAssignment->section ?? 'Not set' }}
        </p>

        <div class="stats-row">
            has satisfactorily completed <span>{{ number_format($termAssignment->required_ojt_hours ?? $student->current_required_hours ?? 120, 0) }}</span> hours
            of On-the-Job Training, with a total of <span>{{ number_format($student->renderedHoursForAssignment($termAssignment ?? null), 1) }}</span> hours rendered.
        </div>

        <p class="body-text details">
            Completion confirmed on {{ $termAssignment?->confirmed_at?->format('F d, Y') }}
            @if($termAssignment?->confirmedBy)
                <br>Verified by: {{ $termAssignment->confirmedBy->name }}, Coordinator
            @endif
        </p>

        <div class="footer-section">
            <div class="signature-block">
                <div class="signature-line"></div>
                <p class="signature-label">Authorized Signature</p>
            </div>
            <div class="date-block">
                Date of issue: {{ $issuedAt ?? now('Asia/Manila')->format('F d, Y') }}
            </div>
        </div>
    </div>
</div>
</body>
</html>
