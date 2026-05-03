<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Student Registration - NORSU OJT DTR</title>
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" />
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/hide-native-password-reveal.css') }}" />
  <style>
    :root {
      --login-purple: #6D5DD1;
      --login-purple-dark: #5a4bb8;
      --login-muted: #64748b;
      --login-border: #e5e7eb;
      --login-radius: 6px;
      --login-radius-lg: 8px;
      --login-font: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      --login-transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      --auth-ease: cubic-bezier(0.25, 0.46, 0.45, 0.94);
      --register-action-min-h: 2.1rem;
      --register-action-pad-y: 0.35rem;
      --register-action-pad-x: 0.75rem;
      --register-action-fs: 0.8rem;
      /* Compact registration type scale */
      --register-fs-section: 0.7rem;
      --register-fs-label: 0.82rem;
      --register-fs-body: 0.88rem;
      --register-fs-title: 1.1rem;
      --register-input-border: #e8eaed;
      --register-input-border-hover: #d1d5db;
      --register-consent-border: #e8ecf2;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html { font-size: 16px; }
    body {
      background:
        radial-gradient(ellipse 140% 80% at 50% -20%, rgba(255, 255, 255, 0.14), transparent 50%),
        linear-gradient(158deg, #5342a8 0%, var(--login-purple) 42%, #4b3daf 100%);
      height: 100vh;
      height: 100dvh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: var(--login-font);
      padding: 0.75rem;
      color: #1e293b;
      overflow: hidden;
    }
    @keyframes authPageIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes authPanelSlide { from { opacity: 0; transform: translateX(8px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes authIllustrationIn { from { opacity: 0; transform: translateX(-8px); } to { opacity: 1; transform: translateX(0); } }

    .login-wrapper {
      width: 95%;
      max-width: min(1400px, calc(100vw - 1.5rem));
      max-height: calc(100vh - 1.5rem);
      max-height: calc(100dvh - 1.5rem);
      background: #fff;
      border-radius: 12px;
      border: 1px solid rgba(255, 255, 255, 0.22);
      box-shadow:
        0 4px 6px rgba(0, 0, 0, 0.04),
        0 22px 50px rgba(43, 32, 99, 0.28);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      animation: authPageIn 0.28s var(--auth-ease) forwards;
    }

    .login-form-panel {
      padding: 0.5rem 0.75rem;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      overflow-y: auto;
      flex: 1;
      animation: authPanelSlide 0.28s var(--auth-ease) both;
    }
    .login-form-panel .form-inner { width: 100%; max-width: 100%; margin: 0 auto; }
    .auth-tabs {
      display: flex;
      gap: 0.2rem;
      padding: 0.2rem;
      margin-bottom: 0.3rem;
      border-radius: var(--login-radius-lg);
      border: 1px solid var(--login-border);
      background: #f1f5f9;
    }
    .auth-tab {
      flex: 1;
      padding: 0.3rem 0.4rem;
      border-radius: calc(var(--login-radius) - 1px);
      text-align: center;
      font-size: var(--register-fs-body);
      font-weight: 600;
      text-decoration: none;
      color: var(--login-muted);
      transition: color var(--login-transition), background var(--login-transition), box-shadow var(--login-transition);
    }
    .auth-tab.active {
      background: var(--login-purple);
      color: #fff;
      box-shadow: 0 2px 8px rgba(109, 93, 209, 0.3);
    }
    .auth-tab:not(.active):hover { background: #fff; color: #334155; }
    .auth-tab:focus-visible {
      outline: 2px solid var(--login-purple);
      outline-offset: 2px;
    }

    .login-form-panel .welcome { margin-bottom: 0.25rem; }
    .login-form-panel .welcome h1 {
      font-size: var(--register-fs-title);
      font-weight: 700;
      color: #111827;
      margin-bottom: 0.05rem;
      line-height: 1.2;
    }
    .login-form-panel .welcome h1 span { color: var(--login-purple); }
    .login-form-panel .welcome p { font-size: var(--register-fs-label); color: var(--login-muted); line-height: 1.3; margin-bottom: 0; }

    .alert {
      border-radius: var(--login-radius);
      font-size: var(--register-fs-body);
      margin-bottom: 0.25rem;
      border: 1px solid transparent;
      border-left-width: 3px;
      padding: 0.25rem 0.5rem;
    }
    .alert ul {
      margin-bottom: 0;
      max-height: 60px;
      overflow: auto;
      padding-left: 1rem;
    }
    .alert.alert-danger {
      background: #fef7f7;
      color: #991b1b;
      border-left-color: #dc2626;
      border-color: rgba(220, 38, 38, 0.12);
    }
    .alert.alert-success {
      background: #f6fdf9;
      color: #166534;
      border-left-color: #16a34a;
      border-color: rgba(22, 163, 74, 0.12);
    }
    .alert.alert-info {
      background: #f4faff;
      color: #0369a1;
      border-left-color: #0284c7;
      border-color: rgba(2, 132, 199, 0.12);
    }
    .alert.alert-secondary {
      background: #f8fafc;
      color: #475569;
      border-left-color: #94a3b8;
      border-color: rgba(148, 163, 184, 0.16);
    }

    #registerForm {
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
      align-items: stretch;
    }
    .register-stack {
      display: flex;
      flex-direction: column;
      gap: 0.3rem;
      width: 100%;
      min-width: 0;
    }
    .register-section-heading {
      margin: 0;
      padding: 0 0 0.2rem;
      font-size: 0.7rem;
      font-weight: 900;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: #111827;
      line-height: 1.2;
      border-bottom: 1.5px solid rgba(15, 23, 42, 0.12);
      width: 100%;
    }
    /* Compact divider between sections */
    hr.register-section-rule {
      display: block;
      width: 100%;
      height: 0;
      margin: 0.25rem 0 0.2rem;
      padding: 0;
      border: none;
      border-top: 1px solid #301934;
      background: none;
      flex-shrink: 0;
      opacity: 0.5;
    }
    .register-field-columns {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 0.2rem 0.5rem;
      align-items: start;
      width: 100%;
      min-width: 0;
    }
    .register-field-columns > .form-group {
      min-width: 0;
    }
    .register-field-columns.register-password-row {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      max-width: 66%;
    }
    .register-col {
      display: flex;
      flex-direction: column;
      gap: 0.2rem;
      min-width: 0;
    }
    .form-group {
      margin-bottom: 0;
      min-width: 0;
    }
    .form-group.is-hidden {
      display: none;
    }
    .form-label {
      font-weight: 600;
      color: #374151;
      font-size: var(--register-fs-label);
      margin-bottom: 0.05rem;
      display: block;
      line-height: 1.2;
      word-break: break-word;
    }
    .form-label i { margin-right: 0.15rem; color: #94a3b8; font-size: 0.85em; }
    .input-wrapper { position: relative; }
    .input-icon {
      position: absolute;
      left: 0.55rem;
      top: 50%;
      transform: translateY(-50%);
      color: #6b7280;
      font-size: 0.85rem;
      z-index: 2;
    }
    .form-control,
    .form-select {
      width: 100%;
      min-height: 1.85rem;
      height: 1.85rem;
      padding: 0.25rem 0.45rem 0.25rem 1.7rem;
      border: 1px solid var(--register-input-border);
      border-radius: var(--login-radius);
      font-size: var(--register-fs-body);
      background: #fff;
      transition: border-color var(--login-transition), background var(--login-transition), box-shadow var(--login-transition);
    }
    .form-control::placeholder { white-space: normal; }
    .form-control:hover,
    .form-select:hover {
      background: #fff;
      border-color: var(--register-input-border-hover);
    }
    .form-control:focus,
    .form-select:focus {
      border-color: var(--login-purple);
      background: #fff;
      outline: none;
      box-shadow: 0 0 0 2px rgba(109,93,209,0.12);
    }
    .form-select {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: right 0.5rem center;
      background-size: 12px 10px;
      padding-right: 1.5rem;
    }
    .form-select.ghost-disabled,
    .form-select.ghost-disabled:hover,
    .form-select.ghost-disabled:focus {
      background-color: #f3f4f6;
      color: #6b7280;
      border-color: #d1d5db;
      box-shadow: none;
      cursor: not-allowed;
    }
    .input-wrapper.has-password-toggle .form-control { padding-right: 2rem; }
    .password-toggle-btn {
      position: absolute;
      right: 0.2rem;
      top: 50%;
      transform: translateY(-50%);
      width: 1.4rem;
      height: 1.4rem;
      border: none;
      background: none;
      color: #94a3b8;
      cursor: pointer;
      border-radius: 4px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: color 0.15s ease;
    }
    .password-toggle-btn:hover { color: var(--login-purple); }
    .login-hint { display: none !important; }
    .form-text { font-size: var(--register-fs-label); color: var(--login-muted); margin-top: 0.08rem; }
    .password-rules {
      margin-top: 0.1rem;
      display: flex;
      flex-wrap: wrap;
      gap: 0.15rem 0.5rem;
      font-size: 0.7rem;
      color: #64748b;
    }
    .password-rule {
      display: inline-flex;
      align-items: center;
      gap: 0.15rem;
      line-height: 1.1;
      min-width: 0;
    }
    .password-rule i {
      font-size: 0.7em;
      color: #94a3b8;
    }
    .password-rule.ok {
      color: #047857;
      font-weight: 600;
    }
    .password-rule.ok i {
      color: #059669;
    }
    .date-range-group {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.2rem;
    }
    .date-range-group .form-control {
      padding-left: 1.6rem;
      cursor: pointer;
      pointer-events: auto;
      position: relative;
      z-index: 1;
    }

    .consent-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0.25rem;
      align-items: stretch;
    }
    .consent-box {
      border: 1px solid var(--register-consent-border);
      border-radius: var(--login-radius);
      background: #fff;
      padding: 0.2rem 0.3rem;
      height: 100%;
      display: flex;
      align-items: flex-start;
    }
    .consent-box .form-check {
      margin: 0;
      font-size: 0.72rem !important;
      color: var(--login-muted);
      line-height: 1.2;
      display: flex;
      align-items: flex-start;
      gap: 0.25rem;
    }
    .consent-box .form-check-input {
      margin: 0.05rem 0 0;
      flex: 0 0 auto;
      width: 0.75rem;
      height: 0.75rem;
      border: 1px solid #64748b;
      background-color: #fff;
      cursor: pointer;
    }
    .consent-box .form-check-input:checked {
      background-color: var(--login-purple);
      border-color: var(--login-purple);
    }
    .consent-box .form-check-input:focus {
      box-shadow: 0 0 0 2px rgba(109,93,209,0.2);
    }
    .consent-box .form-check-label {
      margin: 0;
      flex: 1 1 auto;
      color: #334155;
      cursor: pointer;
      line-height: 1.25;
      word-break: break-word;
      overflow-wrap: anywhere;
    }
    .consent-label-row {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      flex-wrap: wrap;
    }
    .consent-see-more-btn {
      border: 1px solid rgba(109, 93, 209, 0.28);
      background: rgba(109, 93, 209, 0.08);
      color: var(--login-purple);
      border-radius: 999px;
      padding: 0.08rem 0.4rem;
      font-size: 0.7rem;
      font-weight: 600;
      line-height: 1.1;
      display: inline-flex;
      align-items: center;
      gap: 0.15rem;
      cursor: pointer;
    }
    .consent-see-more-btn:hover {
      background: rgba(109, 93, 209, 0.14);
      border-color: rgba(109, 93, 209, 0.38);
      color: var(--login-purple-dark);
    }

    .register-actions {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0.25rem;
      align-items: start;
    }
    .register-actions > .btn-register {
      box-sizing: border-box;
      align-self: start;
      justify-self: stretch;
      width: 100%;
      min-height: var(--register-action-min-h);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.25rem;
    }
    .register-actions > .btn-register .bi {
      font-size: 1em;
      opacity: 0.96;
    }
    .btn-register {
      box-sizing: border-box;
      min-height: var(--register-action-min-h);
      padding: var(--register-action-pad-y) var(--register-action-pad-x);
      background: var(--login-purple);
      border: 1px solid transparent;
      border-radius: var(--login-radius);
      color: #fff;
      font-weight: 600;
      font-size: var(--register-action-fs);
      line-height: 1.3;
      cursor: pointer;
      box-shadow: 0 2px 4px rgba(109, 93, 209, 0.2);
      transition: background var(--login-transition), box-shadow var(--login-transition), transform 0.12s ease;
    }
    #registerForm .btn.btn-register {
      box-sizing: border-box !important;
      min-height: var(--register-action-min-h) !important;
      padding: var(--register-action-pad-y) var(--register-action-pad-x) !important;
      border: 1px solid transparent !important;
      font-size: var(--register-action-fs) !important;
      line-height: 1.3 !important;
    }
    .btn-register:hover {
      background: var(--login-purple-dark);
      color: #fff;
      box-shadow: 0 2px 8px rgba(109, 93, 209, 0.25);
    }
    .btn-register:active { transform: translateY(1px); }
    .register-alt-action {
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 0.15rem;
      align-items: stretch;
    }
    .btn-register-alt {
      box-sizing: border-box;
      width: 100%;
      min-height: var(--register-action-min-h);
      padding: var(--register-action-pad-y) var(--register-action-pad-x);
      border: 1px solid var(--register-consent-border);
      border-radius: var(--login-radius);
      background: #fff;
      color: #334155;
      font-weight: 600;
      font-size: var(--register-action-fs);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.25rem;
      cursor: pointer;
      transition: border-color var(--login-transition), background var(--login-transition), color var(--login-transition), box-shadow var(--login-transition);
    }
    .btn-register-alt:hover { border-color: #c7d2fe; background: #f8faff; color: #4338ca; }
    .register-alt-note {
      margin: 0;
      text-align: center;
      color: #64748b;
      font-size: 0.7rem;
      line-height: 1.3;
    }
    .password-match-indicator {
      margin-top: 0.1rem;
      font-size: 0.7rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 0.15rem;
      color: #64748b;
      min-height: 0.75rem;
    }
    .password-match-indicator i {
      font-size: 0.85em;
      opacity: 0.9;
    }
    .password-match-indicator.match {
      color: #059669;
    }
    .password-match-indicator.mismatch {
      color: #dc2626;
    }
    .login-link,
    .switch-login {
      text-align: center;
      width: 100%;
    }
    .login-link {
      margin-top: 0.25rem;
      padding-top: 0.25rem;
      border-top: 1px solid var(--login-border);
      font-size: var(--register-fs-body);
      color: var(--login-muted);
    }
    .login-link p { margin: 0; }
    .login-link a { color: var(--login-purple); font-weight: 600; text-decoration: none; }
    .login-link a:hover { color: var(--login-purple-dark); }
    .switch-login { margin-top: 0.2rem; }
    .divider {
      position: relative;
      text-align: center;
      margin: 0.2rem 0;
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
      padding: 0 0.3rem;
      color: var(--login-muted);
      font-size: 0.7rem;
    }
    .switch-login {
      display: flex;
      flex-direction: column;
      gap: 0.2rem;
    }
    .btn-switch {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.25rem;
      padding: 0.3rem 0.5rem;
      min-height: 2rem;
      width: 100%;
      background: #fafbfc;
      border: 1px solid var(--login-border);
      border-radius: var(--login-radius);
      color: #374151;
      font-weight: 600;
      font-size: var(--register-fs-body);
      text-decoration: none;
      transition: border-color var(--login-transition), background var(--login-transition), box-shadow var(--login-transition);
    }
    .btn-switch:hover {
      background: #fff;
      border-color: #cfd6e6;
      color: #111827;
      box-shadow: 0 1px 4px rgba(15, 23, 42, 0.05);
    }
    .btn-switch i { color: var(--login-purple); }

    @media (max-width: 991px) {
      .login-wrapper { max-width: min(780px, calc(100vw - 1rem)); }
      .register-field-columns {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.25rem;
      }
      .register-field-columns.register-password-row {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        max-width: 100%;
      }
      .consent-grid,
      .register-actions {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 576px) {
      .login-wrapper {
        border-radius: 8px;
        max-width: 100%;
      }
      .register-field-columns {
        grid-template-columns: 1fr;
      }
      .register-field-columns.register-password-row {
        grid-template-columns: 1fr;
      }
      .password-rules {
        flex-direction: column;
        gap: 0.1rem;
      }
      .login-form-panel { padding: 0.4rem 0.6rem; }
      body { padding: 0.25rem; }
    }
    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { transition-duration: 0.01ms !important; }
      .login-wrapper, .login-illustration, .login-form-panel { animation: none !important; }
    }
  </style>
</head>
<body>
  <div class="login-wrapper">
    <div class="login-form-panel">
      <div class="form-inner">
      <div class="auth-tabs" role="tablist" aria-label="Authentication tabs">
        <a href="{{ route('login') }}" class="auth-tab" role="tab" aria-selected="false">Login</a>
        <a href="{{ route('student.register') }}" class="auth-tab active" role="tab" aria-selected="true">Register</a>
      </div>
      <div class="welcome">
        <h1>Student <span>Registration</span></h1>
        <p>For currently enrolled NORSU OJT students using their official student number.</p>
      </div>

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

      <form action="{{ route('student.register.submit') }}" method="POST" id="registerForm">
        @csrf
        <input type="hidden" name="face_encoding" id="faceEncodingInput">
        <input type="hidden" id="oldMajorValue" value="{{ old('major') }}">

        <div class="register-stack">
          <h2 class="register-section-heading" id="register-heading-personal">Personal Information</h2>
          <div class="register-field-columns" role="group" aria-labelledby="register-heading-personal">
            <div class="form-group">
              <label class="form-label" for="regStudentNo"><i class="bi bi-card-text"></i>Student Number</label>
              <div class="input-wrapper">
                <i class="bi bi-card-text input-icon" aria-hidden="true"></i>
                <input id="regStudentNo" type="text" name="student_no" class="form-control" value="{{ old('student_no') }}" placeholder="e.g. 202212345" inputmode="numeric" pattern="[0-9]*" autocomplete="off" oninput="this.value=this.value.replace(/\D/g,'')" maxlength="50" title="Official ID — digits only." required />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="regLastName"><i class="bi bi-person"></i>Last Name</label>
              <div class="input-wrapper">
                <i class="bi bi-person input-icon" aria-hidden="true"></i>
                <input id="regLastName" type="text" name="last_name" class="form-control register-name-field" value="{{ old('last_name') }}" placeholder="Family name" maxlength="100" autocomplete="family-name" inputmode="text" autocapitalize="words" spellcheck="false" title="Letters only" required />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="regFirstName"><i class="bi bi-person"></i>First Name</label>
              <div class="input-wrapper">
                <i class="bi bi-person input-icon" aria-hidden="true"></i>
                <input id="regFirstName" type="text" name="first_name" class="form-control register-name-field" value="{{ old('first_name') }}" placeholder="Given name" maxlength="100" autocomplete="given-name" inputmode="text" autocapitalize="words" spellcheck="false" title="Letters only" required />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="regMiddleName"><i class="bi bi-person"></i>Middle Name</label>
              <div class="input-wrapper">
                <i class="bi bi-person input-icon" aria-hidden="true"></i>
                <input id="regMiddleName" type="text" name="middle_name" class="form-control register-name-field" value="{{ old('middle_name') }}" placeholder="Optional" maxlength="100" autocomplete="additional-name" inputmode="text" autocapitalize="words" spellcheck="false" />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="regSuffix"><i class="bi bi-person-badge"></i>Suffix</label>
              <div class="input-wrapper">
                <i class="bi bi-person-badge input-icon" aria-hidden="true"></i>
                <select id="regSuffix" name="suffix" class="form-select" autocomplete="honorific-suffix">
                  <option value="">No suffix</option>
                  @foreach(['JR', 'SR', 'II', 'III', 'IV', 'V'] as $suffixOption)
                  <option value="{{ $suffixOption }}" {{ old('suffix') == $suffixOption ? 'selected' : '' }}>{{ $suffixOption }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          <hr class="register-section-rule" aria-hidden="true" />

          <h2 class="register-section-heading" id="register-heading-academic">Academic Information</h2>
          <div class="register-field-columns" role="group" aria-labelledby="register-heading-academic">
            <div class="form-group" id="majorGroup">
              <label class="form-label" for="majorSelect"><i class="bi bi-journal-bookmark"></i>Major</label>
              <div class="input-wrapper">
                <i class="bi bi-journal-bookmark input-icon" aria-hidden="true"></i>
                <select name="major" id="majorSelect" class="form-select">
                  <option value="">Select Major</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="programSelect"><i class="bi bi-book"></i>Program / Course</label>
              <div class="input-wrapper">
                <i class="bi bi-book input-icon" aria-hidden="true"></i>
                <select name="course" id="programSelect" class="form-select" required>
                  @foreach(\App\Models\Student::getProgramCatalog() as $collegeLabel => $programOptions)
                  <optgroup label="{{ $collegeLabel }}">
                    @foreach($programOptions as $programOption)
                    <option value="{{ $programOption }}" {{ old('course') == $programOption ? 'selected' : '' }}>{{ $programOption }}</option>
                    @endforeach
                  </optgroup>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="regTerm"><i class="bi bi-calendar3"></i>Term</label>
              <div class="input-wrapper">
                <i class="bi bi-calendar3 input-icon" aria-hidden="true"></i>
                <select id="regTerm" name="term" class="form-select" required>
                  <option value="">Select Term</option>
                  @foreach(\App\Models\Student::TERMS as $termOption)
                  <option value="{{ $termOption }}" {{ old('term') == $termOption ? 'selected' : '' }}>{{ $termOption }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label"><i class="bi bi-journal-text"></i>School Year</label>
              <input type="hidden" name="school_year" id="schoolYearValue" value="{{ old('school_year') }}">
              <div class="date-range-group">
                <div class="input-wrapper">
                  <i class="bi bi-calendar3 input-icon" aria-hidden="true"></i>
                  <input type="date" id="schoolYearStart" class="form-control" required aria-label="School year start">
                </div>
                <div class="input-wrapper">
                  <i class="bi bi-calendar3 input-icon" aria-hidden="true"></i>
                  <input type="date" id="schoolYearEnd" class="form-control" required aria-label="School year end">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="regSection"><i class="bi bi-grid-3x3-gap"></i>Section</label>
              <div class="input-wrapper">
                <i class="bi bi-grid-3x3-gap input-icon" aria-hidden="true"></i>
                <select id="regSection" name="section" class="form-select" required>
                  <option value="">Select Section</option>
                  @foreach(\App\Models\Student::getSectionOptions() as $sectionOption)
                  @continue(strcasecmp(trim((string) $sectionOption), 'All') === 0 || strcasecmp(trim((string) $sectionOption), 'Section All') === 0)
                  <option value="{{ $sectionOption }}" {{ old('section') == $sectionOption ? 'selected' : '' }}>
                    {{ in_array($sectionOption, \App\Models\Student::SECTIONS, true) ? 'Section '.$sectionOption : $sectionOption }}
                  </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="regOffice"><i class="bi bi-building"></i>Assigned Office</label>
              <div class="input-wrapper">
                <i class="bi bi-building input-icon" aria-hidden="true"></i>
                <select id="regOffice" name="assigned_office" class="form-select" title="Optional now; set or request later in Student settings.">
                  @php($officeOld = old('assigned_office'))
                  <option value="" {{ $officeOld === null || $officeOld === '' ? 'selected' : '' }}>No assigned office yet</option>
                  @foreach(\App\Models\Student::getOfficeOptions() as $officeOption)
                  <option value="{{ $officeOption }}" {{ (string) $officeOld === (string) $officeOption ? 'selected' : '' }}>{{ $officeOption }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          <h2 class="register-section-heading" id="register-heading-password">Account Security</h2>
          <div class="register-field-columns register-password-row" role="group" aria-labelledby="register-heading-password">
            <div class="form-group">
              <label class="form-label" for="passwordInput"><i class="bi bi-lock"></i>Password</label>
              <div class="input-wrapper has-password-toggle">
                <i class="bi bi-key input-icon" aria-hidden="true"></i>
                <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Enter password" minlength="8" autocomplete="new-password" required />
                <button type="button" class="password-toggle-btn" data-password-toggle aria-label="Show password"><i class="bi bi-eye" aria-hidden="true"></i></button>
              </div>
              <div class="password-rules" id="passwordRules" aria-live="polite">
                <span class="password-rule" data-rule="length"><i class="bi bi-circle" aria-hidden="true"></i> 8+ chars</span>
                <span class="password-rule" data-rule="uppercase"><i class="bi bi-circle" aria-hidden="true"></i> ABC</span>
                <span class="password-rule" data-rule="lowercase"><i class="bi bi-circle" aria-hidden="true"></i> abc</span>
                <span class="password-rule" data-rule="number"><i class="bi bi-circle" aria-hidden="true"></i> 123</span>
                <span class="password-rule" data-rule="symbol"><i class="bi bi-circle" aria-hidden="true"></i> !@#</span>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="passwordConfirmationInput"><i class="bi bi-lock-fill"></i>Confirm Password</label>
              <div class="input-wrapper has-password-toggle">
                <i class="bi bi-key-fill input-icon" aria-hidden="true"></i>
                <input type="password" name="password_confirmation" id="passwordConfirmationInput" class="form-control" placeholder="Confirm password" minlength="8" autocomplete="new-password" required />
                <button type="button" class="password-toggle-btn" data-password-toggle aria-label="Show password"><i class="bi bi-eye" aria-hidden="true"></i></button>
              </div>
              <div id="passwordMatchIndicator" class="password-match-indicator" aria-live="polite"></div>
            </div>
          </div>
        </div>

        <div class="consent-grid">
          <div class="consent-box">
            <div class="form-check text-start">
              <input class="form-check-input" type="checkbox" value="1" id="privacyConsent" required>
              <label class="form-check-label" for="privacyConsent">
                <span class="consent-label-row">
                  <span>I understand my personal and facial data will be used only for OJT attendance, in line with RA 10173.</span>
                </span>
              </label>
              <button type="button" class="consent-see-more-btn" id="privacyConsentSeeMoreBtn">
                <i class="bi bi-info-circle"></i> See more
              </button>
            </div>
          </div>
          <div class="consent-box">
            <div class="form-check text-start">
              <input class="form-check-input" type="checkbox" value="1" id="honestyConsent" required>
              <label class="form-check-label" for="honestyConsent">
                I will use only my own account and attendance records honestly.
              </label>
            </div>
          </div>
        </div>

        <div class="register-actions">
          <button type="button" class="btn btn-register" onclick="openFaceCapture()">
            <i class="bi bi-camera" aria-hidden="true"></i><span>Register with Face</span>
          </button>
          <div class="register-alt-action">
            <button type="button" class="btn-register-alt" onclick="submitWithoutFace()">
              <i class="bi bi-person-check"></i>
              <span>Continue without camera</span>
            </button>
            <p class="register-alt-note">Use only if camera access is unavailable.</p>
          </div>
        </div>
      </form>

      <div class="login-link">
        <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
      </div>

      <div class="switch-login">
        <div class="divider">
          <span>Need coordinator access?</span>
        </div>
        <a href="{{ route('login') }}" class="btn-switch">
          <i class="bi bi-person-gear" aria-hidden="true"></i><span>Contact Admin for Coordinator Account</span>
        </a>
      </div>
      </div>
    </div>
  </div>

  <!-- Face Capture Modal -->
  <div class="modal fade" id="faceCaptureModal" tabindex="-1" aria-labelledby="faceCaptureModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="faceCaptureModalLabel">
            <i class="bi bi-camera-fill me-2"></i>Face Registration
          </h5>
        </div>
        <div class="modal-body text-center">
          <div class="alert alert-info mb-3 text-start">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Face registration steps:</strong>
            <ol class="mt-2 mb-0 ps-4">
              <li>Allow camera access when prompted.</li>
              <li>Look directly at the camera with good lighting.</li>
              <li>Blink naturally 2-3 times and hold your head steady.</li>
              <li>Wait for the system to confirm that your face has been captured.</li>
            </ol>
          </div>
          <div class="alert alert-secondary mb-3 text-start">
            <i class="bi bi-shield-lock me-2"></i>
            <strong>Data privacy notice:</strong> Captured faces are used for <strong>educational purposes only</strong> and are kept
            securely inside NORSU's OJT DTR system. They are not shared outside the university.
          </div>
          
          <div class="position-relative d-inline-block mb-3" style="background: #000; border-radius: 10px; overflow: hidden;">
            <video id="captureVideo" autoplay playsinline style="width: 100%; max-width: 640px; display: block;"></video>
            <canvas id="captureCanvas" style="position: absolute; top: 0; left: 0; width: 100%; max-width: 640px; pointer-events: none;"></canvas>
            <div id="faceOverlay" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 200px; height: 250px; border: 3px dashed #4CAF50; border-radius: 10px; display: none; pointer-events: none;"></div>
          </div>

          <!-- Progress Steps -->
          <div class="progress-steps mb-3">
            <div class="step" id="step1">
              <i class="bi bi-camera"></i>
              <span>Camera Ready</span>
            </div>
            <div class="step" id="step2">
              <i class="bi bi-eye"></i>
              <span>Face Detected</span>
            </div>
            <div class="step" id="step3">
              <i class="bi bi-check-circle"></i>
              <span>Liveness Verified</span>
            </div>
            <div class="step" id="step4">
              <i class="bi bi-cloud-upload"></i>
              <span>Capturing...</span>
            </div>
          </div>

          <div id="captureStatus" class="mt-3">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Initializing camera...</p>
          </div>

          <div id="captureLiveness" class="mt-2" style="display: none;">
            <div class="progress mb-2" style="height: 8px;">
              <div id="livenessProgress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
            </div>
            <small class="text-info">
              <i class="bi bi-eye-fill me-1"></i>
              Blinks detected: <strong id="captureBlinkCount">0</strong> / 2 (Keep blinking naturally)
            </small>
          </div>

          <div id="captureSuccess" class="mt-3" style="display: none;">
            <div class="alert alert-success">
              <i class="bi bi-check-circle-fill me-2"></i>
              <strong>Face captured successfully!</strong> Processing registration...
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="cancelBtn" onclick="stopFaceCapture()" disabled>Cancel</button>
          <button type="button" class="btn btn-primary" id="captureFaceBtn" onclick="captureAndRegister()" disabled style="display: none;">
            <i class="bi bi-camera-fill me-2"></i>Capture & Register
          </button>
        </div>
      </div>
    </div>
  </div>

  <style>
    .progress-steps {
      display: flex;
      justify-content: space-around;
      align-items: center;
      margin: 0.85rem 0;
      padding: 0.72rem 1rem;
      background: #fff;
      border-radius: 1rem;
      border: 1px solid var(--register-consent-border, #e8ecf2);
    }
    .step {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.35rem;
      flex: 1;
      opacity: 0.35;
      transition: opacity 0.2s ease, transform 0.2s ease;
    }
    .step.active { opacity: 1; transform: scale(1.05); }
    .step.completed { opacity: 1; }
    .step i { font-size: 1.4rem; color: #64748b; }
    .step.active i {
      color: #4f46e5;
      animation: pulse 1.5s infinite;
    }
    .step.completed i { color: #059669; }
    .step span { font-size: var(--register-fs-body); font-weight: 600; color: #64748b; }
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.15); }
    }
    #faceOverlay { animation: borderPulse 2s infinite; }
    @keyframes borderPulse {
      0%, 100% { border-color: #059669; opacity: 0.5; }
      50% { border-color: #059669; opacity: 1; }
    }
    #faceCaptureModal .modal-content { border-radius: 1.25rem; border: 1px solid #e2e8f0; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.2); }
    #faceCaptureModal .modal-header { border-bottom: 1px solid #e2e8f0; }
    #faceCaptureModal .modal-footer { border-top: 1px solid #e2e8f0; }
  </style>

  <!-- Bootstrap JS -->
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <!-- Face API (local for offline support) -->
  <script>window.FACE_API_MODEL_BASE = "{{ asset('vendor/face-api/model') }}";</script>
  <script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
  <script src="{{ asset('js/face-recognition.js') }}"></script>
  <script id="majorsByProgramData" type="application/json">@json(\App\Models\Student::PROGRAM_MAJORS)</script>

  <script>
  function showRegistrationPrompt(options) {
    var existing = document.getElementById('registerPromptOverlay');
    if (!existing) {
      var overlay = document.createElement('div');
      overlay.id = 'registerPromptOverlay';
      overlay.style.position = 'fixed';
      overlay.style.inset = '0';
      overlay.style.zIndex = '2200';
      overlay.style.display = 'none';
      overlay.style.alignItems = 'center';
      overlay.style.justifyContent = 'center';
      overlay.style.padding = '1rem';
      overlay.style.background = 'rgba(2, 6, 23, 0.66)';
      overlay.innerHTML = ''
        + '<div style="width:min(92vw,420px);background:#0b1a36;border-radius:12px;border:1px solid #334155;box-shadow:0 32px 80px -36px rgba(2,6,23,.9);overflow:hidden;color:#e2e8f0;">'
        + '  <div style="display:flex;align-items:center;gap:.55rem;padding:.95rem 1rem .75rem;font-weight:700;color:#f8fafc;border-bottom:1px solid #475569;">'
        + '    <span style="width:28px;height:28px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;background:rgba(59,130,246,.2);color:#93c5fd;"><i class="bi bi-bell-fill"></i></span>'
        + '    <span id="registerPromptTitle">Notice</span>'
        + '    <button type="button" id="registerPromptClose" aria-label="Close" style="margin-left:auto;width:30px;height:30px;border:none;border-radius:8px;background:transparent;color:#94a3b8;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;"><i class="bi bi-x-lg"></i></button>'
        + '  </div>'
        + '  <div id="registerPromptMessage" style="padding:1rem 1rem .85rem;color:#cbd5e1;line-height:1.48;font-size:.94rem;"></div>'
        + '  <div style="display:flex;justify-content:flex-end;gap:.55rem;padding:.8rem 1rem 1rem;border-top:1px solid #475569;background:#0b1a36;">'
        + '    <button type="button" id="registerPromptCancel" style="border:1px solid #64748b;border-radius:10px;padding:.52rem 1rem;font-size:.86rem;font-weight:600;background:#64748b;color:#f8fafc;cursor:pointer;">Cancel</button>'
        + '    <button type="button" id="registerPromptOk" style="border:1px solid #2563eb;border-radius:10px;padding:.52rem 1rem;font-size:.86rem;font-weight:600;background:#2563eb;color:#fff;cursor:pointer;"><i class="bi bi-check-lg me-1"></i> OK</button>'
        + '  </div>'
        + '</div>';
      document.body.appendChild(overlay);
      existing = overlay;
    }

    var titleEl = document.getElementById('registerPromptTitle');
    var msgEl = document.getElementById('registerPromptMessage');
    var okBtn = document.getElementById('registerPromptOk');
    var cancelBtn = document.getElementById('registerPromptCancel');
    var closeBtn = document.getElementById('registerPromptClose');
    titleEl.textContent = options.title || 'Notice';
    msgEl.textContent = options.message || '';
    cancelBtn.style.display = options.confirm ? 'inline-flex' : 'none';
    existing.style.display = 'flex';

    return new Promise(function (resolve) {
      function done(result) {
        existing.style.display = 'none';
        okBtn.removeEventListener('click', onOk);
        cancelBtn.removeEventListener('click', onCancel);
        closeBtn.removeEventListener('click', onCancel);
        document.removeEventListener('keydown', onEsc);
        resolve(result);
      }
      function onOk() { done(true); }
      function onCancel() { done(false); }
      function onEsc(e) { if (e.key === 'Escape') done(false); }
      okBtn.addEventListener('click', onOk);
      cancelBtn.addEventListener('click', onCancel);
      closeBtn.addEventListener('click', onCancel);
      document.addEventListener('keydown', onEsc);
    });
  }

  let captureInterval = null;
  let autoCaptureAttempted = false;
  let faceDetectedCount = 0;

  async function openFaceCapture() {
    // Validate form first
    const form = document.getElementById('registerForm');
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const modal = new bootstrap.Modal(document.getElementById('faceCaptureModal'));
    modal.show();
    
    // Reset states
    autoCaptureAttempted = false;
    faceDetectedCount = 0;
    resetSteps();
    document.getElementById('captureLiveness').style.display = 'none';
    document.getElementById('captureSuccess').style.display = 'none';
    document.getElementById('captureFaceBtn').style.display = 'none';
    document.getElementById('cancelBtn').disabled = true;
    
    document.getElementById('captureStatus').innerHTML = '<div class="spinner-border text-primary" role="status"></div><p class="text-muted mt-2">Loading face recognition models...</p>';
    
    // Load models
    const modelsLoaded = await faceRecognition.loadModels();
    if (!modelsLoaded) {
      document.getElementById('captureStatus').innerHTML = '<p class="text-danger"><i class="bi bi-x-circle me-2"></i>Failed to load face recognition models. Please refresh the page.</p>';
      document.getElementById('cancelBtn').disabled = false;
      return;
    }
    
    updateStep('step1', true);
    
    // Initialize camera
    const video = document.getElementById('captureVideo');
    const canvas = document.getElementById('captureCanvas');
    
    const cameraResult = await faceRecognition.initializeCamera(video, canvas);
    if (!cameraResult || !cameraResult.ok) {
      var cameraMsg = (cameraResult && cameraResult.message) ? cameraResult.message : 'Camera is unavailable.';
      document.getElementById('captureStatus').innerHTML = '<p class="text-danger"><i class="bi bi-camera-video-off me-2"></i>' + cameraMsg + '</p><p class="text-muted small">You can close this dialog and use "Continue registration without camera".</p>';
      document.getElementById('cancelBtn').disabled = false;
      return;
    }
    
    // Wait for video to be ready
    video.onloadedmetadata = () => {
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
    };
    
    // Reset liveness detection
    faceRecognition.resetLiveness();
    document.getElementById('captureBlinkCount').textContent = '0';
    document.getElementById('livenessProgress').style.width = '0%';
    document.getElementById('captureLiveness').style.display = 'block';
    document.getElementById('cancelBtn').disabled = false;
    
    // Start face detection
    document.getElementById('captureStatus').innerHTML = '<p class="text-info"><i class="bi bi-search me-2"></i>Detecting face... Please look directly at the camera.</p>';
    
    captureInterval = setInterval(async () => {
      const detection = await faceRecognition.detectFace(false, { drawLandmarks: true, detectorProfile: 'fast' });
      
      if (detection) {
        faceDetectedCount++;
        if (faceDetectedCount === 1) {
          updateStep('step2', true);
          document.getElementById('faceOverlay').style.display = 'block';
        }
        
        // Update status
        const blinkCount = faceRecognition.blinkCount;
        document.getElementById('captureBlinkCount').textContent = blinkCount;
        
        // Check liveness with detection object for stability check
        const isLive = faceRecognition.checkLiveness(detection);
        
        // Update progress bar (now based on multiple factors)
        let progress = 0;
        if (blinkCount >= 1) {
          progress = 100; // 1 blink is enough
        } else if (faceRecognition.faceDetectedCount >= 5) {
          progress = 80; // Multiple detections
        } else {
          progress = Math.min((faceRecognition.faceDetectedCount / 5) * 80, 80);
        }
        document.getElementById('livenessProgress').style.width = progress + '%';
        
        if (isLive) {
          updateStep('step3', true);
          document.getElementById('captureStatus').innerHTML = '<p class="text-success"><i class="bi bi-check-circle me-2"></i>Face verified! Capturing face...</p>';
          
          // Auto-capture after liveness is verified (reduced delay)
          if (!autoCaptureAttempted) {
            autoCaptureAttempted = true;
            setTimeout(() => {
              captureAndRegister();
            }, 500); // Reduced delay from 1000ms to 500ms
          }
        } else if (blinkCount > 0) {
          document.getElementById('captureStatus').innerHTML = '<p class="text-info"><i class="bi bi-eye me-2"></i>Face detected! Hold still for 2 seconds...</p>';
        } else {
          document.getElementById('captureStatus').innerHTML = '<p class="text-info"><i class="bi bi-check-circle me-2"></i>Face detected! Please blink once or hold still for 2 seconds.</p>';
        }
      } else {
        faceDetectedCount = 0;
        updateStep('step2', false);
        document.getElementById('faceOverlay').style.display = 'none';
        document.getElementById('captureStatus').innerHTML = '<p class="text-warning"><i class="bi bi-exclamation-triangle me-2"></i>No face detected. Please position yourself in front of the camera with good lighting.</p>';
        document.getElementById('captureBlinkCount').textContent = '0';
        document.getElementById('livenessProgress').style.width = '0%';
      }
    }, 300); // Check every 300ms for smoother experience
  }

  async function captureAndRegister() {
    if (autoCaptureAttempted && document.getElementById('faceEncodingInput').value) {
      return; // Already captured
    }

    updateStep('step4', true);
    document.getElementById('captureStatus').innerHTML = '<div class="spinner-border text-primary me-2"></div><p class="text-info d-inline">Capturing face encoding...</p>';
    document.getElementById('captureFaceBtn').disabled = true;
    document.getElementById('cancelBtn').disabled = true;
    
    try {
      const encoding = await faceRecognition.captureFaceEncoding({
        sampleCount: 3,
        intervalMs: 200,
        useCacheMs: 1200,
        drawLandmarks: false,
        detectorProfile: 'normal'
      });
      document.getElementById('faceEncodingInput').value = encoding;
      
      // Show success
      document.getElementById('captureStatus').style.display = 'none';
      document.getElementById('captureLiveness').style.display = 'none';
      document.getElementById('captureSuccess').style.display = 'block';
      
      // Submit form after short delay
      setTimeout(() => {
        document.getElementById('registerForm').submit();
      }, 1500);
      
    } catch (error) {
      console.error('Capture error:', error);
      document.getElementById('captureStatus').innerHTML = '<p class="text-danger"><i class="bi bi-x-circle me-2"></i>' + error.message + '</p>';
      document.getElementById('captureFaceBtn').disabled = false;
      document.getElementById('cancelBtn').disabled = false;
      autoCaptureAttempted = false;
      resetSteps();
    }
  }

  function stopFaceCapture() {
    if (captureInterval) {
      clearInterval(captureInterval);
      captureInterval = null;
    }
    faceRecognition.stopCamera();
    faceRecognition.resetLiveness();
    autoCaptureAttempted = false;
    faceDetectedCount = 0;
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('faceCaptureModal'));
    if (modal) {
      modal.hide();
    }
  }

  function updateStep(stepId, active) {
    const step = document.getElementById(stepId);
    if (active) {
      step.classList.add('active');
      if (stepId !== 'step4') {
        step.classList.add('completed');
      }
    } else {
      step.classList.remove('active', 'completed');
    }
  }

  function resetSteps() {
    ['step1', 'step2', 'step3', 'step4'].forEach(id => {
      document.getElementById(id).classList.remove('active', 'completed');
    });
  }

  // Clean up when modal is closed
  document.getElementById('faceCaptureModal').addEventListener('hidden.bs.modal', function () {
    stopFaceCapture();
  });

  async function submitWithoutFace() {
    const form = document.getElementById('registerForm');
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }
    const ok = await showRegistrationPrompt({
      title: 'Please Confirm',
      message: 'Continue registration without face enrollment? You can still proceed, but camera-based verification may not be available until your device issue is fixed.',
      confirm: true
    });
    if (!ok) return;
    document.getElementById('faceEncodingInput').value = '';
    form.submit();
  }

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

  (function () {
    var privacySeeMoreBtn = document.getElementById('privacyConsentSeeMoreBtn');
    if (!privacySeeMoreBtn) return;
    privacySeeMoreBtn.addEventListener('click', async function () {
      await showRegistrationPrompt({
        title: 'Educational Purpose Notice',
        message: 'This system is intended for educational purposes only. Your personal and facial data are used strictly for OJT attendance processing in line with RA 10173.'
      });
    });
  })();

  (function () {
    var stripDigits = function (v) {
      var s = String(v || '');
      try {
        return s.replace(/\p{Nd}/gu, '');
      } catch (e) {
        return s.replace(/\d/g, '');
      }
    };
    document.querySelectorAll('.register-name-field').forEach(function (input) {
      input.addEventListener('input', function () {
        var next = stripDigits(input.value);
        if (next !== input.value) {
          input.value = next;
        }
      });
    });
  })();

  (function () {
    var programSelect = document.getElementById('programSelect');
    var majorGroup = document.getElementById('majorGroup');
    var majorSelect = document.getElementById('majorSelect');
    var majorsDataEl = document.getElementById('majorsByProgramData');
    var majorsByProgram = {};
    var oldMajor = (document.getElementById('oldMajorValue') || {}).value || '';
    if (!programSelect || !majorGroup || !majorSelect) return;
    if (majorsDataEl && majorsDataEl.textContent) {
      try {
        majorsByProgram = JSON.parse(majorsDataEl.textContent);
      } catch (e) {
        majorsByProgram = {};
      }
    }

    function setMajorOptions(programValue) {
      majorGroup.classList.remove('is-hidden');
      majorSelect.innerHTML = '';
      majorSelect.removeAttribute('title');
      majorSelect.disabled = false;
      majorSelect.required = false;
      majorSelect.classList.remove('ghost-disabled');

      if (!programValue) {
        var waitOpt = document.createElement('option');
        waitOpt.value = '';
        waitOpt.textContent = 'Choose program first';
        majorSelect.appendChild(waitOpt);
        majorSelect.disabled = true;
        majorSelect.classList.add('ghost-disabled');
        majorSelect.title = 'Choose a program first.';
        return;
      }

      var majors = majorsByProgram[programValue] || [];

      if (!majors.length) {
        var noneOpt = document.createElement('option');
        noneOpt.value = '';
        noneOpt.textContent = 'No major for this program';
        majorSelect.appendChild(noneOpt);
        noneOpt.selected = true;
        majorSelect.disabled = true;
        majorSelect.classList.add('ghost-disabled');
        majorSelect.required = false;
        majorSelect.title = 'No major available for ' + programValue + '.';
        return;
      }

      var hintOpt = document.createElement('option');
      hintOpt.value = '';
      hintOpt.textContent = 'Select Major';
      majorSelect.appendChild(hintOpt);

      var matchedOldMajor = false;
      majors.forEach(function (major) {
        var opt = document.createElement('option');
        opt.value = major;
        opt.textContent = major;
        if (oldMajor && oldMajor === major) {
          opt.selected = true;
          matchedOldMajor = true;
        }
        majorSelect.appendChild(opt);
      });

      if (!matchedOldMajor) {
        hintOpt.selected = true;
      }

      majorSelect.required = true;
    }

    programSelect.addEventListener('change', function () {
      oldMajor = null;
      setMajorOptions(programSelect.value || '');
    });

    setMajorOptions(programSelect.value || '');
  })();

  (function () {
    var passwordInput = document.getElementById('passwordInput');
    var rulesContainer = document.getElementById('passwordRules');
    if (!passwordInput || !rulesContainer) return;

    var checks = {
      length: function (v) { return v.length >= 8; },
      uppercase: function (v) { return /[A-Z]/.test(v); },
      lowercase: function (v) { return /[a-z]/.test(v); },
      number: function (v) { return /[0-9]/.test(v); },
      symbol: function (v) { return /[^A-Za-z0-9]/.test(v); }
    };

    function setRuleState(ruleEl, passed) {
      var icon = ruleEl.querySelector('i');
      if (!icon) return;
      ruleEl.classList.toggle('ok', passed);
      icon.classList.remove('bi-circle', 'bi-check-circle-fill');
      icon.classList.add(passed ? 'bi-check-circle-fill' : 'bi-circle');
    }

    function updatePasswordRules() {
      var value = passwordInput.value || '';
      Object.keys(checks).forEach(function (key) {
        var el = rulesContainer.querySelector('[data-rule="' + key + '"]');
        if (!el) return;
        setRuleState(el, checks[key](value));
      });
    }

    passwordInput.addEventListener('input', updatePasswordRules);
    updatePasswordRules();
  })();

  (function () {
    var passwordInput = document.querySelector('input[name="password"]');
    var confirmInput = document.getElementById('passwordConfirmationInput');
    var indicator = document.getElementById('passwordMatchIndicator');
    if (!passwordInput || !confirmInput || !indicator) return;

    function updatePasswordMatchIndicator() {
      var password = passwordInput.value || '';
      var confirmation = confirmInput.value || '';
      indicator.classList.remove('match', 'mismatch');

      if (confirmation.length === 0) {
        indicator.innerHTML = '';
        return;
      }

      if (password === confirmation) {
        indicator.classList.add('match');
        indicator.innerHTML = '<i class="bi bi-check-circle-fill"></i><span>Passwords match</span>';
        return;
      }

      indicator.classList.add('mismatch');
      indicator.innerHTML = '<i class="bi bi-x-circle-fill"></i><span>Passwords do not match yet</span>';
    }

    passwordInput.addEventListener('input', updatePasswordMatchIndicator);
    confirmInput.addEventListener('input', updatePasswordMatchIndicator);
    updatePasswordMatchIndicator();
  })();

  (function () {
    var startInput = document.getElementById('schoolYearStart');
    var endInput = document.getElementById('schoolYearEnd');
    var hiddenSchoolYear = document.getElementById('schoolYearValue');
    if (!startInput || !endInput || !hiddenSchoolYear) return;
    var now = new Date();
    var currentYear = now.getFullYear();
    var minAllowedDate = currentYear + '-01-01';

    function attachDatePickerFocus(inputEl) {
      if (!inputEl) return;
      inputEl.addEventListener('pointerdown', function () {
        if (typeof inputEl.showPicker === 'function') {
          try { inputEl.showPicker(); } catch (e) {}
        }
      });
    }
    attachDatePickerFocus(startInput);
    attachDatePickerFocus(endInput);
    startInput.min = minAllowedDate;
    endInput.min = minAllowedDate;

    var defaultStartYear = now.getMonth() >= 5 ? now.getFullYear() : now.getFullYear();
    var defaultStartDate = defaultStartYear + '-06-01';
    var defaultEndDate = (defaultStartYear + 1) + '-05-31';

    startInput.value = defaultStartDate;
    endInput.value = defaultEndDate;

    var existing = (hiddenSchoolYear.value || '').trim();
    var match = existing.match(/^(\d{4})-(\d{4})$/);
    if (match) {
      startInput.value = match[1] + '-06-01';
      endInput.value = match[2] + '-05-31';
    }

    function syncSchoolYearValue() {
      if (!startInput.value || !endInput.value) {
        hiddenSchoolYear.value = '';
        return;
      }
      var startYear = new Date(startInput.value).getFullYear();
      var endYear = new Date(endInput.value).getFullYear();
      hiddenSchoolYear.value = startYear + '-' + endYear;
    }

    function normalizeDateRange() {
      if (startInput.value && startInput.value < minAllowedDate) {
        startInput.value = minAllowedDate;
      }
      if (endInput.value && endInput.value < minAllowedDate) {
        endInput.value = minAllowedDate;
      }
      if (startInput.value && endInput.value && endInput.value < startInput.value) {
        endInput.value = startInput.value;
      }
      syncSchoolYearValue();
    }

    normalizeDateRange();
    startInput.addEventListener('change', normalizeDateRange);
    endInput.addEventListener('change', normalizeDateRange);
  })();
  </script>
</body>
</html>
