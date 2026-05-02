<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - NORSU OJT DTR</title>
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
            backdrop-filter: blur(2px);
            z-index: 0;
        }
        .card {
            position: relative;
            z-index: 1;
            max-width: 440px;
            width: 100%;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(20px);
            border-radius: 1.25rem;
            box-shadow: 0 24px 56px -16px rgba(79,70,229,0.2);
            border: 1px solid rgba(255,255,255,0.6);
            padding: 2rem;
        }
        .card h1 { font-size: 1.35rem; font-weight: 700; margin-bottom: 0.5rem; color: #0f172a; }
        .card .subtitle { color: var(--dtr-muted); margin-bottom: 1.5rem; font-size: 0.9rem; }
        .form-label { font-weight: 600; color: #334155; }
        .form-control { border-radius: var(--dtr-radius); }
        .btn-submit {
            width: 100%;
            padding: 0.75rem 1.25rem;
            background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark));
            color: #fff;
            border: none;
            border-radius: var(--dtr-radius);
            font-weight: 600;
            font-size: 1rem;
        }
        .btn-submit:hover { color: #fff; opacity: 0.95; }
        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            color: var(--dtr-muted);
            text-decoration: none;
            font-size: 0.9rem;
        }
        .btn-back:hover { color: var(--dtr-primary); }
    </style>
</head>
<body>
    <div class="card">
        <div class="text-center mb-3">
            <i class="bi bi-key-fill" style="font-size: 2.25rem; color: var(--dtr-primary);"></i>
        </div>
        <h1 class="text-center">Change password</h1>
        <p class="subtitle text-center">Enter your current password and choose a new one (min 8 characters).</p>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('student.password.change.submit') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="current_password" class="form-label">Current password</label>
                <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required autocomplete="current-password" placeholder="Your current password">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">New password</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Min 8 characters">
            </div>
            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Confirm new password</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Same as above">
            </div>
            <button type="submit" class="btn btn-submit">
                <i class="bi bi-check2 me-2"></i>Update password
            </button>
        </form>
    </div>
</body>
</html>
