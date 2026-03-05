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
      --login-purple: #6D5DD1;
      --login-purple-dark: #5a4bb8;
      --login-muted: #64748b;
      --login-border: #e5e7eb;
      --login-radius: 6px;
      --login-radius-lg: 12px;
      --login-font: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      --login-transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      --auth-ease: cubic-bezier(0.25, 0.46, 0.45, 0.94);
      --space-1: 0.25rem;
      --space-2: 0.5rem;
      --space-3: 0.75rem;
      --space-4: 1rem;
      --space-5: 1.25rem;
      --space-6: 1.5rem;
      --space-8: 2rem;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html { font-size: 16px; }
    body {
      background: var(--login-purple);
      min-height: 100vh;
      min-height: 100dvh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: var(--login-font);
      padding: clamp(var(--space-4), 4vw, var(--space-6));
      color: #1e293b;
      line-height: 1.5;
      overflow-x: hidden;
    }
    @keyframes authPageIn {
      from { opacity: 0; transform: translateY(12px) scale(0.99); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes authPanelSlide {
      from { opacity: 0; transform: translateX(10px); }
      to { opacity: 1; transform: translateX(0); }
    }
    @keyframes authIllustrationIn {
      from { opacity: 0; transform: translateX(-10px); }
      to { opacity: 1; transform: translateX(0); }
    }
    .login-wrapper {
      width: 100%;
      max-width: min(720px, calc(100vw - 2rem));
      background: #fff;
      border-radius: var(--login-radius-lg);
      box-shadow: 0 4px 24px rgba(0,0,0,0.06);
      overflow: hidden;
      display: grid;
      grid-template-columns: 1fr 1fr;
      min-height: min(440px, 90vh);
      min-height: min(440px, 90dvh);
      animation: authPageIn 0.35s var(--auth-ease) forwards;
    }
    .login-illustration {
      background: #fafafa;
      padding: var(--space-6) var(--space-5);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      position: relative;
      animation: authIllustrationIn 0.32s var(--auth-ease) 0.05s both;
    }
    .login-illustration .brand {
      position: absolute;
      top: var(--space-6);
      left: var(--space-6);
      display: flex;
      align-items: center;
      gap: var(--space-2);
    }
    .login-illustration .brand img { height: 48px; width: auto; }
    .login-illustration .brand span {
      font-weight: 700;
      font-size: 1.2rem;
      letter-spacing: 0.04em;
      color: var(--login-purple);
      text-transform: uppercase;
      line-height: 1.2;
    }
    .illustration-img { width: 100%; max-width: 200px; height: auto; display: block; object-fit: contain; }
    .login-form-panel {
      padding: var(--space-6) var(--space-5);
      display: flex;
      flex-direction: column;
      justify-content: center;
      max-width: 100%;
      overflow-y: auto;
      animation: authPanelSlide 0.3s var(--auth-ease) 0.08s both;
    }
    .login-form-panel .form-inner { width: 100%; max-width: min(300px, 100%); margin: 0 auto; }
    .login-form-panel .welcome { margin-bottom: var(--space-6); }
    .login-form-panel .welcome h1 {
      font-size: clamp(1.1rem, 2.2vw + 0.8rem, 1.25rem);
      font-weight: 600;
      color: #111827;
      margin-bottom: var(--space-1);
      line-height: 1.35;
      letter-spacing: -0.01em;
    }
    .login-form-panel .welcome h1 span { color: var(--login-purple); }
    .login-form-panel .welcome p { font-size: 0.8125rem; color: var(--login-muted); line-height: 1.5; }
    .form-group { margin-bottom: var(--space-5); }
    .form-label {
      font-weight: 500;
      color: #374151;
      font-size: 0.8125rem;
      margin-bottom: var(--space-1);
      display: block;
    }
    .form-label i { margin-right: var(--space-2); color: #9ca3af; }
    .input-wrapper { position: relative; }
    .input-icon {
      position: absolute;
      left: 0.875rem;
      top: 50%;
      transform: translateY(-50%);
      color: #6b7280;
      font-size: 1rem;
      z-index: 2;
    }
    .form-control {
      width: 100%;
      padding: 0.625rem 1rem 0.625rem 2.5rem;
      border: 1.5px solid #d1d5db;
      border-radius: var(--login-radius);
      font-size: 0.9375rem;
      background: #fafafa;
      transition: border-color var(--login-transition), background var(--login-transition), box-shadow var(--login-transition);
    }
    .form-control:hover {
      background: #fff;
      border-color: #9ca3af;
    }
    .form-control:focus {
      border-color: var(--login-purple);
      background: #fff;
      outline: none;
      box-shadow: 0 0 0 3px rgba(109,93,209,0.12);
    }
    .form-control::placeholder { color: #6b7280; }
    .form-select {
      width: 100%;
      padding: 0.625rem 1rem 0.625rem 2.5rem;
      border: 1.5px solid #d1d5db;
      border-radius: var(--login-radius);
      font-size: 0.9375rem;
      background: #fafafa;
      transition: border-color var(--login-transition), background var(--login-transition);
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: right 0.75rem center;
      background-size: 16px 12px;
    }
    .form-select:hover {
      background-color: #fff;
      border-color: #9ca3af;
    }
    .form-select:focus {
      border-color: var(--login-purple);
      background-color: #fff;
      outline: none;
      box-shadow: 0 0 0 3px rgba(109,93,209,0.12);
    }
    .input-wrapper.has-password-toggle .form-control { padding-right: 3rem; }
    .password-toggle-btn {
      position: absolute;
      right: 0.5rem;
      top: 50%;
      transform: translateY(-50%);
      width: 2.25rem;
      height: 2.25rem;
      padding: 0;
      border: none;
      background: none;
      color: #94a3b8;
      cursor: pointer;
      border-radius: 6px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: color 0.15s ease;
    }
    .password-toggle-btn:hover { color: var(--login-purple); }
    .form-text { font-size: 0.75rem; color: var(--login-muted); margin-top: var(--space-2); display: block; }
    .btn-register {
      width: 100%;
      padding: var(--space-3) var(--space-4);
      background: var(--login-purple);
      border: none;
      border-radius: var(--login-radius);
      color: #fff;
      font-weight: 500;
      font-size: 0.9375rem;
      cursor: pointer;
      transition: background var(--login-transition);
    }
    .btn-register:hover {
      background: var(--login-purple-dark);
      color: #fff;
    }
    .auth-tabs {
      display: flex;
      gap: 0;
      margin-bottom: var(--space-5);
      border-radius: var(--login-radius);
      overflow: hidden;
      border: 1px solid var(--login-border);
      background: #f9fafb;
    }
    .auth-tab {
      flex: 1;
      padding: var(--space-2) var(--space-3);
      text-align: center;
      font-size: 0.8125rem;
      font-weight: 500;
      text-decoration: none;
      color: var(--login-muted);
      transition: color var(--login-transition), background var(--login-transition);
    }
    .auth-tab.active {
      background: var(--login-purple);
      color: #fff;
    }
    .auth-tab:not(.active):hover {
      background: #f3f4f6;
      color: #374151;
    }
    .login-link {
      text-align: center;
      margin-top: var(--space-6);
      padding-top: var(--space-5);
      border-top: 1px solid var(--login-border);
      font-size: 0.8125rem;
      color: var(--login-muted);
    }
    .login-link p { margin: 0; }
    .login-link a {
      color: var(--login-purple);
      font-weight: 500;
      text-decoration: none;
    }
    .login-link a:hover { color: var(--login-purple-dark); }
    .switch-login { margin-top: var(--space-5); text-align: center; }
    .divider {
      position: relative;
      text-align: center;
      margin: var(--space-4) 0;
    }
    .divider::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      right: 0;
      height: 1px;
      background: var(--login-border);
    }
    .divider span {
      position: relative;
      background: #fff;
      padding: 0 var(--space-4);
      color: var(--login-muted);
      font-size: 0.85rem;
    }
    .btn-switch {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: var(--space-2) var(--space-4);
      width: 100%;
      background: #fff;
      border: 1px solid var(--login-border);
      border-radius: var(--login-radius);
      color: #374151;
      font-weight: 500;
      font-size: 0.8125rem;
      text-decoration: none;
      transition: border-color var(--login-transition), background var(--login-transition);
    }
    .btn-switch:hover {
      background: #f9fafb;
      border-color: #d1d5db;
      color: #111827;
    }
    .btn-switch i { color: var(--login-purple); }
    .alert {
      border-radius: var(--login-radius);
      font-size: 0.8125rem;
      margin-bottom: var(--space-4);
      border: none;
      padding: var(--space-3) var(--space-4);
    }
    .alert-danger { background: #fef2f2; color: #b91c1c; }
    .alert-success { background: #f0fdf4; color: #166534; }
    @media (max-width: 768px) {
      .login-wrapper {
        grid-template-columns: 1fr;
        max-width: min(380px, calc(100vw - 1.5rem));
        min-height: auto;
      }
      .login-illustration {
        padding: clamp(var(--space-4), 4vw, var(--space-5));
        min-height: clamp(120px, 22vh, 160px);
      }
      .illustration-img { max-width: min(140px, 38vw); }
      .login-form-panel { padding: clamp(var(--space-4), 4vw, var(--space-5)); }
    }
    @media (max-width: 480px) {
      body { padding: var(--space-3); }
      .login-wrapper { max-width: 100%; border-radius: var(--login-radius); }
      .login-illustration .brand img { height: 40px; }
      .login-illustration .brand span { font-size: 1rem; letter-spacing: 0.03em; }
      .login-form-panel .form-inner { max-width: 100%; }
    }
    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { transition-duration: 0.01ms !important; }
      .login-wrapper, .login-illustration, .login-form-panel { animation: none !important; }
    }
  </style>
</head>
<body>
  <div class="login-wrapper">
    <div class="login-illustration">
      <div class="brand">
        <img src="{{ asset('images/norsu-seal.png') }}" alt="NORSU" />
        <span>NORSU OJT DTR</span>
      </div>
      <img src="{{ asset('images/registration-illustration.png') }}" alt="Create your account" class="illustration-img" width="200" height="auto" />
    </div>
    <div class="login-form-panel">
      <div class="form-inner">
      <div class="auth-tabs" role="tablist" aria-label="Authentication tabs">
        <a href="{{ route('login') }}" class="auth-tab" role="tab" aria-selected="false">Login</a>
        <a href="{{ route('coordinator.register') }}" class="auth-tab active" role="tab" aria-selected="true">Register</a>
      </div>
      <div class="welcome">
        <h1>Coordinator <span>Registration</span></h1>
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

      <form action="{{ route('coordinator.register.submit') }}" method="POST">
        @csrf
        <div class="form-group">
          <label class="form-label"><i class="bi bi-person"></i>Full Name</label>
          <div class="input-wrapper">
            <i class="bi bi-person input-icon"></i>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Enter your full name" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label"><i class="bi bi-envelope"></i>Email Address</label>
          <div class="input-wrapper">
            <i class="bi bi-envelope-at input-icon"></i>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Enter your email" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label"><i class="bi bi-building"></i>College</label>
          <div class="input-wrapper">
            <i class="bi bi-building input-icon"></i>
            <input type="text" name="college" class="form-control" value="{{ old('college') }}" placeholder="Enter your college" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label"><i class="bi bi-book"></i>Program/Course</label>
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
          <label class="form-label"><i class="bi bi-lock"></i>Password</label>
          <div class="input-wrapper has-password-toggle">
            <i class="bi bi-key input-icon"></i>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            <button type="button" class="password-toggle-btn" data-password-toggle aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label"><i class="bi bi-lock-fill"></i>Confirm Password</label>
          <div class="input-wrapper has-password-toggle">
            <i class="bi bi-key-fill input-icon"></i>
            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm password" required>
            <button type="button" class="password-toggle-btn" data-password-toggle aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
          </div>
        </div>
        <button type="submit" class="btn btn-register">
          <i class="bi bi-person-plus me-2"></i>Register
        </button>
      </form>

      <div class="login-link">
        <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
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
