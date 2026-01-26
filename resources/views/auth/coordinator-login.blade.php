<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Coordinator Login - NORSU OJT DTR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background-color: #f4f6f8;
      background-image: url('/images/negrosorientalstateuniversity_cover.jpg');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      background-repeat: no-repeat;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      position: relative;
      padding: 1rem;
    }

    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.65);
      z-index: 0;
    }

    .login-container {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 450px;
    }

    .login-card {
      background: #ffffff;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      padding: 3rem 2.5rem;
      transition: all 0.3s ease;
      backdrop-filter: blur(10px);
    }

    .login-card:hover {
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
      transform: translateY(-2px);
    }

    .login-header {
      text-align: center;
      margin-bottom: 2.5rem;
    }

    .login-header .icon-wrapper {
      width: 70px;
      height: 70px;
      margin: 0 auto 1.5rem;
      background: linear-gradient(135deg, #357ABD 0%, #2c5ca8 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 20px rgba(53, 122, 189, 0.3);
    }

    .login-header .icon-wrapper i {
      font-size: 2rem;
      color: white;
    }

    .login-header h1 {
      font-size: 1.75rem;
      font-weight: 700;
      color: #2d3748;
      margin-bottom: 0.5rem;
    }

    .login-header p {
      color: #718096;
      font-size: 0.95rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
      position: relative;
    }

    .form-label {
      font-weight: 600;
      color: #4a5568;
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
      display: block;
    }

    .form-label i {
      margin-right: 0.5rem;
      color: #357ABD;
    }

    .input-wrapper {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #a0aec0;
      font-size: 1.1rem;
      z-index: 2;
    }

    .form-control,
    .form-select {
      width: 100%;
      padding: 0.875rem 1rem 0.875rem 2.75rem;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      font-size: 0.95rem;
      transition: all 0.3s ease;
      background-color: #f7fafc;
    }

    .form-select {
      padding-left: 2.75rem;
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: right 0.75rem center;
      background-size: 16px 12px;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #357ABD;
      background-color: #ffffff;
      box-shadow: 0 0 0 4px rgba(53, 122, 189, 0.1);
      outline: none;
    }

    .form-control::placeholder {
      color: #a0aec0;
    }

    .form-text {
      font-size: 0.8rem;
      color: #718096;
      margin-top: 0.5rem;
    }

    .btn-login {
      width: 100%;
      padding: 0.875rem;
      background: linear-gradient(135deg, #357ABD 0%, #2c5ca8 100%);
      border: none;
      border-radius: 12px;
      color: white;
      font-weight: 600;
      font-size: 1rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(53, 122, 189, 0.4);
      margin-top: 0.5rem;
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(53, 122, 189, 0.5);
    }

    .btn-login:active {
      transform: translateY(0);
    }

    .register-link {
      text-align: center;
      margin-top: 2rem;
      padding-top: 1.5rem;
      border-top: 1px solid #e2e8f0;
    }

    .register-link p {
      color: #718096;
      font-size: 0.9rem;
      margin: 0;
    }

    .register-link a {
      color: #357ABD;
      font-weight: 600;
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .register-link a:hover {
      color: #2c5ca8;
      text-decoration: underline;
    }

    .alert {
      border-radius: 12px;
      font-size: 0.9rem;
      margin-bottom: 1.5rem;
      border: none;
      padding: 1rem 1.25rem;
    }

    .alert-danger {
      background-color: #fed7d7;
      color: #c53030;
    }

    .alert-success {
      background-color: #c6f6d5;
      color: #22543d;
    }

    .switch-login {
      margin-top: 1.5rem;
      text-align: center;
    }

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
      background: #e2e8f0;
    }

    .divider span {
      position: relative;
      background: white;
      padding: 0 1rem;
      color: #718096;
      font-size: 0.85rem;
    }

    .btn-switch {
      display: inline-flex;
      align-items: center;
      padding: 0.75rem 1.5rem;
      background: #f7fafc;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      color: #4a5568;
      font-weight: 600;
      font-size: 0.9rem;
      text-decoration: none;
      transition: all 0.3s ease;
      width: 100%;
      justify-content: center;
    }

    .btn-switch:hover {
      background: #edf2f7;
      border-color: #cbd5e0;
      color: #2d3748;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .btn-switch i {
      font-size: 1.1rem;
      color: #357ABD;
    }

    @media (max-width: 576px) {
      .login-card {
        padding: 2rem 1.5rem;
      }

      .login-header h1 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="icon-wrapper">
          <i class="bi bi-person-gear"></i>
        </div>
        <h1>Coordinator Login</h1>
        <p>Welcome back! Please login to continue</p>
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

      <form action="{{ route('coordinator.login.submit') }}" method="POST">
        @csrf
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
          <small class="form-text">Please select your program before logging in</small>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="bi bi-envelope"></i>Email Address
          </label>
          <div class="input-wrapper">
            <i class="bi bi-envelope-at input-icon"></i>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Enter your email address" required />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="bi bi-lock"></i>Password
          </label>
          <div class="input-wrapper">
            <i class="bi bi-key input-icon"></i>
            <input type="password" name="password" class="form-control" placeholder="Enter your password" required />
          </div>
        </div>

        <button type="submit" class="btn btn-login">
          <i class="bi bi-box-arrow-in-right me-2"></i>Login
        </button>
      </form>

      <div class="register-link">
        <p>Don't have an account? <a href="{{ route('coordinator.register') }}">Register here</a></p>
      </div>

      <div class="switch-login">
        <div class="divider">
          <span>OR</span>
        </div>
        <a href="{{ route('student.login') }}" class="btn-switch">
          <i class="bi bi-person-badge me-2"></i>
          Login as Student
        </a>
      </div>
    </div>
  </div>
</body>
</html>