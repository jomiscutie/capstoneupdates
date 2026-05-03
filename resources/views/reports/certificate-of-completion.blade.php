<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of OJT Completion - {{ $student->name }}</title>
    <style>
        /* US Letter (“short bond”): landscape = 11 in × 8.5 in */
        @page {
            margin: 0;
            size: 11in 8.5in;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            width: 11in;
            height: 8.5in;
            max-height: 8.5in;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #1b1522;
            line-height: 1.45;
            overflow: hidden;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .cert-sheet {
            position: relative;
            width: 11in;
            height: 8.5in;
            max-height: 8.5in;
            page-break-inside: avoid;
        }

        .cert-frame {
            position: relative;
            width: 11in;
            height: 8.5in;
            max-height: 8.5in;
            border: 6px solid #38245a;
            background: linear-gradient(
                168deg,
                rgba(239, 244, 255, 0.75) 0%,
                #ffffff 45%,
                rgba(237, 242, 252, 0.55) 100%
            );
            overflow: hidden;
            page-break-inside: avoid;
        }

        .cert-accent-tr {
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 32mm 32mm 0;
            border-color: transparent rgba(40, 22, 58, 0.88) transparent transparent;
            z-index: 1;
            pointer-events: none;
        }
        .cert-accent-bl {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 28mm 28mm 0 0;
            border-color: rgba(45, 24, 64, 0.85) transparent transparent transparent;
            z-index: 1;
            pointer-events: none;
        }

        /* Flex column fills page; spacer pushes signatures toward bottom edge */
        .cert-inner {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            width: 100%;
            height: 100%;
            min-height: 100%;
            padding: 0.55in 0.72in 0.48in;
            text-align: center;
        }

        .cert-middle {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            min-height: 0;
        }

        .cert-org {
            font-size: 8.5pt;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #3f3350;
            margin-bottom: 3px;
        }
        .cert-org-sub {
            font-size: 8.75pt;
            color: #5c5470;
            margin-bottom: 10mm;
        }

        .cert-title-script {
            font-family: DejaVu Serif, Georgia, Times, serif;
            font-style: italic;
            font-weight: 400;
            font-size: 26pt;
            color: #150e1c;
            line-height: 1.06;
            margin-bottom: 6mm;
        }

        .cert-lead {
            font-size: 9.75pt;
            color: #5e5d66;
            margin-bottom: 4mm;
        }

        .cert-name {
            font-size: 19pt;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #0f0a14;
            margin-bottom: 5mm;
            line-height: 1.12;
            max-width: 95%;
        }

        .cert-body {
            font-size: 10.25pt;
            color: #3a3640;
            text-align: center;
            line-height: 1.55;
            max-width: 680px;
            margin: 0 auto;
        }
        .cert-body strong {
            color: #24182c;
            font-weight: 700;
        }

        .cert-meta {
            font-size: 9pt;
            color: #56525e;
            margin-top: 4mm;
            line-height: 1.45;
        }

        .cert-footer {
            flex: 0 0 auto;
            width: 100%;
            max-width: 640px;
            margin-top: 4mm;
        }

        .cert-divider {
            width: 120px;
            height: 1px;
            background: rgba(56, 36, 90, 0.25);
            margin: 0 auto 4mm;
        }

        .cert-confirm {
            font-size: 9pt;
            color: #4d4755;
            margin-bottom: 5mm;
            line-height: 1.38;
        }

        .cert-signatures {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            border-collapse: separate;
            border-spacing: 40px 0;
        }
        .cert-signatures td {
            width: 50%;
            vertical-align: bottom;
            text-align: center;
            padding: 0;
        }
        .cert-sig-line {
            border-bottom: 1px solid #17141c;
            min-height: 22px;
            margin-bottom: 4px;
            font-size: 9pt;
            color: #1a1720;
            text-align: center;
            padding-bottom: 2px;
        }
        .cert-sig-label {
            font-size: 8.75pt;
            color: #524c5c;
            font-weight: 600;
            letter-spacing: 0.03em;
        }
    </style>
</head>
<body>
<div class="cert-sheet">
    <div class="cert-frame">
        <span class="cert-accent-tr" aria-hidden="true"></span>
        <span class="cert-accent-bl" aria-hidden="true"></span>

        <div class="cert-inner">
            <div class="cert-middle">
                <p class="cert-org">Negros Oriental State University</p>
                <p class="cert-org-sub">On-the-Job Training Program</p>

                <p class="cert-title-script">Certificate of Completion</p>

                <p class="cert-lead">This is to certify that</p>

                <p class="cert-name">{{ $student->name }}</p>

                <div class="cert-body">
                    <p>
                        has satisfactorily completed <strong>{{ number_format($termAssignment->required_ojt_hours ?? $student->current_required_hours ?? 120, 0) }}</strong> required hours of On-the-Job Training
                        ({{ number_format($student->renderedHoursForAssignment($termAssignment ?? null), 1) }} hours rendered).
                    </p>
                    <p class="cert-meta">
                        No. {{ $student->student_no }} · {{ $termAssignment->course ?? $student->course ?? '—' }}
                        · Term {{ $termAssignment->term ?? '—' }} · Sec. {{ $termAssignment->section ?? '—' }}
                    </p>
                </div>
            </div>

            <footer class="cert-footer">
                <div class="cert-divider" aria-hidden="true"></div>

                <p class="cert-confirm">
                    Confirmed {{ $termAssignment?->confirmed_at?->format('M j, Y') ?? '—' }}
                    @if($termAssignment?->confirmedBy)
                        · {{ $termAssignment->confirmedBy->name }}, Coordinator
                    @endif
                </p>

                <table class="cert-signatures" role="presentation">
                    <tr>
                        <td>
                            <div class="cert-sig-line">{{ $issuedAt ?? now('Asia/Manila')->format('F j, Y') }}</div>
                            <p class="cert-sig-label">Date</p>
                        </td>
                        <td>
                            <div class="cert-sig-line">&nbsp;</div>
                            <p class="cert-sig-label">Signature</p>
                        </td>
                    </tr>
                </table>
            </footer>
        </div>
    </div>
</div>
</body>
</html>
