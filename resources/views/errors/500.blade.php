<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Something went wrong - NORSU OJT DTR</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(165deg, #fef2f2 0%, #fee2e2 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 1rem;
        }
        .error-card {
            max-width: 420px;
            text-align: center;
            padding: 2rem;
        }
        .error-code { font-size: 4rem; font-weight: 800; color: #dc2626; line-height: 1; }
        .error-title { font-size: 1.25rem; font-weight: 600; color: #1e293b; margin: 1rem 0 0.5rem; }
        .error-message { color: #64748b; margin-bottom: 0.75rem; }
        .error-details { color: #9ca3af; font-size: 0.9rem; margin-bottom: 1.5rem; }
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
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
        <div class="error-code">500</div>
        <h1 class="error-title">Something went wrong</h1>
        <p class="error-message">We're sorry. An unexpected error occurred on our side.</p>
        <p class="error-details">
            Error code 500 means an internal server error. This is not your fault – the server
            failed while processing your request. You can try again in a moment. If the problem
            keeps happening, please check your server/database connection or contact your
            coordinator or system administrator.
        </p>
        <a href="{{ url('/') }}" class="btn-home">
            <i class="bi bi-house-door"></i> Back to home
        </a>
    </div>
</body>
</html>
