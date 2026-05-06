<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of OJT Completion - {{ $student->name }}</title>
    <style type="text/css">
        /*
         * Letter landscape, single page — compact wrapper; inner table balances main vs footer.
         */
        @page {
            margin: 0;
            padding: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #1a1420;
            line-height: 1.34;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body > table.cert-wrap {
            width: 10.94in;
            margin: 0;
            padding: 0 !important;
            border-collapse: collapse;
            border-spacing: 0;
            page-break-after: avoid;
        }

        body > table.cert-wrap > tbody > tr > td {
            padding: 0;
            vertical-align: top;
        }

        .cert-sheet {
            width: 10.94in;
            height: 8.42in;
            overflow: hidden;
            page-break-inside: avoid;
            page-break-after: avoid;
        }

        .cert-frame {
            width: 10.94in;
            height: 8.42in;
            border: 6px solid #2a163c;
            background: linear-gradient(168deg, #f3f6ff 0%, #ffffff 46%, #f9fafc 100%);
            overflow: hidden;
            position: relative;
            page-break-inside: avoid;
            page-break-after: avoid;
        }

        /* Top-right accent only (modern asymmetry) */
        .cert-accent-tr {
            position: absolute;
            top: 0;
            right: 0;
            border-style: solid;
            border-width: 0 26mm 26mm 0;
            border-color: transparent rgba(36, 18, 52, 0.88) transparent transparent;
            width: 0;
            height: 0;
            z-index: 1;
            pointer-events: none;
        }

        .cert-inner {
            position: relative;
            z-index: 2;
            padding: 0.34in 0.52in 0.26in;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        /*
         * Upper band: tall cell + valign=middle fills the canvas;
         * lower band: footer flush to visual bottom without flex.
         */
        .cert-vertical {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
            margin: 0 auto;
            page-break-inside: avoid;
        }
        .cert-vertical td {
            padding: 0;
            vertical-align: middle;
        }
        .cert-vertical .cert-main-cell {
            vertical-align: middle;
            padding: 0 0 0.1in;
        }
        .cert-vertical .cert-foot-cell {
            vertical-align: bottom;
            padding: 0.14in 0 0;
        }

        .cert-org {
            font-size: 8pt;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #362a44;
            margin-bottom: 2px;
        }
        .cert-org-sub {
            font-size: 8.15pt;
            color: #5a5465;
            margin-bottom: 0.16in;
        }

        .cert-title-script {
            font-family: DejaVu Serif, Georgia, serif;
            font-style: italic;
            font-weight: normal;
            font-size: 22pt;
            color: #100818;
            margin-bottom: 0.12in;
            line-height: 1.04;
        }

        .cert-lead {
            font-size: 8.65pt;
            color: #5c5b64;
            margin-bottom: 0.05in;
        }

        .cert-name {
            font-size: 16.75pt;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #0c0710;
            margin-bottom: 0.125in;
            line-height: 1.08;
            max-width: 96%;
            margin-left: auto;
            margin-right: auto;
            page-break-inside: avoid;
        }

        .cert-body-text {
            font-size: 9.05pt;
            color: #3a3740;
            margin: 0 auto 0.1in;
            max-width: 7.82in;
            line-height: 1.42;
            text-align: center;
        }
        .cert-body-text strong {
            color: #24182c;
            font-weight: 700;
        }

        .cert-meta {
            font-size: 8.2pt;
            color: #4f4958;
            margin: 0 auto;
            max-width: 7.95in;
            line-height: 1.4;
            page-break-inside: avoid;
            text-align: center;
        }
        .cert-gantt {
            width: 100%;
            max-width: 7.95in;
            margin: 0.14in auto 0;
            text-align: left;
            page-break-inside: avoid;
        }
        .cert-gantt-title {
            font-size: 8.25pt;
            font-weight: 700;
            color: #2a163c;
            letter-spacing: 0.02em;
            margin-bottom: 0.045in;
            text-transform: uppercase;
        }
        .cert-gantt-sub {
            font-size: 7.6pt;
            color: #5a5465;
            margin-bottom: 0.06in;
        }
        .cert-gantt-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid rgba(42, 22, 60, 0.12);
            background: #ffffff;
        }
        .cert-gantt-table th,
        .cert-gantt-table td {
            border: 1px solid rgba(42, 22, 60, 0.12);
            padding: 2px 3px;
            font-size: 7.05pt;
            color: #332f3a;
            text-align: center;
        }
        .cert-gantt-task {
            width: 24%;
            text-align: left !important;
            font-weight: 700;
            color: #1f1626 !important;
            background: #faf9fc;
        }
        .cert-gantt-head th {
            font-size: 6.9pt;
            font-weight: 700;
            letter-spacing: 0.01em;
            color: #3d3748;
            background: #f2eff8;
        }
        .gantt-bar {
            background: linear-gradient(90deg, #4f2d6f 0%, #7b4aa9 100%);
            color: #ffffff !important;
            font-weight: 700;
            letter-spacing: 0.01em;
        }
        .gantt-milestone {
            background: #ece8f5;
            font-weight: 700;
            color: #3f2b57 !important;
        }

        .cert-footer-stack {
            text-align: center;
            width: 100%;
        }

        .cert-divider {
            width: 108px;
            height: 1px;
            background: rgba(42, 22, 60, 0.22);
            margin: 0 auto 0.1in;
        }

        .cert-confirm {
            font-size: 8.15pt;
            color: #45404d;
            margin-bottom: 0.1in;
            line-height: 1.32;
            page-break-inside: avoid;
        }

        .cert-signatures {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: avoid;
        }
        .cert-signatures td {
            width: 50%;
            vertical-align: top;
            padding: 0 18px;
        }

        .cert-sig-line {
            border-bottom: 1px solid #141016;
            padding-bottom: 2px;
            margin-bottom: 3px;
            min-height: 17px;
            font-size: 8.15pt;
            color: #18141c;
        }
        .cert-sig-label {
            font-size: 7.9pt;
            color: #524c5c;
            font-weight: 700;
            margin: 0;
            padding: 0;
            letter-spacing: 0.04em;
        }
    </style>
</head>
<body>
<table class="cert-wrap" cellpadding="0" cellspacing="0">
    <tbody>
    <tr>
        <td>
            <div class="cert-sheet">
                <div class="cert-frame">
                    <span class="cert-accent-tr" aria-hidden="true"></span>

                    <div class="cert-inner">
                        <table class="cert-vertical" role="presentation" cellpadding="0" cellspacing="0">
                            <tbody>
                            <tr>
                                <td class="cert-main-cell" align="center" valign="middle" style="height: 6.08in;">
                                    <div>
                                        <p class="cert-org">Negros Oriental State University</p>
                                        <p class="cert-org-sub">On-the-Job Training Program</p>

                                        <p class="cert-title-script">Certificate of Completion</p>

                                        <p class="cert-lead">This is to certify that</p>

                                        <p class="cert-name">{{ $student->name }}</p>

                                        <p class="cert-body-text">
                                            has satisfactorily completed <strong>{{ number_format($termAssignment->required_ojt_hours ?? $student->current_required_hours ?? 120, 0) }}</strong> required hours of On-the-Job Training,
                                            having rendered <strong>{{ number_format($student->renderedHoursForAssignment($termAssignment ?? null), 1) }}</strong> credited hours during the prescribed period.
                                        </p>

                                        <p class="cert-meta">
                                            Student No.: {{ $student->student_no }}
                                            · Program: {{ $termAssignment->course ?? $student->course ?? '—' }}
                                            · Term: {{ $termAssignment->term ?? '—' }}
                                            · Section: {{ $termAssignment->section ?? '—' }}
                                        </p>

                                        <div class="cert-gantt">
                                            <p class="cert-gantt-title">OJT Program Timeline (Gantt Overview)</p>
                                            <p class="cert-gantt-sub">Coverage period: September 3 to May 3 (academic implementation window)</p>
                                            <table class="cert-gantt-table" role="presentation" aria-label="OJT timeline">
                                                <thead class="cert-gantt-head">
                                                <tr>
                                                    <th class="cert-gantt-task">Activity</th>
                                                    <th>Sep</th>
                                                    <th>Oct</th>
                                                    <th>Nov</th>
                                                    <th>Dec</th>
                                                    <th>Jan</th>
                                                    <th>Feb</th>
                                                    <th>Mar</th>
                                                    <th>Apr</th>
                                                    <th>May</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr>
                                                    <td class="cert-gantt-task">Program Orientation &amp; Alignment</td>
                                                    <td class="gantt-milestone">Sep 3</td>
                                                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                                </tr>
                                                <tr>
                                                    <td class="cert-gantt-task">Workplace Deployment &amp; Setup</td>
                                                    <td class="gantt-bar">Active</td>
                                                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                                </tr>
                                                <tr>
                                                    <td class="cert-gantt-task">Technical Immersion &amp; Core Tasks</td>
                                                    <td></td>
                                                    <td class="gantt-bar">Active</td>
                                                    <td class="gantt-bar">Active</td>
                                                    <td class="gantt-bar">Active</td>
                                                    <td class="gantt-bar">Active</td>
                                                    <td></td><td></td><td></td><td></td>
                                                </tr>
                                                <tr>
                                                    <td class="cert-gantt-task">Project Execution &amp; Deliverables</td>
                                                    <td></td><td></td><td></td>
                                                    <td class="gantt-bar">Active</td>
                                                    <td class="gantt-bar">Active</td>
                                                    <td class="gantt-bar">Active</td>
                                                    <td class="gantt-bar">Active</td>
                                                    <td></td><td></td>
                                                </tr>
                                                <tr>
                                                    <td class="cert-gantt-task">Documentation &amp; Completion Reports</td>
                                                    <td></td><td></td><td></td><td></td><td></td><td></td>
                                                    <td class="gantt-bar">Active</td>
                                                    <td class="gantt-bar">Active</td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td class="cert-gantt-task">Final Evaluation, Validation &amp; Closure</td>
                                                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                                    <td class="gantt-bar">Active</td>
                                                    <td class="gantt-milestone">May 3</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="cert-foot-cell" align="center">
                                    <div class="cert-footer-stack">
                                        <div class="cert-divider" aria-hidden="true"></div>

                                        <p class="cert-confirm">
                                            Completion confirmed on {{ $termAssignment?->confirmed_at?->format('F j, Y') ?? '—' }}
                                            @if($termAssignment?->confirmedBy)
                                                · Verified by {{ $termAssignment->confirmedBy->name }}, Coordinator
                                            @endif
                                        </p>

                                        <table class="cert-signatures" role="presentation">
                                            <tbody>
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
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
