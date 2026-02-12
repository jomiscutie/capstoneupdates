<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Login - NORSU OJT DTR</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <style>
        :root {
            --dtr-primary: #2563eb;
            --dtr-primary-dark: #1d4ed8;
            --dtr-success: #059669;
            --dtr-success-dark: #047857;
            --dtr-muted: #64748b;
            --dtr-glass: rgba(255,255,255,0.78);
            --dtr-glass-border: rgba(255,255,255,0.5);
            --dtr-radius: 1rem;
            --dtr-radius-lg: 1.25rem;
            --dtr-radius-xl: 1.5rem;
            --dtr-shadow-md: 0 4px 20px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
            --dtr-font: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --dtr-ease-out: cubic-bezier(0.22, 1, 0.36, 1);
            --dtr-transition: 0.35s var(--dtr-ease-out);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: url('/images/negrosorientalstateuniversity_cover.jpg') center/cover fixed no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            font-family: var(--dtr-font);
            padding: clamp(1.5rem, 4vw, 2rem) 1rem;
            color: #0f172a;
            line-height: 1.6;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse 100% 60% at 50% 0%, rgba(37,99,235,0.08) 0%, transparent 50%),
                linear-gradient(165deg, rgba(255,255,255,0.93) 0%, rgba(248,250,252,0.96) 100%);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            z-index: 0;
        }
        .login-container { position: relative; z-index: 1; width: 100%; max-width: 900px; }
        .header-section {
            text-align: center;
            margin-bottom: clamp(2rem, 5vw, 3rem);
        }
        .header-section h1 {
            font-size: clamp(1.75rem, 4vw, 2.5rem);
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--dtr-primary) 0%, var(--dtr-primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.02em;
        }
        .header-section p { font-size: 1.05rem; color: var(--dtr-muted); }
        .norsu-logo {
            height: 72px;
            width: auto;
            display: block;
            object-fit: contain;
            margin: 0 auto 1rem;
        }
        .login-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.75rem;
        }
        .login-card {
            background: var(--dtr-glass);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: var(--dtr-radius-xl);
            padding: clamp(2rem, 4vw, 2.5rem);
            text-align: center;
            box-shadow: var(--dtr-shadow-md), inset 0 1px 0 rgba(255,255,255,0.9);
            transition: transform var(--dtr-transition), box-shadow var(--dtr-transition);
            border: 1px solid var(--dtr-glass-border);
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
            position: relative;
            overflow: hidden;
        }
        .login-card::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: inherit;
            padding: 1px;
            background: linear-gradient(135deg, transparent, var(--dtr-primary));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity var(--dtr-transition);
            pointer-events: none;
        }
        .login-card.student::before { background: linear-gradient(135deg, transparent, var(--dtr-success)); }
        .login-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 24px 56px -16px rgba(0,0,0,0.2), 0 0 0 1px rgba(255,255,255,0.5);
        }
        .login-card.student:hover {
            box-shadow: 0 24px 56px -16px rgba(5,150,105,0.35), 0 0 0 1px rgba(255,255,255,0.5);
        }
        .login-card.coordinator:hover {
            box-shadow: 0 24px 56px -16px rgba(37,99,235,0.35), 0 0 0 1px rgba(255,255,255,0.5);
        }
        .login-card:hover::before { opacity: 0.7; }
        .icon-wrapper {
            width: 88px;
            height: 88px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.25rem;
            transition: transform var(--dtr-transition);
            position: relative;
        }
        .icon-wrapper::after {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            border: 2px solid transparent;
            transition: border-color var(--dtr-transition), transform var(--dtr-transition);
        }
        .login-card:hover .icon-wrapper::after {
            border-color: rgba(255,255,255,0.5);
            transform: scale(1.05);
        }
        .login-card.student .icon-wrapper {
            background: linear-gradient(135deg, var(--dtr-success) 0%, var(--dtr-success-dark) 100%);
            color: #fff;
            box-shadow: 0 8px 24px rgba(5,150,105,0.4), inset 0 1px 0 rgba(255,255,255,0.3);
        }
        .login-card.coordinator .icon-wrapper {
            background: linear-gradient(135deg, var(--dtr-primary) 0%, var(--dtr-primary-dark) 100%);
            color: #fff;
            box-shadow: 0 8px 24px rgba(37,99,235,0.4), inset 0 1px 0 rgba(255,255,255,0.3);
        }
        .login-card:hover .icon-wrapper {
            transform: scale(1.1);
        }
        .login-card h3 {
            font-size: 1.35rem;
            font-weight: 600;
            margin-bottom: 0.6rem;
            color: #0f172a;
            letter-spacing: -0.01em;
        }
        .login-card p {
            font-size: 0.9rem;
            color: var(--dtr-muted);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        .login-btn {
            display: inline-block;
            padding: 0.75rem 1.75rem;
            border-radius: var(--dtr-radius);
            font-weight: 600;
            font-size: 0.95rem;
            transition: transform var(--dtr-transition), box-shadow var(--dtr-transition);
            border: none;
            text-decoration: none;
        }
        .login-card.student .login-btn {
            background: linear-gradient(135deg, var(--dtr-success), var(--dtr-success-dark));
            color: #fff;
            box-shadow: 0 4px 16px rgba(5,150,105,0.35), inset 0 1px 0 rgba(255,255,255,0.2);
        }
        .login-card.student:hover .login-btn {
            box-shadow: 0 8px 24px -4px rgba(5,150,105,0.5);
        }
        .login-card.coordinator .login-btn {
            background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark));
            color: #fff;
            box-shadow: 0 4px 16px rgba(37,99,235,0.35), inset 0 1px 0 rgba(255,255,255,0.2);
        }
        .login-card.coordinator:hover .login-btn {
            box-shadow: 0 8px 24px -4px rgba(37,99,235,0.5);
        }
        @media (max-width: 768px) {
            .header-section h1 { font-size: 1.75rem; }
            .login-cards { grid-template-columns: 1fr; gap: 1.5rem; }
            .login-card { padding: 2rem; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="header-section">
            <img src="{{ asset('images/norsu-seal.png') }}" alt="NORSU" class="norsu-logo" />
            <h1>Welcome to NORSU OJT DTR</h1>
            <p>Please select your login type to continue</p>
        </div>

        <div class="login-cards">
            <a href="{{ route('student.login') }}" class="login-card student">
                <div class="icon-wrapper">
                    <i class="bi bi-person-badge"></i>
                </div>
                <h3>Student Login</h3>
                <p>Access your attendance records, time in/out, and view your daily logs</p>
                <span class="login-btn">Login as Student</span>
            </a>

            <a href="{{ route('coordinator.login') }}" class="login-card coordinator">
                <div class="icon-wrapper">
                    <i class="bi bi-person-gear"></i>
                </div>
                <h3>Coordinator Login</h3>
                <p>Manage student attendance, view reports, and monitor daily activities</p>
                <span class="login-btn">Login as Coordinator</span>
            </a>
        </div>
    </div>
</body>
</html>
