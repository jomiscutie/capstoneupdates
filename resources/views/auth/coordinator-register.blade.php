<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinator Registration - NORSU OJT DTR</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" />
    <style>
        :root {
            --dtr-primary: #2563eb;
            --dtr-primary-dark: #1d4ed8;
            --dtr-muted: #64748b;
            --dtr-surface: #ffffff;
            --dtr-border: #e2e8f0;
            --dtr-radius: 1rem;
            --dtr-radius-lg: 1.25rem;
            --dtr-shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --dtr-shadow-lg: 0 10px 40px -10px rgba(37,99,235,0.25);
            --dtr-font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --dtr-transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: url('/images/negrosorientalstateuniversity_cover.jpg') center/cover fixed no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--dtr-font);
            position: relative;
            padding: 1rem;
            color: #0f172a;
            line-height: 1.6;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse 100% 70% at 70% 20%, rgba(37,99,235,0.08) 0%, transparent 50%),
                linear-gradient(165deg, rgba(255,255,255,0.94) 0%, rgba(248,250,252,0.96) 100%);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            z-index: 0;
        }
        .register-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 500px;
        }
        .register-card {
            background: rgba(255,255,255,0.82);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 1.5rem;
            box-shadow: 0 24px 56px -16px rgba(37,99,235,0.2), 0 0 0 1px rgba(255,255,255,0.5), inset 0 1px 0 rgba(255,255,255,0.9);
            padding: clamp(2rem, 4vw, 3rem) clamp(1.5rem, 4vw, 2.5rem);
            transition: transform 0.35s cubic-bezier(0.22,1,0.36,1), box-shadow 0.35s ease;
            border: 1px solid rgba(255,255,255,0.6);
        }
        .register-card:hover {
            box-shadow: 0 28px 60px -16px rgba(37,99,235,0.28), 0 0 0 1px rgba(255,255,255,0.6);
            transform: translateY(-4px);
        }
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .register-header .icon-wrapper {
            width: 72px;
            height: 72px;
            margin: 0 auto 1.25rem;
            background: linear-gradient(135deg, var(--dtr-primary) 0%, var(--dtr-primary-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 28px rgba(37,99,235,0.4), inset 0 1px 0 rgba(255,255,255,0.3);
        }
        .register-header .icon-wrapper i { font-size: 1.9rem; color: #fff; }
        .register-header h1 {
            font-size: clamp(1.5rem, 3vw, 1.75rem);
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.35rem;
            letter-spacing: -0.02em;
        }
        .register-header p { color: var(--dtr-muted); font-size: 0.95rem; }
        .form-group { margin-bottom: 1.35rem; position: relative; }
        .form-label {
            font-weight: 600;
            color: #334155;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        .form-label i { margin-right: 0.5rem; color: var(--dtr-primary); }
        .input-wrapper { position: relative; }
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.05rem;
            z-index: 2;
        }
        .form-control, .form-select {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 1px solid var(--dtr-border);
            border-radius: var(--dtr-radius);
            font-size: 0.95rem;
            transition: border-color var(--dtr-transition), box-shadow var(--dtr-transition);
            background: #f8fafc;
        }
        .form-select {
            padding-left: 2.75rem;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--dtr-primary);
            background: var(--dtr-surface);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
            outline: none;
        }
        .form-control::placeholder { color: #94a3b8; }
        .form-text { font-size: 0.8rem; color: var(--dtr-muted); margin-top: 0.5rem; }
        .btn-register {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, var(--dtr-primary) 0%, var(--dtr-primary-dark) 100%);
            border: none;
            border-radius: var(--dtr-radius);
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            transition: transform 0.35s cubic-bezier(0.22,1,0.36,1), box-shadow 0.35s ease;
            box-shadow: 0 4px 18px rgba(37,99,235,0.4), inset 0 1px 0 rgba(255,255,255,0.2);
            margin-top: 0.5rem;
            cursor: pointer;
        }
        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px -4px rgba(37,99,235,0.5), inset 0 1px 0 rgba(255,255,255,0.2);
            color: #fff;
        }
        .btn-register:active { transform: translateY(0); }
        .login-link {
            text-align: center;
            margin-top: 1.75rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--dtr-border);
        }
        .login-link p { color: var(--dtr-muted); font-size: 0.9rem; margin: 0; }
        .login-link a {
            color: var(--dtr-primary);
            font-weight: 600;
            text-decoration: none;
            transition: color var(--dtr-transition);
        }
        .login-link a:hover { color: var(--dtr-primary-dark); text-decoration: underline; }
        .switch-login { margin-top: 1.5rem; text-align: center; }
        .divider {
            position: relative;
            text-align: center;
            margin: 1.5rem 0;
        }
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--dtr-border);
        }
        .divider span {
            position: relative;
            background: var(--dtr-surface);
            padding: 0 1rem;
            color: var(--dtr-muted);
            font-size: 0.85rem;
        }
        .btn-switch {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background: #f8fafc;
            border: 1px solid var(--dtr-border);
            border-radius: var(--dtr-radius);
            color: #334155;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all var(--dtr-transition);
            width: 100%;
            justify-content: center;
        }
        .btn-switch:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #0f172a;
            transform: translateY(-2px);
            box-shadow: var(--dtr-shadow-md);
        }
        .btn-switch i { font-size: 1.1rem; color: var(--dtr-primary); }
        .alert {
            border-radius: var(--dtr-radius);
            font-size: 0.9rem;
            margin-bottom: 1.35rem;
            border: none;
            padding: 1rem 1.25rem;
        }
        .alert-danger { background: #fef2f2; color: #b91c1c; }
        .alert-success { background: #f0fdf4; color: #166534; }
        @media (max-width: 576px) {
            .register-card { padding: 2rem 1.5rem; }
            .register-header h1 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <div class="icon-wrapper">
                    <i class="bi bi-person-gear"></i>
                </div>
                <h1>Coordinator Registration</h1>
                <p>Create your coordinator account</p>
            </div>

            @if(session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('coordinator.register') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-person"></i>Full Name
                    </label>
                    <div class="input-wrapper">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Enter your full name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-envelope"></i>Email Address
                    </label>
                    <div class="input-wrapper">
                        <i class="bi bi-envelope-at input-icon"></i>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Enter your email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-building"></i>College
                    </label>
                    <div class="input-wrapper">
                        <i class="bi bi-building input-icon"></i>
                        <input type="text" name="college" class="form-control" value="{{ old('college') }}" placeholder="Enter your college" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-book"></i>Program/Course
                    </label>
                    <div class="input-wrapper">
                        <i class="bi bi-mortarboard input-icon"></i>
                        <select name="course" class="form-select" required>
                            <option value="">Select Program</option>
                            <option value="GEOLOGY" {{ old('course') == 'GEOLOGY' ? 'selected' : '' }}>GEOLOGY</option>
                            <option value="PSYCHOLOGY" {{ old('course') == 'PSYCHOLOGY' ? 'selected' : '' }}>PSYCHOLOGY</option>
                            <option value="INFORMATION TECHNOLOGY" {{ old('course') == 'INFORMATION TECHNOLOGY' ? 'selected' : '' }}>INFORMATION TECHNOLOGY</option>
                            <option value="COMPUTER SCIENCE" {{ old('course') == 'COMPUTER SCIENCE' ? 'selected' : '' }}>COMPUTER SCIENCE</option>
                            <option value="HISTORY" {{ old('course') == 'HISTORY' ? 'selected' : '' }}>HISTORY</option>
                            <option value="MATHEMATICS" {{ old('course') == 'MATHEMATICS' ? 'selected' : '' }}>MATHEMATICS</option>
                        </select>
                    </div>
                    <small class="form-text">Select the program you coordinate</small>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-lock"></i>Password
                    </label>
                    <div class="input-wrapper">
                        <i class="bi bi-key input-icon"></i>
                        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-lock-fill"></i>Confirm Password
                    </label>
                    <div class="input-wrapper">
                        <i class="bi bi-key-fill input-icon"></i>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-register">
                    <i class="bi bi-person-plus me-2"></i>Register
                </button>
            </form>

            <div class="login-link">
                <p>Already have an account? <a href="{{ route('coordinator.login') }}">Login here</a></p>
            </div>

            <div class="switch-login">
                <div class="divider">
                    <span>OR</span>
                </div>
                <a href="{{ route('student.register') }}" class="btn-switch">
                    <i class="bi bi-person-badge me-2"></i>
                    Register as Student
                </a>
            </div>
        </div>
    </div>
</body>
</html>
