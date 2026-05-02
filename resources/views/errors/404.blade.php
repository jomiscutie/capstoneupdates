<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - NORSU OJT DTR</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(165deg, #f8fafc 0%, #f1f5f9 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 1rem;
        }
        .error-card {
            max-width: 420px;
            text-align: center;
            padding: 2rem;
        }
        .error-code { font-size: 4rem; font-weight: 800; color: #4f46e5; line-height: 1; }
        .error-title { font-size: 1.25rem; font-weight: 600; color: #1e293b; margin: 1rem 0 0.5rem; }
        .error-message { color: #64748b; margin-bottom: 1.5rem; }
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: #fff;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-home:hover { color: #fff; opacity: 0.95; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">404</div>
        <h1 class="error-title">Page not found</h1>
        <p class="error-message">The page you're looking for doesn't exist or has been moved.</p>
        <a href="{{ url('/') }}" class="btn-home">
            <i class="bi bi-house-door"></i> Back to home
        </a>
    </div>
</body>
</html>
