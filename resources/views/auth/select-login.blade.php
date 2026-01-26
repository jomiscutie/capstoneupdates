<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Login - NORSU OJT DTR</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
            position: relative;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            padding: 2rem 1rem;
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
            max-width: 900px;
        }

        .header-section {
            text-align: center;
            margin-bottom: 3rem;
            color: #212529;
        }

        .header-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #0d6efd;
        }

        .header-section p {
            font-size: 1.1rem;
            color: #6c757d;
        }

        .login-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .login-card {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .login-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            border-color: rgba(13, 110, 253, 0.3);
        }

        .login-card.student:hover {
            border-color: rgba(25, 135, 84, 0.3);
        }

        .login-card.coordinator:hover {
            border-color: rgba(13, 110, 253, 0.3);
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            transition: all 0.3s ease;
        }

        .login-card.student .icon-wrapper {
            background: linear-gradient(135deg, #198754, #20c997);
            color: white;
        }

        .login-card.coordinator .icon-wrapper {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            color: white;
        }

        .login-card:hover .icon-wrapper {
            transform: scale(1.1);
        }

        .login-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #212529;
        }

        .login-card p {
            font-size: 0.95rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .login-btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
        }

        .login-card.student .login-btn {
            background: #198754;
            color: white;
        }

        .login-card.student .login-btn:hover {
            background: #157347;
            color: white;
        }

        .login-card.coordinator .login-btn {
            background: #0d6efd;
            color: white;
        }

        .login-card.coordinator .login-btn:hover {
            background: #0b5ed7;
            color: white;
        }

        @media (max-width: 768px) {
            .header-section h1 {
                font-size: 2rem;
            }

            .login-cards {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .login-card {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="header-section">
            <h1>Welcome to NORSU OJT DTR</h1>
            <p>Please select your login type to continue</p>
        </div>

        <div class="login-cards">
            <a href="{{ route('student.login') }}" class="login-card student">
                <div class="icon-wrapper">
                    <i class="bi bi-person-badge"></i>
                </div>
                <h3>Student Login</h3>
                <p>Access your attendance records, time in/out, and view your daily logs</p>
                <span class="login-btn">Login as Student</span>
            </a>

            <a href="{{ route('coordinator.login') }}" class="login-card coordinator">
                <div class="icon-wrapper">
                    <i class="bi bi-person-gear"></i>
                </div>
                <h3>Coordinator Login</h3>
                <p>Manage student attendance, view reports, and monitor daily activities</p>
                <span class="login-btn">Login as Coordinator</span>
            </a>
        </div>
    </div>
</body>
</html>
