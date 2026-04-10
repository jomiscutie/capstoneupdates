<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Student Registration - NORSU OJT DTR</title>
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
      top: 1.75rem;
      left: 1.75rem;
      display: flex;
      align-items: center;
      gap: 0.625rem;
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
        <input type="hidden" name="face_encoding" id="faceEncodingInput" required>
        
        <div class="form-group">
          <label class="form-label">
            <i class="bi bi-card-text"></i>Student Number
          </label>
          <div class="input-wrapper">
            <i class="bi bi-card-text input-icon"></i>
            <input type="text" name="student_no" class="form-control" value="{{ old('student_no') }}" placeholder="Enter student number (e.g. 202212345)" required />
          </div>
          <small class="login-hint d-block mt-1">Use your official student number exactly as it appears on your ID (e.g. 202212345).</small>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="bi bi-person"></i>Full Name
          </label>
          <div class="input-wrapper">
            <i class="bi bi-person input-icon"></i>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Enter full name" required />
          </div>
          <small class="login-hint d-block mt-1">Enter your complete name as recorded in NORSU (First, Middle, Last).</small>
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
          <small class="login-hint d-block mt-1">Select the program where you are officially enrolled for OJT.</small>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="bi bi-calendar3"></i>Term
          </label>
          <div class="input-wrapper">
            <i class="bi bi-calendar3 input-icon"></i>
            <select name="term" class="form-select" required>
              <option value="">Select Term</option>
              @foreach(\App\Models\Student::TERMS as $termOption)
              <option value="{{ $termOption }}" {{ old('term') == $termOption ? 'selected' : '' }}>{{ $termOption }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="bi bi-journal-text"></i>School Year
          </label>
          <div class="input-wrapper">
            <i class="bi bi-journal-text input-icon"></i>
            <input type="text" name="school_year" class="form-control" value="{{ old('school_year') }}" placeholder="Example: 2026-2027" pattern="\d{4}-\d{4}" inputmode="numeric" required />
          </div>
          <small class="login-hint d-block mt-1">Use the school year shown on your load slip, for example 2026-2027.</small>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="bi bi-grid-3x3-gap"></i>Section
            <div class="form-text">
            </div>
          </label>
          <div class="input-wrapper">
            <i class="bi bi-grid-3x3-gap input-icon"></i>
            <select name="section" class="form-select" required>
              <option value="">Select Section</option>
              @foreach(\App\Models\Student::SECTIONS as $sectionOption)
              <option value="{{ $sectionOption }}" {{ old('section') == $sectionOption ? 'selected' : '' }}>Section {{ $sectionOption }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="bi bi-lock"></i>Password
          </label>
          <div class="input-wrapper has-password-toggle">
            <i class="bi bi-key input-icon"></i>
            <input type="password" name="password" class="form-control" placeholder="Enter password" minlength="8" required />
            <button type="button" class="password-toggle-btn" data-password-toggle aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
          </div>
          <small class="login-hint d-block mt-1">Use at least 8 characters with a mix of letters and numbers for better security.</small>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="bi bi-lock-fill"></i>Confirm Password
          </label>
          <div class="input-wrapper has-password-toggle">
            <i class="bi bi-key-fill input-icon"></i>
            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm password" minlength="8" required />
            <button type="button" class="password-toggle-btn" data-password-toggle aria-label="Show password" title="Show password"><i class="bi bi-eye"></i></button>
          </div>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-4);">
          <div class="form-check text-start" style="font-size: 0.8rem; color: var(--login-muted);">
            <input class="form-check-input me-2" type="checkbox" value="1" id="privacyConsent" required>
            <label class="form-check-label" for="privacyConsent">
              I understand that my personal information and captured facial data will be used for <strong>educational and OJT attendance purposes only</strong>, kept securely inside NORSU's OJT DTR system, and handled in accordance with the Data Privacy Act of 2012 (RA 10173).
            </label>
          </div>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-4);">
          <div class="form-check text-start" style="font-size: 0.8rem; color: var(--login-muted);">
            <input class="form-check-input me-2" type="checkbox" value="1" id="honestyConsent" required>
            <label class="form-check-label" for="honestyConsent">
              I agree to use this system honestly and only for my own attendance and OJT requirements, and I will not allow others to use my account or facial data.
            </label>
          </div>
        </div>

        <button type="button" class="btn btn-register" onclick="openFaceCapture()">
          <i class="bi bi-camera me-2"></i>Register with Face Recognition
        </button>
      </form>

      <div class="login-link">
        <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
      </div>

      <div class="switch-login">
        <div class="divider">
          <span>Need coordinator access?</span>
        </div>
        <a href="{{ route('login') }}" class="btn-switch">
          <i class="bi bi-person-gear me-2"></i>
          Contact Admin for Coordinator Account
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
          <div class="alert alert-info mb-3 text-start" style="font-size: 0.85rem;">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Face registration steps:</strong>
            <ol class="mt-2 mb-0 ps-4">
              <li>Allow camera access when prompted.</li>
              <li>Look directly at the camera with good lighting.</li>
              <li>Blink naturally 2-3 times and hold your head steady.</li>
              <li>Wait for the system to confirm that your face has been captured.</li>
            </ol>
          </div>
          <div class="alert alert-secondary mb-3 text-start" style="font-size: 0.85rem;">
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
      margin: 1.5rem 0;
      padding: 1rem 1.25rem;
      background: #f8fafc;
      border-radius: 1rem;
      border: 1px solid #e2e8f0;
    }
    .step {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.5rem;
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
    .step span { font-size: 0.75rem; font-weight: 600; color: #64748b; }
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

  <script>
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
    
    const cameraReady = await faceRecognition.initializeCamera(video, canvas);
    if (!cameraReady) {
      document.getElementById('captureStatus').innerHTML = '<p class="text-danger"><i class="bi bi-camera-video-off me-2"></i>Camera access denied. Please allow camera permissions and refresh.</p>';
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
      const detection = await faceRecognition.detectFace();
      
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
      const encoding = await faceRecognition.captureFaceEncoding();
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
