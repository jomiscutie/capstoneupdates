<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Student Login - NORSU OJT DTR</title>
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" />
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" />
  <style>
    :root {
      --dtr-primary: #4f46e5;
      --dtr-primary-dark: #4338ca;
      --dtr-muted: #64748b;
      --dtr-surface: #ffffff;
      --dtr-border: #e2e8f0;
      --dtr-radius: 1rem;
      --dtr-radius-lg: 1.25rem;
      --dtr-shadow-md: 0 4px 12px rgba(0,0,0,0.08);
      --dtr-shadow-lg: 0 10px 40px -10px rgba(79,70,229,0.25);
      --dtr-font: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
      background: radial-gradient(ellipse 100% 70% at 30% 20%, rgba(79,70,229,0.08) 0%, transparent 50%),
        linear-gradient(165deg, rgba(255,255,255,0.94) 0%, rgba(248,250,252,0.96) 100%);
      backdrop-filter: blur(3px);
      -webkit-backdrop-filter: blur(3px);
      z-index: 0;
    }
    .login-container {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 450px;
    }
    .login-card {
      background: rgba(255,255,255,0.82);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border-radius: 1.5rem;
      box-shadow: 0 24px 56px -16px rgba(79,70,229,0.2), 0 0 0 1px rgba(255,255,255,0.5), inset 0 1px 0 rgba(255,255,255,0.9);
      padding: clamp(2rem, 4vw, 3rem) clamp(1.5rem, 4vw, 2.5rem);
      transition: transform 0.35s cubic-bezier(0.22,1,0.36,1), box-shadow 0.35s ease;
      border: 1px solid rgba(255,255,255,0.6);
    }
    .login-card:hover {
      box-shadow: 0 28px 60px -16px rgba(79,70,229,0.28), 0 0 0 1px rgba(255,255,255,0.6);
      transform: translateY(-4px);
    }
    .login-header {
      text-align: center;
      margin-bottom: 2rem;
    }
    .login-header .icon-wrapper {
      width: 72px;
      height: 72px;
      margin: 0 auto 1.25rem;
      background: linear-gradient(135deg, var(--dtr-primary) 0%, var(--dtr-primary-dark) 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 28px rgba(79,70,229,0.4), inset 0 1px 0 rgba(255,255,255,0.3);
    }
    .login-header .icon-wrapper i { font-size: 1.9rem; color: #fff; }
    .login-header h1 {
      font-size: clamp(1.5rem, 3vw, 1.75rem);
      font-weight: 700;
      color: #0f172a;
      margin-bottom: 0.35rem;
      letter-spacing: -0.02em;
    }
    .login-header p { color: var(--dtr-muted); font-size: 0.95rem; }
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
      box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
      outline: none;
    }
    .form-control::placeholder { color: #94a3b8; }
    .form-text { font-size: 0.8rem; color: var(--dtr-muted); margin-top: 0.5rem; }
    .btn-login {
      width: 100%;
      padding: 0.875rem;
      background: linear-gradient(135deg, var(--dtr-primary) 0%, var(--dtr-primary-dark) 100%);
      border: none;
      border-radius: var(--dtr-radius);
      color: #fff;
      font-weight: 600;
      font-size: 1rem;
      transition: transform 0.35s cubic-bezier(0.22,1,0.36,1), box-shadow 0.35s ease;
      box-shadow: 0 4px 18px rgba(79,70,229,0.4), inset 0 1px 0 rgba(255,255,255,0.2);
      margin-top: 0.5rem;
      cursor: pointer;
    }
    .btn-login:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 28px -4px rgba(79,70,229,0.5), inset 0 1px 0 rgba(255,255,255,0.2);
      color: #fff;
    }
    .btn-login:active { transform: translateY(0); }
    .register-link {
      text-align: center;
      margin-top: 1.75rem;
      padding-top: 1.5rem;
      border-top: 1px solid var(--dtr-border);
    }
    .register-link p { color: var(--dtr-muted); font-size: 0.9rem; margin: 0; }
    .register-link a {
      color: var(--dtr-primary);
      font-weight: 600;
      text-decoration: none;
      transition: color var(--dtr-transition);
    }
    .register-link a:hover { color: var(--dtr-primary-dark); text-decoration: underline; }
    .alert {
      border-radius: var(--dtr-radius);
      font-size: 0.9rem;
      margin-bottom: 1.35rem;
      border: none;
      padding: 1rem 1.25rem;
    }
    .alert-danger { background: #fef2f2; color: #b91c1c; }
    .alert-success { background: #f0fdf4; color: #166534; }
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
    @media (max-width: 576px) {
      .login-card { padding: 2rem 1.5rem; }
      .login-header h1 { font-size: 1.5rem; }
    }
    .input-wrapper.has-password-toggle .form-control { padding-right: 2.75rem; }
    .password-toggle-btn {
      position: absolute;
      right: 0.5rem;
      top: 50%;
      transform: translateY(-50%);
      width: 2rem;
      height: 2rem;
      padding: 0;
      border: none;
      background: none;
      color: #94a3b8;
      cursor: pointer;
      border-radius: 6px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: color 0.15s ease, background 0.15s ease;
    }
    .password-toggle-btn:hover { color: var(--dtr-primary); background: rgba(79,70,229,0.08); }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="icon-wrapper">
          <i class="bi bi-person-badge"></i>
        </div>
        <h1>Student Login</h1>
        <p>Welcome back! Please login to continue</p>
      </div>

      @if(session('success'))
      <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
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

      <form action="{{ route('student.login.submit') }}" method="POST">
        @csrf
        <div class="form-group">
          <label class="form-label">
            <i class="bi bi-person"></i>Student Number
          </label>
          <div class="input-wrapper">
            <i class="bi bi-card-text input-icon"></i>
            <input type="text" name="student_no" class="form-control" value="{{ old('student_no') }}" placeholder="Enter your student number" required />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="bi bi-lock"></i>Password
          </label>
          <div class="input-wrapper has-password-toggle">
            <i class="bi bi-key input-icon"></i>
            <input type="password" name="password" class="form-control" placeholder="Enter your password" required />
            <button type="button" class="password-toggle-btn" data-password-toggle aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
          </div>
          <div class="text-end mt-1">
            <a href="{{ route('student.password.request') }}" class="small text-decoration-none" style="color: var(--dtr-primary);">Forgot password?</a>
          </div>
        </div>

        <button type="submit" class="btn btn-login">
          <i class="bi bi-box-arrow-in-right me-2"></i>Login
        </button>
      </form>

      <div class="register-link">
        <p>Don't have an account? <a href="{{ route('student.register') }}">Register here</a></p>
      </div>

      <div class="switch-login">
        <div class="divider">
          <span>OR</span>
        </div>
        <a href="{{ route('coordinator.login') }}" class="btn-switch">
          <i class="bi bi-person-gear me-2"></i>
          Login as Coordinator
        </a>
      </div>
    </div>
  </div>
  <script>
  document.body.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-password-toggle]');
    if (!btn) return;
    var wrap = btn.closest('.input-wrapper');
    var input = wrap && wrap.querySelector('input');
    var icon = btn.querySelector('i');
    if (!input || !icon) return;
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('bi-eye');
      icon.classList.add('bi-eye-slash');
      btn.setAttribute('aria-label', 'Hide password');
      btn.setAttribute('title', 'Hide password');
    } else {
      input.type = 'password';
      icon.classList.remove('bi-eye-slash');
      icon.classList.add('bi-eye');
      btn.setAttribute('aria-label', 'Show password');
      btn.setAttribute('title', 'Show password');
    }
  });
  </script>
</body>
</html>