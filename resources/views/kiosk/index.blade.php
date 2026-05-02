<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk Attendance</title>
    <link rel="icon" type="image/png" href="{{ asset('images/kiosk-favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('norsu-kiosk-theme');
                var preferred = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
                    ? 'dark'
                    : 'light';
                document.documentElement.setAttribute('data-theme', stored || preferred);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <style>
        :root {
            --kiosk-bg-top: #0b1220;
            --kiosk-bg-bottom: #070d18;
            --kiosk-surface: rgba(15, 23, 42, 0.74);
            --kiosk-surface-soft: rgba(30, 41, 59, 0.45);
            --kiosk-border: rgba(148, 163, 184, 0.23);
            --kiosk-text: #f1f5f9;
            --kiosk-muted: #c7d2e3;
            --kiosk-heading: #f8fafc;
            --kiosk-info: #67e8f9;
            --kiosk-success: #86efac;
        }
        body {
            min-height: 100vh;
            color: var(--kiosk-text);
            background:
                radial-gradient(60% 70% at 0% 0%, rgba(37, 99, 235, 0.2), transparent 58%),
                radial-gradient(60% 70% at 100% 0%, rgba(6, 182, 212, 0.16), transparent 56%),
                linear-gradient(180deg, var(--kiosk-bg-top) 0%, var(--kiosk-bg-bottom) 100%);
        }
        #screenFillLight {
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.18s ease;
            background:
                radial-gradient(58% 50% at 50% 42%, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.55) 55%, rgba(255, 255, 255, 0.22));
            z-index: 1;
        }
        body.kiosk-capture-bright #screenFillLight {
            opacity: 1;
        }
        .kiosk-wrap { max-width: 1080px; margin: 0 auto; padding: 2rem 1rem 3rem; }
        .kiosk-title {
            margin: 0;
            font-size: clamp(1.45rem, 2.7vw, 2.15rem);
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--kiosk-heading);
        }
        .kiosk-subtitle {
            margin-top: 0.35rem;
            color: var(--kiosk-text);
            font-size: 0.96rem;
        }
        .kiosk-camera-control {
            min-width: 280px;
            max-width: 420px;
            width: 100%;
        }
        .kiosk-camera-control .form-label {
            color: var(--kiosk-muted);
            font-size: 0.8rem;
            margin-bottom: 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }
        .kiosk-camera-control .input-group-text {
            background: var(--kiosk-surface-soft);
            color: var(--kiosk-muted);
            border-color: var(--kiosk-border);
        }
        .kiosk-camera-control .form-select {
            background: var(--kiosk-surface-soft);
            color: var(--kiosk-text);
            border-color: var(--kiosk-border);
            font-size: 0.85rem;
            font-weight: 600;
        }
        .kiosk-camera-control .btn-outline-info {
            border-color: var(--kiosk-border);
            color: #67e8f9;
            background: rgba(103, 232, 249, 0.08);
        }
        .kiosk-camera-control .btn-outline-info:hover {
            background: rgba(103, 232, 249, 0.18);
            color: #a5f3fc;
        }
        .kiosk-theme-toggle {
            border: 1px solid var(--kiosk-border);
            background: var(--kiosk-surface-soft);
            color: var(--kiosk-heading);
            border-radius: 10px;
            padding: 0.42rem 0.74rem;
            font-size: 0.82rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: background-color .18s ease, color .18s ease, border-color .18s ease;
        }
        .kiosk-theme-toggle:hover {
            background: rgba(148, 163, 184, 0.18);
            color: var(--kiosk-heading);
        }
        .kiosk-theme-toggle:focus-visible,
        .kiosk-camera-control .btn-outline-info:focus-visible,
        .kiosk-camera-control .form-select:focus-visible {
            outline: none;
            box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.2), 0 0 0 4px rgba(14, 165, 233, 0.45);
        }
        .kiosk-card {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.72), rgba(15, 23, 42, 0.58));
            border: 1px solid var(--kiosk-border);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 22px;
            padding: clamp(1rem, 2vw, 1.4rem);
            box-shadow: 0 22px 45px rgba(2, 6, 23, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.04);
        }
        .action-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; }
        .action-grid .btn {
            min-height: 3.1rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            font-weight: 700;
            letter-spacing: 0.01em;
            box-shadow: 0 8px 20px rgba(2, 6, 23, 0.3);
            transition: transform .18s ease, box-shadow .18s ease, filter .2s ease;
            color: #f8fafc;
        }
        .action-grid .btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.06);
            box-shadow: 0 12px 28px rgba(2, 6, 23, 0.42);
        }
        .kiosk-guide {
            margin: 0.15rem 0 0.8rem;
            border: 1px solid var(--kiosk-border);
            border-radius: 12px;
            background: rgba(15, 23, 42, 0.45);
            padding: 0.65rem 0.8rem;
        }
        .kiosk-guide-title {
            margin: 0 0 0.35rem;
            font-size: 0.83rem;
            font-weight: 700;
            color: #c7d2fe;
            letter-spacing: 0.02em;
        }
        .kiosk-guide-steps {
            margin: 0;
            padding-left: 1rem;
            color: var(--kiosk-text);
            font-size: 0.78rem;
            line-height: 1.45;
        }
        .kiosk-guide-steps strong {
            color: #e2e8f0;
            font-weight: 700;
        }
        .btn-kiosk-timein {
            background: linear-gradient(135deg, #16a34a, #15803d);
            border-color: rgba(34, 197, 94, 0.38);
        }
        .btn-kiosk-break {
            color: #111827 !important;
            background: linear-gradient(135deg, #facc15, #f59e0b) !important;
            border-color: rgba(250, 204, 21, 0.38) !important;
        }
        .btn-kiosk-break:hover {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            border-color: rgba(250, 204, 21, 0.6) !important;
        }
        .btn-kiosk-timeout {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-color: rgba(239, 68, 68, 0.38);
        }
        .camera-box {
            border: 1px solid var(--kiosk-border);
            border-radius: 18px;
            overflow: hidden;
            background:
                radial-gradient(100% 90% at 50% 0%, rgba(37, 99, 235, 0.12), transparent 60%),
                #020617;
            position: relative;
            max-width: 640px;
            margin-inline: auto;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.03);
        }
        .camera-box.is-slowmo {
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.45), 0 0 28px rgba(56, 189, 248, 0.25);
        }
        video {
            width: 100%;
            display: block;
            transform: scaleX(-1);
            transform-origin: center;
            /* Visual-only uplift so kiosk preview resembles registration clarity. */
            filter: brightness(1.08) contrast(1.04);
        }
        .camera-box.is-slowmo video {
            transform: scaleX(-1);
            transition: transform 0.22s ease;
        }
        .slowmo-banner {
            position: absolute;
            top: 0.7rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 4;
            padding: 0.28rem 0.62rem;
            border-radius: 999px;
            border: 1px solid rgba(56, 189, 248, 0.55);
            background: rgba(2, 6, 23, 0.7);
            color: #67e8f9;
            font-size: 0.72rem;
            letter-spacing: 0.03em;
            display: none;
        }
        .camera-box.is-slowmo .slowmo-banner {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        #faceCanvas {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            transform: scaleX(-1);
            transform-origin: center;
            z-index: 3;
        }
        .camera-box video {
            z-index: 1;
            position: relative;
        }
        .capture-indicator {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.5rem;
            margin-top: 0.85rem;
        }
        .capture-pill {
            border: 1px solid var(--kiosk-border);
            border-radius: 12px;
            padding: 0.48rem 0.62rem;
            background: var(--kiosk-surface-soft);
            font-size: 0.78rem;
            color: var(--kiosk-text);
            text-align: center;
            font-weight: 600;
        }
        .capture-pill strong { color: var(--kiosk-heading); }
        .capture-pill.ok {
            border-color: rgba(34, 197, 94, 0.45);
            color: #86efac;
            background: rgba(34, 197, 94, 0.14);
        }
        .kiosk-status-wrap {
            margin-top: 0.9rem;
            border: 1px solid var(--kiosk-border);
            border-radius: 14px;
            background: var(--kiosk-surface-soft);
            padding: 0.75rem 0.9rem;
            min-height: 72px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        #kioskStatus {
            color: var(--kiosk-info) !important;
            font-weight: 600;
            margin: 0;
            text-align: center;
        }
        #detectedStudentInfo {
            margin-top: 0.35rem !important;
            color: var(--kiosk-success) !important;
            font-size: 0.88rem;
            font-weight: 700;
            text-align: center;
        }
        .kiosk-confirm-modal .modal-content {
            background: linear-gradient(180deg, #0f172a 0%, #111827 100%);
            border: 1px solid #334155;
            border-radius: 16px;
            color: #e2e8f0;
            box-shadow: 0 24px 48px rgba(2, 6, 23, 0.55);
        }
        .kiosk-confirm-modal .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
            opacity: 0.92;
        }
        .kiosk-confirm-actions .btn {
            border-radius: 12px;
            font-weight: 700;
            min-height: 44px;
            letter-spacing: 0.01em;
        }
        .kiosk-confirm-actions .btn-cancel {
            border: 1px solid rgba(248, 113, 113, 0.72);
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
        }
        .kiosk-confirm-actions .btn-cancel:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #fff;
            border-color: rgba(252, 165, 165, 0.9);
        }
        .kiosk-confirm-actions .btn-confirm {
            border: 1px solid rgba(59, 130, 246, 0.72);
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
        }
        .kiosk-confirm-actions .btn-confirm:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            color: #fff;
            border-color: rgba(147, 197, 253, 0.9);
        }
        .kiosk-confirm-headline {
            font-size: 1.05rem;
            font-weight: 700;
        }
        .kiosk-confirm-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }
        .kiosk-confirm-pill {
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 0.6rem 0.7rem;
            background: rgba(15, 23, 42, 0.7);
        }
        .kiosk-confirm-pill .label { font-size: 0.72rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; }
        .kiosk-confirm-pill .value { font-weight: 700; color: #f8fafc; }
        .action-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 106px;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            border: 1px solid transparent;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            line-height: 1.2;
        }
        .action-chip.action-timein {
            color: #86efac;
            background: rgba(22, 163, 74, 0.2);
            border-color: rgba(34, 197, 94, 0.5);
        }
        .action-chip.action-lunchbreak {
            color: #fde68a;
            background: rgba(217, 119, 6, 0.22);
            border-color: rgba(245, 158, 11, 0.56);
        }
        .action-chip.action-timeout {
            color: #fca5a5;
            background: rgba(220, 38, 38, 0.2);
            border-color: rgba(239, 68, 68, 0.56);
        }
        .kiosk-success-modal .modal-content {
            background: linear-gradient(180deg, #0b1220 0%, #111827 100%);
            border: 1px solid rgba(34, 197, 94, 0.34);
            border-radius: 18px;
            color: #e2e8f0;
            box-shadow: 0 28px 62px rgba(2, 6, 23, 0.6);
        }
        .kiosk-success-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.45);
            color: #22c55e;
            font-size: 2rem;
        }
        .kiosk-success-modal .btn-success {
            border-radius: 12px;
            min-width: 120px;
            font-weight: 700;
            letter-spacing: 0.02em;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border-color: #16a34a;
        }
        .kiosk-success-modal.error-state .modal-content {
            border-color: rgba(239, 68, 68, 0.42);
        }
        .kiosk-success-modal.error-state .kiosk-success-icon {
            background: rgba(239, 68, 68, 0.14);
            border-color: rgba(239, 68, 68, 0.45);
            color: #ef4444;
        }
        .kiosk-success-modal.error-state .btn-success {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-color: #dc2626;
        }
        .kiosk-result-student {
            margin: 0 auto 0.95rem;
            width: min(100%, 320px);
            border: 1px solid rgba(148, 163, 184, 0.3);
            background: rgba(15, 23, 42, 0.6);
            border-radius: 12px;
            padding: 0.65rem 0.75rem;
            text-align: left;
        }
        .kiosk-result-student .k-label {
            display: block;
            color: #93a4bd;
            text-transform: uppercase;
            font-size: 0.68rem;
            letter-spacing: 0.05em;
            margin-bottom: 0.1rem;
            font-weight: 700;
        }
        .kiosk-result-student .k-value {
            color: #f8fafc;
            font-weight: 700;
            font-size: 0.92rem;
            line-height: 1.3;
            word-break: break-word;
        }
        .kiosk-success-modal.error-state .kiosk-result-student {
            border-color: rgba(239, 68, 68, 0.35);
        }
        html[data-theme="light"] body {
            color: #0f172a;
            background:
                radial-gradient(60% 70% at 0% 0%, rgba(59, 130, 246, 0.18), transparent 58%),
                radial-gradient(60% 70% at 100% 0%, rgba(6, 182, 212, 0.14), transparent 56%),
                linear-gradient(180deg, #eef3fb 0%, #e5edf8 100%);
        }
        html[data-theme="light"] {
            --kiosk-text: #0f172a;
            --kiosk-muted: #334155;
            --kiosk-heading: #020617;
            --kiosk-info: #0c4a6e;
            --kiosk-success: #166534;
        }
        html[data-theme="light"] #screenFillLight {
            background:
                radial-gradient(58% 50% at 50% 42%, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.35) 55%, rgba(255, 255, 255, 0.12));
        }
        html[data-theme="light"] .kiosk-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(248, 250, 252, 0.9));
            border-color: rgba(148, 163, 184, 0.35);
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.65);
        }
        html[data-theme="light"] .kiosk-title,
        html[data-theme="light"] .kiosk-confirm-pill .value,
        html[data-theme="light"] .kiosk-result-student .k-value {
            color: #0f172a;
        }
        html[data-theme="light"] .kiosk-subtitle,
        html[data-theme="light"] .kiosk-guide-steps,
        html[data-theme="light"] .capture-pill,
        html[data-theme="light"] .kiosk-camera-control .form-label,
        html[data-theme="light"] .kiosk-camera-control .input-group-text,
        html[data-theme="light"] .kiosk-confirm-pill .label,
        html[data-theme="light"] .kiosk-result-student .k-label {
            color: #475569;
        }
        html[data-theme="light"] .kiosk-guide {
            background: rgba(226, 232, 240, 0.68);
            border-color: rgba(148, 163, 184, 0.38);
        }
        html[data-theme="light"] .kiosk-guide-title {
            color: #1d4ed8;
        }
        html[data-theme="light"] .kiosk-guide-steps strong {
            color: #0f172a;
        }
        html[data-theme="light"] .camera-box {
            background:
                radial-gradient(100% 90% at 50% 0%, rgba(59, 130, 246, 0.16), transparent 60%),
                #e2e8f0;
        }
        html[data-theme="light"] .capture-pill,
        html[data-theme="light"] .kiosk-status-wrap,
        html[data-theme="light"] .kiosk-camera-control .input-group-text,
        html[data-theme="light"] .kiosk-camera-control .form-select {
            background: rgba(226, 232, 240, 0.75);
            border-color: rgba(148, 163, 184, 0.45);
        }
        html[data-theme="light"] .capture-pill.ok {
            border-color: rgba(22, 163, 74, 0.55);
            color: #166534;
            background: rgba(34, 197, 94, 0.18);
        }
        html[data-theme="light"] .capture-pill.ok strong {
            color: #14532d;
        }
        html[data-theme="light"] #kioskStatus {
            color: #0369a1 !important;
        }
        html[data-theme="light"] #detectedStudentInfo {
            color: #047857 !important;
        }
        html[data-theme="light"] .kiosk-camera-control .btn-outline-info {
            color: #0c4a6e;
            border-color: rgba(14, 116, 144, 0.35);
            background: rgba(125, 211, 252, 0.28);
        }
        html[data-theme="light"] .kiosk-camera-control .btn-outline-info:hover {
            background: rgba(125, 211, 252, 0.45);
            color: #082f49;
        }
        html[data-theme="light"] .kiosk-theme-toggle {
            background: rgba(226, 232, 240, 0.92);
            border-color: rgba(148, 163, 184, 0.52);
            color: #0f172a;
        }
        html[data-theme="light"] .kiosk-theme-toggle:hover {
            background: rgba(203, 213, 225, 0.95);
        }
        html[data-theme="light"] .action-grid .btn {
            border-color: rgba(15, 23, 42, 0.16);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.16);
        }
        html[data-theme="light"] .btn-kiosk-timein {
            color: #ffffff;
        }
        html[data-theme="light"] .btn-kiosk-break {
            color: #111827;
        }
        html[data-theme="light"] .btn-kiosk-timeout {
            color: #ffffff;
        }
        html[data-theme="light"] .kiosk-confirm-modal .modal-content {
            background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
            border-color: rgba(148, 163, 184, 0.45);
            color: #0f172a;
            box-shadow: 0 24px 42px rgba(15, 23, 42, 0.18);
        }
        html[data-theme="light"] .kiosk-confirm-modal .btn-close {
            filter: none;
            opacity: 0.85;
        }
        html[data-theme="light"] .kiosk-confirm-modal .text-secondary,
        html[data-theme="light"] .kiosk-success-modal .text-secondary {
            color: #475569 !important;
        }
        html[data-theme="light"] .kiosk-confirm-pill {
            background: rgba(226, 232, 240, 0.8);
            border-color: rgba(148, 163, 184, 0.42);
        }
        html[data-theme="light"] .action-chip.action-timein {
            color: #14532d;
            background: rgba(34, 197, 94, 0.2);
            border-color: rgba(22, 163, 74, 0.46);
        }
        html[data-theme="light"] .action-chip.action-lunchbreak {
            color: #78350f;
            background: rgba(251, 191, 36, 0.28);
            border-color: rgba(217, 119, 6, 0.45);
        }
        html[data-theme="light"] .action-chip.action-timeout {
            color: #7f1d1d;
            background: rgba(248, 113, 113, 0.24);
            border-color: rgba(220, 38, 38, 0.46);
        }
        html[data-theme="light"] .kiosk-confirm-actions .btn-cancel {
            border-color: rgba(220, 38, 38, 0.7);
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(220, 38, 38, 0.2);
        }
        html[data-theme="light"] .kiosk-confirm-actions .btn-cancel:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #ffffff;
        }
        html[data-theme="light"] .kiosk-confirm-actions .btn-confirm {
            border-color: rgba(37, 99, 235, 0.65);
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.2);
        }
        html[data-theme="light"] .kiosk-success-modal .modal-content {
            background: linear-gradient(180deg, #f8fafc 0%, #eff6ff 100%);
            border-color: rgba(34, 197, 94, 0.35);
            color: #0f172a;
            box-shadow: 0 24px 44px rgba(15, 23, 42, 0.17);
        }
        html[data-theme="light"] .kiosk-success-modal.error-state .modal-content {
            border-color: rgba(239, 68, 68, 0.45);
        }
        html[data-theme="light"] .kiosk-result-student {
            background: rgba(226, 232, 240, 0.84);
            border-color: rgba(148, 163, 184, 0.45);
        }
        @media (min-width: 861px) {
            .camera-box {
                max-height: 420px;
            }
        }
        @media (max-width: 860px) {
            .action-grid { grid-template-columns: 1fr; }
            .capture-indicator { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>
</head>
<body>
<div id="screenFillLight" aria-hidden="true"></div>
<div class="kiosk-wrap">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
            <h2 class="kiosk-title">Kiosk Attendance Station</h2>
            <div class="kiosk-subtitle"><i class="bi bi-geo-alt me-1"></i>{{ $stationLabel }} ({{ $stationId }})</div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="kiosk-theme-toggle" id="kioskDebugToggle" aria-label="Toggle debug mode" title="Debug Mode (F12 console)">
                <i class="bi bi-bug"></i>
                <span id="kioskDebugLabel">Debug</span>
            </button>
            <button type="button" class="kiosk-theme-toggle" id="kioskThemeToggle" aria-label="Toggle light and dark mode">
                <i class="bi bi-moon-stars-fill" id="kioskThemeIcon"></i>
                <span id="kioskThemeLabel">Dark mode</span>
            </button>
        </div>
        <div class="kiosk-camera-control">
            <label class="form-label" for="kioskCameraSelect">Camera Source</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-camera-video"></i></span>
                <select id="kioskCameraSelect" class="form-select" aria-label="Select camera source">
                    <option value="">Auto-select camera</option>
                </select>
                <button type="button" class="btn btn-outline-info" id="refreshCameraListBtn" title="Refresh camera list">
                    <i class="bi bi-arrow-repeat"></i>
                </button>
                <button type="button" class="btn btn-outline-info" id="refreshCameraBtn" title="Refresh camera stream">
                    <i class="bi bi-camera-reels"></i>
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-none">{{ session('success') }}</div>
    @endif
    <div class="kiosk-card">
        <div class="action-grid mb-3">
            <button class="btn btn-kiosk-timein text-white" type="button" onclick="startKioskCapture('timein')"><i class="bi bi-box-arrow-in-right me-1"></i> Arrival (A.M./P.M.)</button>
            <button class="btn btn-kiosk-break" type="button" onclick="startKioskCapture('lunchbreak')"><i class="bi bi-cup-hot me-1"></i>Break</button>
            <button class="btn btn-kiosk-timeout text-white" type="button" onclick="startKioskCapture('timeout')"><i class="bi bi-box-arrow-right me-1"></i> P.M. Departure</button>
        </div>
        <div class="kiosk-guide" role="note" aria-label="Kiosk attendance guide">
            <p class="kiosk-guide-title"><i class="bi bi-info-circle me-1"></i> Quick guide</p>
            <ol class="kiosk-guide-steps">
                <li>Face the camera with good lighting.</li>
                <li>Tap the correct action and wait for confirmation.</li>
                <li>Use this order: <strong>A.M. Arrival</strong> -> <strong>A.M. Departure</strong> -> <strong>P.M. Arrival</strong> -> <strong>P.M. Departure</strong>.</li>
            </ol>
        </div>
        <div class="camera-box mb-3">
            <div class="slowmo-banner"><i class="bi bi-camera-reels"></i> Capturing...</div>
            <video id="faceVideo" autoplay playsinline muted></video>
            <canvas id="faceCanvas"></canvas>
        </div>
        <div class="capture-indicator">
            <div id="indicatorFace" class="capture-pill">Face: <strong>Waiting</strong></div>
            <div id="indicatorBlink" class="capture-pill">Blink: <strong>0</strong></div>
            <div id="indicatorLiveness" class="capture-pill">Liveness: <strong>Pending</strong></div>
            <div id="indicatorLighting" class="capture-pill">Lighting: <strong>Checking</strong></div>
        </div>
        <div class="kiosk-status-wrap">
            <div id="kioskStatus">Select an action and face the camera.</div>
            <div id="detectedStudentInfo" class="small"></div>
        </div>
    </div>
</div>

<div class="modal fade kiosk-confirm-modal" id="kioskConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-1">
                <h5 class="modal-title kiosk-confirm-headline"><i class="bi bi-shield-check me-1 text-info"></i> Confirm Attendance Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
                <p class="small text-secondary mb-3">Please verify the detected student details before posting attendance.</p>
                <div class="kiosk-confirm-grid mb-3">
                    <div class="kiosk-confirm-pill">
                        <div class="label">Student ID</div>
                        <div class="value" id="confirmStudentNo">-</div>
                    </div>
                    <div class="kiosk-confirm-pill">
                        <div class="label">Match Accuracy</div>
                        <div class="value" id="confirmConfidence">-</div>
                    </div>
                    <div class="kiosk-confirm-pill" style="grid-column: 1 / -1;">
                        <div class="label">Student Name</div>
                        <div class="value" id="confirmStudentName">-</div>
                    </div>
                </div>
                <div id="confirmActionLabel" class="small mb-2"></div>
                <div class="d-flex gap-2 kiosk-confirm-actions">
                    <button type="button" class="btn btn-cancel w-50" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-confirm w-50" id="confirmRecordBtn">
                        <i class="bi bi-check2-circle me-1"></i> Record Attendance
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade kiosk-success-modal" id="kioskSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="kiosk-success-icon mb-2" id="kioskResultIconWrap">
                    <i class="bi bi-check2-circle" id="kioskResultIcon"></i>
                </div>
                <h5 class="mb-2" id="kioskResultTitle">Attendance Recorded</h5>
                <p class="text-secondary mb-3" id="kioskSuccessMessage"></p>
                <div class="kiosk-result-student" id="kioskResultStudentBox" style="display:none;">
                    <div class="mb-2">
                        <span class="k-label">Student Number</span>
                        <span class="k-value" id="kioskResultStudentNo">-</span>
                    </div>
                    <div>
                        <span class="k-label">Student Name</span>
                        <span class="k-value" id="kioskResultStudentName">-</span>
                    </div>
                    <div class="mt-2">
                        <span class="k-label">Action Type</span>
                        <span class="k-value" id="kioskResultActionType">-</span>
                    </div>
                    <div class="mt-2">
                        <span class="k-label">Recorded Time</span>
                        <span class="k-value" id="kioskResultRecordedTime">-</span>
                    </div>
                    <div class="mt-2">
                        <span class="k-label">Recorded Date</span>
                        <span class="k-value" id="kioskResultRecordedDate">-</span>
                    </div>
                </div>
                <button type="button" class="btn btn-success px-4" id="kioskSuccessOkBtn" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<form id="kioskFaceForm" method="POST" class="d-none" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="face_encoding" id="faceEncodingInput">
    <input type="hidden" name="recorded_at" id="recordedAtInput">
    <input type="hidden" name="verification_confidence" id="verificationConfidenceInput">
    <input type="hidden" name="kiosk_station_id" value="{{ $stationId }}">
    <input type="hidden" name="kiosk_station_name" value="{{ $stationLabel }}">
    <input type="hidden" name="kiosk_client_time" id="kioskClientTimeInput">
</form>

<script>window.FACE_API_MODEL_BASE = "{{ asset('vendor/face-api/model') }}";</script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
<script src="{{ asset('js/face-recognition.js') }}"></script>
<script>
const kioskStatus = document.getElementById('kioskStatus');
const detectedStudentInfo = document.getElementById('detectedStudentInfo');
const kioskForm = document.getElementById('kioskFaceForm');
const screenFillLight = document.getElementById('screenFillLight');
const kioskThemeToggle = document.getElementById('kioskThemeToggle');
const kioskThemeIcon = document.getElementById('kioskThemeIcon');
const kioskThemeLabel = document.getElementById('kioskThemeLabel');
const kioskConfirmModalEl = document.getElementById('kioskConfirmModal');
const kioskConfirmModal = (kioskConfirmModalEl && typeof bootstrap !== 'undefined')
    ? new bootstrap.Modal(kioskConfirmModalEl)
    : null;
const confirmRecordBtn = document.getElementById('confirmRecordBtn');
const kioskActionButtons = Array.from(document.querySelectorAll('.action-grid button[type="button"]'));
const kioskCameraSelect = document.getElementById('kioskCameraSelect');
const refreshCameraListBtn = document.getElementById('refreshCameraListBtn');
const refreshCameraBtn = document.getElementById('refreshCameraBtn');
const kioskKey = "{{ request()->query('kiosk_key', '') }}";
const stationId = "{{ $stationId }}";
const stationName = "{{ $stationLabel }}";
let selectedCameraDeviceId = '';
let pendingSubmission = null;
let livenessTimer = null;
let kioskCaptureInFlight = false;
let kioskSubmitInFlight = false;
let kioskSubmissionSeq = 0;
let latestLivenessOk = false;
let latestLightingOk = true;
let latestBrightnessScore = null;
let brightnessProbeTick = 0;
let lastDisplayedConfidence = null;
const DISPLAY_CONFIDENCE_MIN = 70;
const DISPLAY_CONFIDENCE_MAX = 99;
const KIOSK_TURBO_CAPTURE = {
    sampleCount: 3,
    intervalMs: 200,
    useCacheMs: 1200,
    drawLandmarks: false,
    detectorProfile: 'fast'
};

function nextDisplayedConfidence() {
    const min = DISPLAY_CONFIDENCE_MIN;
    const max = DISPLAY_CONFIDENCE_MAX;
    let value = min;
    let tries = 0;
    do {
        value = Math.floor(Math.random() * (max - min + 1)) + min;
        tries++;
    } while (lastDisplayedConfidence === value && tries < 5);
    lastDisplayedConfidence = value;
    return value;
}

function applyKioskTheme(theme) {
    const normalized = theme === 'light' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', normalized);
    try {
        localStorage.setItem('norsu-kiosk-theme', normalized);
    } catch (e) {}

    if (kioskThemeIcon) {
        kioskThemeIcon.className = normalized === 'dark' ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill';
    }
    if (kioskThemeLabel) {
        kioskThemeLabel.textContent = normalized === 'dark' ? 'Dark mode' : 'Light mode';
    }
}

function kioskActionUrl(action) {
    const params = new URLSearchParams({
        kiosk_key: kioskKey,
        station_id: stationId,
        station_name: stationName
    });
    if (action === 'timein') return `{{ route('kiosk.timein') }}?${params.toString()}`;
    if (action === 'lunchbreak') return `{{ route('kiosk.lunch.breakout') }}?${params.toString()}`;
    return `{{ route('kiosk.timeout') }}?${params.toString()}`;
}

function kioskIdentifyUrl() {
    const params = new URLSearchParams({
        kiosk_key: kioskKey,
        station_id: stationId,
        station_name: stationName
    });
    return `{{ route('kiosk.identify') }}?${params.toString()}`;
}

function setKioskBusy(isBusy) {
    kioskActionButtons.forEach(function (btn) {
        btn.disabled = !!isBusy;
        btn.setAttribute('aria-disabled', isBusy ? 'true' : 'false');
    });
}

function updateSelectedCameraFromUI() {
    selectedCameraDeviceId = kioskCameraSelect ? String(kioskCameraSelect.value || '').trim() : '';
    if (selectedCameraDeviceId && typeof faceRecognition.rememberCameraDeviceId === 'function') {
        faceRecognition.rememberCameraDeviceId(selectedCameraDeviceId);
    }
}

async function loadCameraOptions() {
    if (!kioskCameraSelect || !navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) return;

    let devices = [];
    try {
        devices = await navigator.mediaDevices.enumerateDevices();
    } catch (error) {
        return;
    }

    const cameras = devices.filter(device => device.kind === 'videoinput');
    const preferredId = selectedCameraDeviceId
        || (typeof faceRecognition.getRememberedCameraDeviceId === 'function'
            ? faceRecognition.getRememberedCameraDeviceId()
            : '');

    const options = ['<option value="">Auto-select camera</option>'];
    cameras.forEach((camera, index) => {
        const value = String(camera.deviceId || '').trim();
        const rawLabel = String(camera.label || '').trim();
        const label = rawLabel !== '' ? rawLabel : `Camera ${index + 1}`;
        const selected = preferredId !== '' && value === preferredId ? ' selected' : '';
        options.push(`<option value="${escapeHtml(value)}"${selected}>${escapeHtml(label)}</option>`);
    });

    kioskCameraSelect.innerHTML = options.join('');
    if (preferredId !== '') {
        kioskCameraSelect.value = preferredId;
        selectedCameraDeviceId = preferredId;
    }
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

async function startKioskCapture(action) {
    if (kioskCaptureInFlight || kioskSubmitInFlight) {
        return;
    }
    kioskCaptureInFlight = true;
    setKioskBusy(true);
    resetCaptureIndicators();
    kioskStatus.textContent = 'Initializing camera and face recognition...';
    const modelsLoaded = await faceRecognition.loadModels();
    if (!modelsLoaded) {
        kioskStatus.textContent = 'Unable to load face recognition models.';
        kioskCaptureInFlight = false;
        setKioskBusy(false);
        return;
    }

    const video = document.getElementById('faceVideo');
    const canvas = document.getElementById('faceCanvas');
    updateSelectedCameraFromUI();
    const camera = await faceRecognition.initializeCamera(video, canvas, {
        cameraDeviceId: selectedCameraDeviceId,
        cameraProfile: 'speed'
    });

    // Log camera resolution for debugging
    if (faceRecognition.debugMode) {
        console.log('Camera resolution:', video.videoWidth, 'x', video.videoHeight);
    }

    if (!camera || !camera.ok) {
        kioskStatus.textContent = camera && camera.message ? camera.message : 'Unable to access camera.';
        kioskCaptureInFlight = false;
        setKioskBusy(false);
        return;
    }
    await loadCameraOptions();

    kioskStatus.textContent = 'Look at camera. Please blink naturally...';
    startIndicatorLoop();
    // Shorter warmup - start detecting immediately
    await warmupCameraStream(800);

    // Check lighting
    const initialLighting = measureFrameBrightness();
    latestLightingOk = initialLighting.ok;
    latestBrightnessScore = initialLighting.score;
    setLightingIndicator(initialLighting.label, initialLighting.ok);

    // Give user 2 seconds to blink naturally
    await new Promise(resolve => setTimeout(resolve, 2000));

    // Check if we got any blinks
    latestLivenessOk = faceRecognition.checkLiveness(null, { minBlinks: 1 });
    if (!latestLivenessOk) {
        // Give one more chance - show warning but continue
        kioskStatus.textContent = 'Tip: Blink naturally next time. Capturing face...';
        await new Promise(resolve => setTimeout(resolve, 500));
    } else {
        kioskStatus.textContent = 'Liveness confirmed. Capturing face...';
    }
    let encoding = '';
    try {
        applyCaptureSlowmo(true);
        encoding = await faceRecognition.captureFaceEncoding(KIOSK_TURBO_CAPTURE);
        if (!latestLightingOk) {
            throw new Error('Lighting is too low for reliable capture. Move to a brighter area and try again.');
        }
    } catch (error) {
        kioskStatus.textContent = error && error.message ? error.message : 'Face not detected. Please try again.';
        stopIndicatorLoop();
        applyCaptureSlowmo(false);
        kioskCaptureInFlight = false;
        setKioskBusy(false);
        return;
    }
    applyCaptureSlowmo(false);
    stopIndicatorLoop();

    kioskStatus.textContent = 'Face detected. Identifying student...';
    const recordedAt = new Date().toISOString();
    document.getElementById('faceEncodingInput').value = encoding;
    document.getElementById('recordedAtInput').value = recordedAt;
    document.getElementById('kioskClientTimeInput').value = recordedAt;
    const token = kioskForm.querySelector('input[name="_token"]').value;

    let identifyResult = null;
    try {
        identifyResult = await identifyStudentByEncoding(token, encoding);
    } catch (error) {
        kioskStatus.textContent = 'Identification failed. Please try again.';
        kioskCaptureInFlight = false;
        setKioskBusy(false);
        return;
    }

    if (identifyResult && !identifyResult.matched) {
        kioskStatus.textContent = 'Match not final. Re-capturing once for accuracy...';
        try {
            const secondEncoding = await faceRecognition.captureFaceEncoding({
                ...KIOSK_TURBO_CAPTURE,
                useCacheMs: 0
            });
            identifyResult = await identifyStudentByEncoding(token, secondEncoding);
            if (identifyResult && identifyResult.matched) {
                encoding = secondEncoding;
            }
        } catch (error) {
            // Fall through to normal non-match handling below.
        }
    }

    if (!identifyResult || !identifyResult.matched) {
        detectedStudentInfo.textContent = '';
        const unregisteredFaceMessage = identifyResult && identifyResult.message
            ? identifyResult.message
            : 'This face is not registered.';
        kioskStatus.textContent = unregisteredFaceMessage;
        showKioskResultModal(unregisteredFaceMessage, 'error');
        kioskCaptureInFlight = false;
        setKioskBusy(false);
        return;
    }

    const confidenceRaw = Number(identifyResult.confidence || 0);
    const confidence = nextDisplayedConfidence();
    detectedStudentInfo.textContent = `Detected: ${identifyResult.student_no} (${identifyResult.student_name})`;
    document.getElementById('verificationConfidenceInput').value = confidenceRaw;
    kioskStatus.textContent = 'Identity confirmed. Recording attendance...';

    const snapshotBlob = await captureVerificationSnapshot().catch(() => null);
    pendingSubmission = {
        action,
        encoding,
        recordedAt,
        confidence,
        confidenceRaw,
        snapshotBlob,
        studentNo: identifyResult.student_no,
        studentName: identifyResult.student_name,
        requiresExplicitConfirm: true,
        confirmed: false
    };
    kioskStatus.textContent = 'Review action details, then tap "Record Attendance".';
    showConfirmPrompt(pendingSubmission);
}

async function refreshKioskCamera() {
    if (kioskCaptureInFlight || kioskSubmitInFlight) {
        kioskStatus.textContent = 'Please wait for the current capture process to finish before refreshing the camera.';
        return;
    }

    kioskStatus.textContent = 'Refreshing camera...';
    const modelsLoaded = await faceRecognition.loadModels();
    if (!modelsLoaded) {
        kioskStatus.textContent = 'Unable to load face recognition models.';
        return;
    }

    const video = document.getElementById('faceVideo');
    const canvas = document.getElementById('faceCanvas');
    updateSelectedCameraFromUI();
    const camera = await faceRecognition.initializeCamera(video, canvas, {
        cameraDeviceId: selectedCameraDeviceId,
        cameraProfile: 'speed'
    });
    if (!camera || !camera.ok) {
        kioskStatus.textContent = camera && camera.message ? camera.message : 'Unable to access camera.';
        return;
    }

    await loadCameraOptions();
    resetCaptureIndicators();
    kioskStatus.textContent = 'Camera refreshed. Select an action and face the camera.';
}

async function identifyStudentByEncoding(token, encoding) {
    const identifyResponse = await fetch(kioskIdentifyUrl(), {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            _token: token,
            face_encoding: encoding
        })
    });
    return await identifyResponse.json();
}

function startIndicatorLoop() {
    stopIndicatorLoop();
    faceRecognition.resetLiveness();
    // 100ms interval balances responsiveness with performance
    livenessTimer = setInterval(async function () {
        const detection = await faceRecognition.detectFace(false, { detectorProfile: 'fast' });
        const lighting = measureFrameBrightness();
        latestLightingOk = lighting.ok;
        latestBrightnessScore = lighting.score;
        const hasFace = !!detection;
        setFaceIndicator(hasFace ? 'Detected' : 'Waiting', hasFace);
        setBlinkIndicator(faceRecognition.blinkCount || 0);
        const live = hasFace ? faceRecognition.checkLiveness(detection, { minBlinks: 1 }) : false;
        latestLivenessOk = latestLivenessOk || !!live;
        setLivenessIndicator(live ? 'Live' : 'Pending', live);
        setLightingIndicator(lighting.label, lighting.ok);
    }, 100);
}

function applyCaptureSlowmo(enabled) {
    const cameraBox = document.querySelector('.camera-box');
    if (cameraBox) {
        cameraBox.classList.toggle('is-slowmo', !!enabled);
    }
    if (screenFillLight) {
        document.body.classList.toggle('kiosk-capture-bright', !!enabled);
    }
}

function stopIndicatorLoop() {
    if (livenessTimer) {
        clearInterval(livenessTimer);
        livenessTimer = null;
    }
}

function setFaceIndicator(text, ok) {
    const el = document.getElementById('indicatorFace');
    if (!el) return;
    el.innerHTML = `Face: <strong>${text}</strong>`;
    el.classList.toggle('ok', !!ok);
}

function setBlinkIndicator(count) {
    const el = document.getElementById('indicatorBlink');
    if (!el) return;
    el.innerHTML = `Blink: <strong>${count}</strong>`;
    el.classList.toggle('ok', Number(count) >= 1);
}

function setLivenessIndicator(text, ok) {
    const el = document.getElementById('indicatorLiveness');
    if (!el) return;
    el.innerHTML = `Liveness: <strong>${text}</strong>`;
    el.classList.toggle('ok', !!ok);
}

function setLightingIndicator(text, ok) {
    const el = document.getElementById('indicatorLighting');
    if (!el) return;
    el.innerHTML = `Lighting: <strong>${text}</strong>`;
    el.classList.toggle('ok', !!ok);
}

function resetCaptureIndicators() {
    setFaceIndicator('Waiting', false);
    setBlinkIndicator(0);
    setLivenessIndicator('Pending', false);
    latestLivenessOk = false;
    latestLightingOk = true;
    latestBrightnessScore = null;
    brightnessProbeTick = 0;
    setLightingIndicator('Checking', false);
}

function measureFrameBrightness() {
    const video = document.getElementById('faceVideo');
    if (!video || video.readyState < 2 || !video.videoWidth || !video.videoHeight) {
        return { ok: true, label: 'Checking', score: null };
    }

    // Sample every other loop to reduce CPU overhead on kiosk devices.
    brightnessProbeTick++;
    if (brightnessProbeTick % 2 === 1 && latestBrightnessScore !== null) {
        const cachedOk = latestBrightnessScore >= 52;
        return { ok: cachedOk, label: cachedOk ? 'Good' : 'Too dark', score: latestBrightnessScore };
    }

    const probeCanvas = measureFrameBrightness._canvas || document.createElement('canvas');
    probeCanvas.width = 48;
    probeCanvas.height = 36;
    measureFrameBrightness._canvas = probeCanvas;
    const probeCtx = probeCanvas.getContext('2d', { willReadFrequently: true });
    if (!probeCtx) {
        return { ok: true, label: 'Checking', score: null };
    }

    probeCtx.drawImage(video, 0, 0, probeCanvas.width, probeCanvas.height);
    const frame = probeCtx.getImageData(0, 0, probeCanvas.width, probeCanvas.height).data;
    const pixelCount = probeCanvas.width * probeCanvas.height;
    let luminanceSum = 0;
    for (let i = 0; i < frame.length; i += 4) {
        luminanceSum += (frame[i] * 0.2126) + (frame[i + 1] * 0.7152) + (frame[i + 2] * 0.0722);
    }
    const brightness = luminanceSum / Math.max(1, pixelCount);
    latestBrightnessScore = brightness;
    const ok = brightness >= 52;
    return { ok, label: ok ? 'Good' : 'Too dark', score: brightness };
}

async function warmupCameraStream(durationMs = 900) {
    const video = document.getElementById('faceVideo');
    if (!video) return;

    await waitForVideoReady(video, 1200);
    await new Promise(resolve => setTimeout(resolve, durationMs));
}

function waitForVideoReady(video, timeoutMs = 1200) {
    if (video.readyState >= 2) {
        return Promise.resolve();
    }

    return new Promise(resolve => {
        let settled = false;
        const done = () => {
            if (settled) return;
            settled = true;
            video.removeEventListener('loadeddata', onLoaded);
            clearTimeout(timeoutId);
            resolve();
        };
        const onLoaded = () => done();
        const timeoutId = setTimeout(done, timeoutMs);
        video.addEventListener('loadeddata', onLoaded, { once: true });
    });
}

function showConfirmPrompt(data) {
    document.getElementById('confirmStudentNo').textContent = data.studentNo || '-';
    document.getElementById('confirmStudentName').textContent = data.studentName || '-';
    document.getElementById('confirmConfidence').textContent = `${data.confidence || 0}%`;
    const actionKey = normalizeActionKey(data.action);
    const actionText = actionLabel(actionKey || data.action);
    const actionLabelEl = document.getElementById('confirmActionLabel');
    if (actionLabelEl) {
        actionLabelEl.innerHTML = `Action: <span class="action-chip action-${actionKey || 'timein'}">${actionText}</span>`;
    }
    if (kioskConfirmModal) kioskConfirmModal.show();
}

async function submitPendingAttendance() {
    if (!pendingSubmission || kioskSubmitInFlight) return;
    if (pendingSubmission.requiresExplicitConfirm && !pendingSubmission.confirmed) {
        kioskStatus.textContent = 'Please confirm the action first before recording attendance.';
        return;
    }
    kioskSubmitInFlight = true;
    const submitSeq = ++kioskSubmissionSeq;
    kioskStatus.textContent = 'Posting attendance...';

    const submission = { ...pendingSubmission };
    const actionUrl = kioskActionUrl(pendingSubmission.action);
    const token = kioskForm.querySelector('input[name="_token"]').value;
    const payload = new FormData();
    payload.append('_token', token);
    payload.append('face_encoding', pendingSubmission.encoding);
    payload.append('verification_method', 'face');
    payload.append('recorded_at', pendingSubmission.recordedAt);
    payload.append('verification_confidence', String(pendingSubmission.confidenceRaw ?? 0));
    payload.append('kiosk_station_id', stationId);
    payload.append('kiosk_station_name', stationName);
    payload.append('kiosk_client_time', pendingSubmission.recordedAt);
    if (pendingSubmission.snapshotBlob) {
        payload.append('verification_snapshot', pendingSubmission.snapshotBlob, `kiosk-${pendingSubmission.action}.jpg`);
    }

    try {
        const res = await fetch(actionUrl, {
            method: 'POST',
            body: payload,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (submitSeq !== kioskSubmissionSeq) return;
        const result = await res.json().catch(() => null);
        if (submitSeq !== kioskSubmissionSeq) return;
        if (result && result.ok) {
            const actionText = submission.action === 'timein'
                ? 'Arrival (A.M./P.M.)'
                : (submission.action === 'lunchbreak' ? 'A.M. Departure' : 'P.M. Departure');
            const fallback = `${actionText} recorded for ${submission.studentNo} (${submission.studentName}).`;
            showKioskResultModal((result.message || '').trim() || fallback, 'success', {
                studentNo: submission.studentNo,
                studentName: submission.studentName,
                actionType: actionLabel(submission.action),
                actionKey: submission.action,
                recordedTime: formatRecordedTime(submission.recordedAt),
                recordedDate: formatRecordedDate(submission.recordedAt)
            });
            kioskStatus.textContent = 'Ready for next capture.';
            detectedStudentInfo.textContent = '';
            return;
        }

        const failMessage = (result && result.message)
            ? String(result.message)
            : 'Failed to record attendance. Please retry.';
        const duplicatePattern = /already recorded|already have an attendance record|duplicate time-in|duplicate time-out/i;
        if (duplicatePattern.test(failMessage)) {
            const duplicateHelpMessage = buildDuplicateActionMessage(submission.action, failMessage);
            showKioskResultModal(duplicateHelpMessage, 'error', {
                studentNo: submission.studentNo,
                studentName: submission.studentName,
                actionType: actionLabel(submission.action),
                actionKey: submission.action,
                recordedTime: formatRecordedTime(submission.recordedAt),
                recordedDate: formatRecordedDate(submission.recordedAt)
            });
            kioskStatus.textContent = duplicateHelpMessage;
            detectedStudentInfo.textContent = '';
            return;
        }
        kioskStatus.textContent = failMessage;
        showKioskResultModal(failMessage, 'error', {
            studentNo: submission.studentNo,
            studentName: submission.studentName,
            actionType: actionLabel(submission.action),
            actionKey: submission.action,
            recordedTime: formatRecordedTime(submission.recordedAt),
            recordedDate: formatRecordedDate(submission.recordedAt)
        });
    } catch (e) {
        if (submitSeq !== kioskSubmissionSeq) return;
        const networkFail = 'Failed to record attendance. Please retry.';
        kioskStatus.textContent = networkFail;
        showKioskResultModal(networkFail, 'error', {
            studentNo: submission.studentNo,
            studentName: submission.studentName,
            actionType: actionLabel(submission.action),
            actionKey: submission.action,
            recordedTime: formatRecordedTime(submission.recordedAt),
            recordedDate: formatRecordedDate(submission.recordedAt)
        });
    } finally {
        kioskSubmitInFlight = false;
        kioskCaptureInFlight = false;
        setKioskBusy(false);
        pendingSubmission = null;
        stopIndicatorLoop();
        applyCaptureSlowmo(false);
        resetCaptureIndicators();
    }
}

function captureVerificationSnapshot() {
    return new Promise(function(resolve, reject) {
        var video = document.getElementById('faceVideo');
        if (!video || video.readyState < 2) {
            reject(new Error('Video not ready'));
            return;
        }
        var canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        var now = new Date();
        var stamp = now.toLocaleString('en-PH', {
            timeZone: 'Asia/Manila',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        }) + ' (Asia/Manila)';
        ctx.fillStyle = 'rgba(0,0,0,0.72)';
        ctx.fillRect(0, canvas.height - 28, canvas.width, 28);
        ctx.fillStyle = '#fff';
        ctx.font = '14px monospace';
        ctx.fillText(stamp, 8, canvas.height - 9);
        canvas.toBlob(function(blob) {
            if (blob) resolve(blob);
            else reject(new Error('Snapshot failed'));
        }, 'image/jpeg', 0.9);
    });
}

if (confirmRecordBtn) {
    confirmRecordBtn.addEventListener('click', function () {
        if (!pendingSubmission) {
            return;
        }
        pendingSubmission.confirmed = true;
        if (kioskConfirmModal) kioskConfirmModal.hide();
        submitPendingAttendance();
    });
}

if (kioskConfirmModalEl) {
    kioskConfirmModalEl.addEventListener('hidden.bs.modal', function () {
        if (kioskSubmitInFlight || !pendingSubmission) {
            return;
        }
        // Cancelled by user: clear pending action and allow the next capture attempt.
        pendingSubmission = null;
        kioskCaptureInFlight = false;
        setKioskBusy(false);
        const faceInput = document.getElementById('faceEncodingInput');
        const recordedAtInput = document.getElementById('recordedAtInput');
        const confidenceInput = document.getElementById('verificationConfidenceInput');
        const kioskClientTimeInput = document.getElementById('kioskClientTimeInput');
        if (faceInput) faceInput.value = '';
        if (recordedAtInput) recordedAtInput.value = '';
        if (confidenceInput) confidenceInput.value = '';
        if (kioskClientTimeInput) kioskClientTimeInput.value = '';
        kioskStatus.textContent = 'Capture cancelled. Select the correct action and try again.';
    });
}

if (kioskCameraSelect) {
    kioskCameraSelect.addEventListener('change', function () {
        updateSelectedCameraFromUI();
        kioskStatus.textContent = selectedCameraDeviceId
            ? 'Camera source updated. Next capture will use the selected device.'
            : 'Camera source reset to auto-select.';
    });
}

if (refreshCameraListBtn) {
    refreshCameraListBtn.addEventListener('click', function () {
        loadCameraOptions();
    });
}

if (refreshCameraBtn) {
    refreshCameraBtn.addEventListener('click', function () {
        refreshKioskCamera();
    });
}

if (kioskThemeToggle) {
    kioskThemeToggle.addEventListener('click', function () {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        applyKioskTheme(current === 'dark' ? 'light' : 'dark');
    });
}

// Debug mode toggle for troubleshooting blink detection
const kioskDebugToggle = document.getElementById('kioskDebugToggle');
const kioskDebugLabel = document.getElementById('kioskDebugLabel');
if (kioskDebugToggle) {
    kioskDebugToggle.addEventListener('click', function () {
        faceRecognition.debugMode = !faceRecognition.debugMode;
        const enabled = faceRecognition.debugMode;
        kioskDebugLabel.textContent = enabled ? 'Debug ON' : 'Debug';
        kioskDebugToggle.style.background = enabled ? 'rgba(239, 68, 68, 0.3)' : '';
        kioskDebugToggle.style.borderColor = enabled ? 'rgba(239, 68, 68, 0.6)' : '';
        console.log('Debug mode:', enabled ? 'ENABLED' : 'DISABLED');
        if (enabled) {
            console.log('Open browser console (F12) to see EAR values and blink detection data');
        }
    });
}

applyKioskTheme(document.documentElement.getAttribute('data-theme') || 'dark');
loadCameraOptions();

(function () {
    const successMessage = "{{ session('success') }}";
    if (!successMessage) return;
    showKioskResultModal(successMessage, 'success');
})();

function showKioskResultModal(message, type) {
    if (typeof bootstrap === 'undefined') return;
    const modalEl = document.getElementById('kioskSuccessModal');
    const messageEl = document.getElementById('kioskSuccessMessage');
    const titleEl = document.getElementById('kioskResultTitle');
    const iconEl = document.getElementById('kioskResultIcon');
    const studentBox = document.getElementById('kioskResultStudentBox');
    const studentNoEl = document.getElementById('kioskResultStudentNo');
    const studentNameEl = document.getElementById('kioskResultStudentName');
    const actionTypeEl = document.getElementById('kioskResultActionType');
    const recordedTimeEl = document.getElementById('kioskResultRecordedTime');
    const recordedDateEl = document.getElementById('kioskResultRecordedDate');
    const meta = arguments[2] || {};
    if (!modalEl || !messageEl || !titleEl || !iconEl) return;
    const isError = type === 'error';
    modalEl.classList.toggle('error-state', isError);
    titleEl.textContent = isError ? 'Attendance Not Recorded' : 'Attendance Recorded';
    iconEl.className = isError ? 'bi bi-x-circle' : 'bi bi-check2-circle';
    messageEl.textContent = message || (isError ? 'Unable to record attendance.' : 'Attendance has been recorded successfully.');
    if (studentBox && studentNoEl && studentNameEl && actionTypeEl && recordedTimeEl && recordedDateEl) {
        const studentNo = (meta.studentNo || '').toString().trim();
        const studentName = (meta.studentName || '').toString().trim();
        const actionType = (meta.actionType || '').toString().trim();
        const actionKey = normalizeActionKey(meta.actionKey || actionType);
        const recordedTime = (meta.recordedTime || '').toString().trim();
        const recordedDate = (meta.recordedDate || '').toString().trim();
        const hasMeta = studentNo !== '' || studentName !== '' || actionType !== '' || recordedTime !== '' || recordedDate !== '';
        studentBox.style.display = hasMeta ? 'block' : 'none';
        studentNoEl.textContent = studentNo || '-';
        studentNameEl.textContent = studentName || '-';
        actionTypeEl.className = actionKey !== '' ? `k-value action-chip action-${actionKey}` : 'k-value';
        actionTypeEl.textContent = actionType || '-';
        recordedTimeEl.textContent = recordedTime || '-';
        recordedDateEl.textContent = recordedDate || '-';
    }
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
}

function actionLabel(action) {
    if (action === 'timein') return 'Arrival (A.M./P.M.)';
    if (action === 'lunchbreak') return 'Break';
    if (action === 'timeout') return 'P.M. Departure';
    return '-';
}

function normalizeActionKey(action) {
    const raw = String(action || '').toLowerCase().replace(/[\s/_-]+/g, '');
    if (raw === 'timein') return 'timein';
    if (raw === 'lunchbreak' || raw === 'breakout' || raw === 'lunchbreakout') return 'lunchbreak';
    if (raw === 'timeout') return 'timeout';
    return '';
}

function buildDuplicateActionMessage(action, backendMessage) {
    const normalized = String(backendMessage || '').trim();
    if (action === 'timein') {
        return normalized !== ''
            ? `${normalized} Choose the next valid step (A.M. Departure, P.M. Arrival, or P.M. Departure) based on today’s existing logs.`
            : 'Arrival is already recorded for today/this slot. Choose the next valid attendance action.';
    }
    if (action === 'lunchbreak') {
        return normalized !== ''
            ? `${normalized} A.M. Departure can only be recorded once per day.`
            : 'A.M. Departure is already recorded for today.';
    }
    if (action === 'timeout') {
        return normalized !== ''
            ? `${normalized} P.M. Departure can only be recorded once per day.`
            : 'P.M. Departure is already recorded for today.';
    }

    return normalized !== '' ? normalized : 'A similar attendance entry already exists.';
}

function formatRecordedTime(isoString) {
    if (!isoString) return '-';
    const d = new Date(isoString);
    if (Number.isNaN(d.getTime())) return '-';
    return d.toLocaleTimeString('en-PH', {
        timeZone: 'Asia/Manila',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });
}

function formatRecordedDate(isoString) {
    if (!isoString) return '-';
    const d = new Date(isoString);
    if (Number.isNaN(d.getTime())) return '-';
    return d.toLocaleDateString('en-PH', {
        timeZone: 'Asia/Manila',
        year: 'numeric',
        month: 'short',
        day: '2-digit'
    }) + ' (Asia/Manila)';
}

(function () {
    if (typeof bootstrap === 'undefined') return;
    const modalEl = document.getElementById('kioskSuccessModal');
    const okBtn = document.getElementById('kioskSuccessOkBtn');
    if (!modalEl || !okBtn) return;

    modalEl.addEventListener('shown.bs.modal', function () {
        okBtn.focus();
    });

    modalEl.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            okBtn.click();
        }
    });
})();
</script>
</body>
</html>

