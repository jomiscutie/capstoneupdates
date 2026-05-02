<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - NORSU OJT DTR</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/hide-native-password-reveal.css') }}">
    <style>
        :root {
            --dtr-primary: #4f46e5;
            --dtr-primary-dark: #4338ca;
            --dtr-muted: #64748b;
            --dtr-radius: 1rem;
            --dtr-font: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: url('/images/negrosorientalstateuniversity_cover.jpg') center/cover fixed no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--dtr-font);
            padding: 1rem;
            color: #0f172a;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: linear-gradient(165deg, rgba(255,255,255,0.94) 0%, rgba(248,250,252,0.96) 100%);
            z-index: 0;
        }
        .card {
            position: relative;
            z-index: 1;
            max-width: 440px;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(20px);
            border-radius: 1.25rem;
            box-shadow: 0 24px 56px -16px rgba(79,70,229,0.2);
            border: 1px solid rgba(255,255,255,0.6);
            padding: 2rem;
        }
        .card h1 { font-size: 1.35rem; font-weight: 700; margin-bottom: 1rem; color: #0f172a; }
        .card p { color: var(--dtr-muted); margin-bottom: 1.5rem; line-height: 1.6; }
        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.65rem 1.25rem;
            background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark));
            color: #fff;
            border: none;
            border-radius: var(--dtr-radius);
            font-weight: 600;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .btn-back:hover { color: #fff; opacity: 0.95; }
    </style>
</head>
<body>
    <div class="card text-center">
        <div class="mb-3">
            <i class="bi bi-key-fill text-primary" style="font-size: 2.5rem;"></i>
        </div>
        <h1>Forgot password?</h1>
        <p class="mb-0">
            Your coordinator can set a new password for you from the OJT Completion page. Please contact your OJT coordinator or the office to have your password reset.
        </p>
        <a href="{{ route('login') }}" class="btn btn-back">
            <i class="bi bi-arrow-left"></i> Back to login
        </a>
    </div>
</body>
</html>
