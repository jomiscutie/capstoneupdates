<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login - NORSU OJT DTR</title>
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" />
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/auth-tokens.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/hide-native-password-reveal.css') }}" />
  <style>
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
      from {
        opacity: 0;
        transform: translateY(12px) scale(0.99);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }
    @keyframes authPanelSlide {
      from {
        opacity: 0;
        transform: translateX(10px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }
    @keyframes authIllustrationIn {
      from {
        opacity: 0;
        transform: translateX(-10px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }
    .login-wrapper {
      width: 100%;
      max-width: min(960px, calc(100vw - 2rem));
      background: #fff;
      border-radius: var(--login-radius-lg);
      box-shadow: 0 4px 24px rgba(0,0,0,0.06);
      overflow: hidden;
      display: grid;
      grid-template-columns: 1fr 1fr;
      min-height: min(520px, 90vh);
      min-height: min(520px, 90dvh);
      animation: authPageIn 0.35s var(--auth-ease) forwards;
    }
    .login-illustration {
      background: #fafafa;
      padding: var(--space-8) var(--space-6);
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
      max-width: calc(100% - (var(--space-6) * 2));
    }
    .login-illustration .brand img {
      height: 48px;
      width: auto;
    }
    .login-illustration .brand span {
      font-weight: 700;
      font-size: clamp(1rem, 1.3vw + 0.72rem, 1.18rem);
      letter-spacing: 0.02em;
      color: var(--login-purple);
      text-transform: uppercase;
      line-height: 1.15;
      white-space: nowrap;
      display: inline-block;
      flex: 0 1 auto;
    }
    .illustration-img {
      width: 100%;
      max-width: 240px;
      height: auto;
      display: block;
      object-fit: contain;
    }
    .login-form-panel {
      padding: var(--space-8) var(--space-6);
      display: flex;
      flex-direction: column;
      justify-content: center;
      max-width: 100%;
      animation: authPanelSlide 0.3s var(--auth-ease) 0.08s both;
    }
    .login-form-panel .form-inner {
      width: 100%;
      max-width: min(360px, 100%);
      margin: 0 auto;
    }
    .login-form-panel .welcome {
      margin-bottom: var(--space-6);
    }
    .login-form-panel .welcome h1 {
      font-size: clamp(1.15rem, 2.5vw + 0.85rem, 1.35rem);
      font-weight: 600;
      color: var(--login-text-heading, #111827);
      margin-bottom: var(--space-2);
      line-height: 1.35;
      letter-spacing: -0.01em;
    }
    .login-form-panel .welcome h1 span {
      display: block;
      font-size: 1.4em;
      font-weight: 700;
      letter-spacing: 0.06em;
      color: var(--login-purple);
      margin-top: 0.2em;
    }
    .login-form-panel .welcome p {
      font-size: 0.875rem;
      color: var(--login-muted);
      line-height: 1.5;
    }
    .form-group { margin-bottom: var(--space-5); }
    .form-group:last-of-type { margin-bottom: var(--space-3); }
    .input-wrapper {
      position: relative;
    }
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
    .form-control:focus-visible {
      outline: none;
      box-shadow: 0 0 0 3px rgba(109,93,209,0.25);
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
    .form-group .login-hint { margin-top: var(--space-2); display: block; }
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
      transition: color var(--login-transition);
    }
    .password-toggle-btn:hover { color: var(--login-purple); }
    .form-options {
      margin-bottom: var(--space-5);
      text-align: center;
    }
    .forgot-link {
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--login-purple);
      text-decoration: none;
    }
    .forgot-link:hover { color: var(--login-purple-dark); }
    .btn-login {
      width: 100%;
      padding: 0.75rem var(--space-4);
      background: var(--login-purple);
      border: none;
      border-radius: var(--login-radius);
      color: #fff;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: background var(--login-transition), opacity var(--login-transition);
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .btn-login:hover:not(:disabled) {
      background: var(--login-purple-dark);
      color: #fff;
    }
    .btn-login:disabled {
      cursor: not-allowed;
      opacity: 0.85;
    }
    .register-prompt {
      text-align: center;
      margin-top: var(--space-6);
      padding-top: var(--space-5);
      border-top: 1px solid var(--login-border);
      font-size: 0.8125rem;
      color: var(--login-muted);
      line-height: 1.5;
    }
    .register-prompt a {
      color: var(--login-purple);
      font-weight: 500;
      text-decoration: none;
    }
    .register-prompt a:hover { color: var(--login-purple-dark); }
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
    .auth-tab:focus-visible {
      outline: none;
      box-shadow: inset 0 0 0 2px var(--login-purple);
    }
    .forgot-link:focus-visible {
      outline: none;
      border-radius: var(--login-radius);
      box-shadow: 0 0 0 2px var(--login-purple);
    }
    .btn-login:focus-visible {
      outline: none;
      box-shadow: 0 0 0 3px rgba(255,255,255,0.5), 0 0 0 6px var(--login-purple);
    }
    .alert {
      border-radius: var(--login-radius);
      font-size: 0.8125rem;
      margin-bottom: var(--space-4);
      border: none;
      padding: var(--space-3) var(--space-4);
    }
    .alert-danger { background: #fef2f2; color: #b91c1c; }
    .alert-success { background: #f0fdf4; color: #166534; }
    .alert-warning { background: #fffbeb; color: #b45309; }
    .login-hint { font-size: 0.75rem; color: var(--login-muted); }
    .field-error {
      font-size: 0.8125rem;
      color: #b91c1c;
      margin-top: var(--space-1);
      display: flex;
      align-items: center;
      gap: 0.35rem;
    }
    .field-error i { flex-shrink: 0; }
    .form-control.is-invalid { border-color: #dc2626; background: #fef2f2; }
    @media (max-width: 768px) {
      .login-wrapper {
        grid-template-columns: 1fr;
        max-width: min(420px, calc(100vw - 1.5rem));
        min-height: auto;
      }
      .login-illustration {
        padding: clamp(var(--space-4), 4vw, var(--space-6));
        min-height: clamp(140px, 25vh, 200px);
        align-items: center;
        justify-content: flex-start;
        gap: 0.55rem;
      }
      .login-illustration .brand {
        position: static;
        max-width: 100%;
        width: 100%;
        justify-content: center;
        margin-bottom: 0.2rem;
      }
      .login-illustration .brand span {
        font-size: clamp(0.82rem, 2.4vw + 0.36rem, 1rem);
        letter-spacing: 0.01em;
      }
      .illustration-img { max-width: min(160px, 40vw); }
      .login-form-panel { padding: clamp(var(--space-4), 4vw, var(--space-6)); }
    }
    @media (max-width: 480px) {
      body { padding: var(--space-3); }
      .login-wrapper { max-width: 100%; border-radius: var(--login-radius); }
      .login-illustration .brand img { height: 30px; }
      .login-illustration .brand span {
        font-size: 0.72rem;
        letter-spacing: 0.01em;
        line-height: 1.1;
      }
      .illustration-img {
        margin-top: 0;
        max-width: min(145px, 38vw);
      }
      .login-form-panel .form-inner { max-width: 100%; }
    }
    body.auth-has-errors .login-wrapper,
    body.auth-has-errors .login-illustration,
    body.auth-has-errors .login-form-panel {
      animation: none;
    }
    body.auth-has-errors .alert,
    body.auth-has-errors .field-error,
    body.auth-has-errors .form-control {
      transition: none;
    }
    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { transition-duration: 0.01ms !important; }
      .login-wrapper, .login-illustration, .login-form-panel { animation: none !important; }
    }
  </style>
</head>
<body class="{{ $errors->any() ? 'auth-has-errors' : '' }}">
  <div class="login-wrapper">
    <div class="login-illustration">
      <div class="brand">
        <img src="{{ asset('images/norsu-seal.png') }}" alt="NORSU" />
        <span>NORSU OJT DTR</span>
      </div>
      <img src="{{ asset('images/login-illustration.png') }}" alt="" class="illustration-img" width="240" height="auto" />
    </div>

    <div class="login-form-panel">
      <div class="form-inner">
      <div class="auth-tabs" role="tablist" aria-label="Authentication tabs">
        <a href="{{ route('login') }}" class="auth-tab active" role="tab" aria-selected="true">Login</a>
        <a href="{{ route('student.register') }}" class="auth-tab" role="tab" aria-selected="false">Register</a>
      </div>

      <div class="welcome">
        <h1>Welcome to <span>NORSU OJT DTR</span></h1>
        <p>Admins and coordinators use email. Students use student number.</p>
      </div>

      @if(session('success'))
      <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
      </div>
      @endif

      @if(session('warning'))
      <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
      </div>
      @endif

      @if(session('info'))
      <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
      </div>
      @endif

      @if($errors->any() && !$errors->has('identifier') && !$errors->has('password'))
      <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-2"></i>
        @foreach($errors->all() as $error) {{ $error }} @endforeach
      </div>
      @endif

      <form id="loginForm" action="{{ url('/login') }}" method="POST">
        @csrf
        <div class="form-group">
          <div class="input-wrapper">
            <i class="bi bi-envelope input-icon"></i>
            <input type="text" name="identifier" id="loginIdentifier" class="form-control {{ $errors->has('identifier') ? 'is-invalid' : '' }}" value="{{ old('identifier') }}" placeholder="Email or student number" required autocomplete="username" />
          </div>
          @if($errors->has('identifier'))
          <p class="field-error" role="alert"><i class="bi bi-exclamation-circle"></i>{{ $errors->first('identifier') }}</p>
          @else
          <p class="login-hint">Admins/coordinators: use email. Students: use student number.</p>
          @endif
        </div>

        <div class="form-group">
          <div class="input-wrapper has-password-toggle">
            <i class="bi bi-key input-icon"></i>
            <input type="password" name="password" id="loginPassword" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="Password" required autocomplete="current-password" />
            <button type="button" class="password-toggle-btn" data-password-toggle aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
          </div>
          @if($errors->has('password'))
          <p class="field-error" role="alert"><i class="bi bi-exclamation-circle"></i>{{ $errors->first('password') }}</p>
          @endif
        </div>

        <div class="form-options">
          <a href="{{ route('student.password.request') }}" class="forgot-link">Forgot Password?</a>
        </div>

        <button type="submit" class="btn-login" id="loginSubmit">
          <span class="btn-login-text">Login</span>
          <span class="btn-login-loading" aria-hidden="true" style="display: none;"><i class="bi bi-arrow-repeat spin" style="margin-right: 0.35rem;"></i>Signing in…</span>
        </button>
      </form>

      <div class="register-prompt">
        Don't have an account? <a href="{{ route('student.register') }}">Register</a>
      </div>
      </div>
    </div>
  </div>
  <style>.spin { animation: spin 0.7s linear infinite; } @keyframes spin { to { transform: rotate(360deg); } }</style>
  <script>
  (function () {
    var STORAGE_KEY = 'norsu_login_identifier';
    var form = document.getElementById('loginForm');
    var identifierInput = document.getElementById('loginIdentifier');
    var submitBtn = document.getElementById('loginSubmit');
    var btnText = submitBtn && submitBtn.querySelector('.btn-login-text');
    var btnLoading = submitBtn && submitBtn.querySelector('.btn-login-loading');

    if (identifierInput && !identifierInput.value) {
      try {
        var saved = localStorage.getItem(STORAGE_KEY);
        if (saved) identifierInput.value = saved;
      } catch (e) {}
    }

    if (form && submitBtn) {
      form.addEventListener('submit', function () {
        if (identifierInput && identifierInput.value) {
          try { localStorage.setItem(STORAGE_KEY, identifierInput.value.trim()); } catch (e) {}
        }
        submitBtn.disabled = true;
        if (btnText) btnText.style.display = 'none';
        if (btnLoading) btnLoading.style.display = 'inline-flex';
      });
    }

    document.body.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-password-toggle]');
      if (!btn) return;
      var wrap = btn.closest('.input-wrapper');
      var input = wrap && wrap.querySelector('input[type="password"], input[type="text"]');
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

  })();
  </script>
</body>
</html>
