<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinator Access - NORSU OJT DTR</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <style>
        :root {
            --dtr-primary: #2563eb;
            --dtr-primary-dark: #1d4ed8;
            --dtr-muted: #64748b;
            --dtr-border: #e2e8f0;
            --dtr-radius: 1rem;
            --dtr-font: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            font-family: var(--dtr-font);
            background: linear-gradient(160deg, #eff6ff 0%, #f8fafc 100%);
            color: #0f172a;
        }
        .access-card {
            width: 100%;
            max-width: 460px;
            background: rgba(255,255,255,0.92);
            border: 1px solid rgba(148,163,184,0.22);
            border-radius: 22px;
            padding: 2rem 1.75rem;
            box-shadow: 0 24px 60px -30px rgba(37,99,235,0.25);
            text-align: center;
        }
        .icon-wrap {
            width: 72px;
            height: 72px;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark));
            color: #fff;
            font-size: 1.8rem;
        }
        h1 {
            margin: 0 0 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
        }
        p {
            margin: 0 0 1.25rem;
            color: var(--dtr-muted);
            line-height: 1.6;
        }
        .info-box {
            margin-bottom: 1.25rem;
            padding: 0.95rem 1rem;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1e3a8a;
            border-radius: 14px;
            font-size: 0.95rem;
        }
        .actions {
            display: grid;
            gap: 0.75rem;
        }
        .btn-main,
        .btn-alt {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
            border-radius: 14px;
            padding: 0.8rem 1rem;
            font-weight: 600;
        }
        .btn-main {
            background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark));
            color: #fff;
        }
        .btn-alt {
            border: 1px solid var(--dtr-border);
            background: #fff;
            color: #0f172a;
        }
    </style>
</head>
<body>
    <div class="access-card">
        <div class="icon-wrap">
            <i class="bi bi-person-gear"></i>
        </div>
        <h1>Coordinator Access</h1>
        <p>Coordinator accounts are no longer self-registered. They are created and managed by the admin.</p>

        <div class="info-box">
            If you need a coordinator account, please contact the system admin and ask them to create your access first.
        </div>

        <div class="actions">
            <a href="{{ route('login') }}" class="btn-main">
                <i class="bi bi-box-arrow-in-right"></i>
                Go to Login
            </a>
            <a href="{{ route('student.register') }}" class="btn-alt">
                <i class="bi bi-mortarboard"></i>
                Student Registration
            </a>
        </div>
    </div>
</body>
</html>
