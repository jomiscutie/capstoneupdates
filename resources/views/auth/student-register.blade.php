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
      --dtr-primary: #4f46e5;
      --dtr-primary-dark: #4338ca;
      --dtr-muted: #64748b;
      --dtr-surface: #ffffff;
      --dtr-border: #e2e8f0;
      --dtr-radius: 1rem;
      --dtr-radius-lg: 1.25rem;
      --dtr-shadow-md: 0 4px 12px rgba(0,0,0,0.08);
      --dtr-shadow-lg: 0 10px 40px -10px rgba(79,70,229,0.25);
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
      background: radial-gradient(ellipse 100% 70% at 30% 20%, rgba(79,70,229,0.08) 0%, transparent 50%),
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
      box-shadow: 0 24px 56px -16px rgba(79,70,229,0.2), 0 0 0 1px rgba(255,255,255,0.5), inset 0 1px 0 rgba(255,255,255,0.9);
      padding: clamp(2rem, 4vw, 3rem) clamp(1.5rem, 4vw, 2.5rem);
      transition: transform 0.35s cubic-bezier(0.22,1,0.36,1), box-shadow 0.35s ease;
      border: 1px solid rgba(255,255,255,0.6);
    }
    .register-card:hover {
      box-shadow: 0 28px 60px -16px rgba(79,70,229,0.28), 0 0 0 1px rgba(255,255,255,0.6);
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
      box-shadow: 0 8px 28px rgba(79,70,229,0.4), inset 0 1px 0 rgba(255,255,255,0.3);
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
      box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
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
      box-shadow: 0 4px 18px rgba(79,70,229,0.4), inset 0 1px 0 rgba(255,255,255,0.2);
      margin-top: 0.5rem;
      cursor: pointer;
    }
    .btn-register:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 28px -4px rgba(79,70,229,0.5), inset 0 1px 0 rgba(255,255,255,0.2);
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
          <i class="bi bi-person-badge"></i>
        </div>
        <h1>Student Registration</h1>
        <p>Create your account to get started</p>
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
            <input type="text" name="student_no" class="form-control" value="{{ old('student_no') }}" placeholder="Enter student number" required />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="bi bi-person"></i>Full Name
          </label>
          <div class="input-wrapper">
            <i class="bi bi-person input-icon"></i>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Enter full name" required />
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
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="bi bi-lock"></i>Password
          </label>
          <div class="input-wrapper">
            <i class="bi bi-key input-icon"></i>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="bi bi-lock-fill"></i>Confirm Password
          </label>
          <div class="input-wrapper">
            <i class="bi bi-key-fill input-icon"></i>
            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm password" required />
          </div>
        </div>

        <button type="button" class="btn btn-register" onclick="openFaceCapture()">
          <i class="bi bi-camera me-2"></i>Register with Face Recognition
        </button>
      </form>

      <div class="login-link">
        <p>Already have an account? <a href="{{ route('student.login') }}">Login here</a></p>
      </div>

      <div class="switch-login">
        <div class="divider">
          <span>OR</span>
        </div>
        <a href="{{ route('coordinator.register') }}" class="btn-switch">
          <i class="bi bi-person-gear me-2"></i>
          Register as Coordinator
        </a>
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
          <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Instructions:</strong> Look directly at the camera, ensure good lighting, and blink naturally 2-3 times. 
            Your face will be automatically captured when ready.
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
  </script>
</body>
</html>