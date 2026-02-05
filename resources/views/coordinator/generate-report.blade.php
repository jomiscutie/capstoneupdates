<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Monthly Report - NORSU OJT DTR</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <style>
        :root {
            --dtr-primary: #2563eb;
            --dtr-primary-dark: #1d4ed8;
            --dtr-muted: #64748b;
            --dtr-surface: #ffffff;
            --dtr-border: #e2e8f0;
            --dtr-radius: 1rem;
            --dtr-radius-lg: 1.25rem;
            --dtr-shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --dtr-shadow-lg: 0 10px 40px -10px rgba(37,99,235,0.25);
            --dtr-font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --dtr-transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: url('/images/negrosorientalstateuniversity_cover.jpg') center/cover fixed no-repeat;
            font-family: var(--dtr-font);
            min-height: 100vh;
            position: relative;
            color: #0f172a;
            line-height: 1.6;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse 120% 80% at 80% 0%, rgba(37,99,235,0.06) 0%, transparent 50%),
                linear-gradient(165deg, rgba(255,255,255,0.94) 0%, rgba(248,250,252,0.96) 100%);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            z-index: 0;
        }
        .container {
            position: relative;
            z-index: 1;
            padding: clamp(1.5rem, 4vw, 2.5rem) clamp(1rem, 3vw, 1.5rem);
        }
        .card {
            border-radius: 1.5rem;
            box-shadow: 0 24px 56px -16px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.04);
            border: 1px solid rgba(255,255,255,0.5);
            overflow: hidden;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .card-header {
            background: linear-gradient(135deg, var(--dtr-primary) 0%, var(--dtr-primary-dark) 40%, #1e40af 100%);
            color: #fff;
            padding: 1.5rem 2rem;
            border: none;
            position: relative;
            overflow: hidden;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.15);
        }
        .card-header::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 65%);
            border-radius: 50%;
        }
        .card-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 1.2rem;
            position: relative;
            z-index: 1;
        }
        .card-body {
            padding: 2rem;
        }
        .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
        }
        .form-control, .form-select {
            border-radius: var(--dtr-radius);
            border: 1px solid var(--dtr-border);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark));
            border: none;
            padding: 0.75rem 1.75rem;
            font-weight: 600;
            border-radius: var(--dtr-radius);
            transition: transform var(--dtr-transition), box-shadow var(--dtr-transition);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37,99,235,0.35);
        }
        .btn-secondary {
            border-radius: var(--dtr-radius);
            padding: 0.75rem 1.75rem;
            font-weight: 500;
            border: 1px solid var(--dtr-border);
            background: #f8fafc;
            color: #334155;
            transition: transform var(--dtr-transition), background var(--dtr-transition);
        }
        .btn-secondary:hover {
            background: #f1f5f9;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="bi bi-file-earmark-pdf me-2"></i>Generate Monthly Attendance Report</h4>
                    </div>
                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                            </div>
                        @endif
                        
                        <form action="{{ route('coordinator.generate.report.submit') }}" method="POST">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="student_id" class="form-label">
                                    <i class="bi bi-person me-1"></i>Select Student
                                </label>
                                <select name="student_id" id="student_id" class="form-select" required>
                                    <option value="">-- Choose a student --</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}">
                                            {{ $student->student_no }} - {{ $student->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('student_id')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-4">
                                <label for="month" class="form-label">
                                    <i class="bi bi-calendar3 me-1"></i>Select Month
                                </label>
                                <input type="month" name="month" id="month" class="form-control" 
                                       value="{{ request('month') ?? now()->format('Y-m') }}" required>
                                @error('month')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-download me-2"></i>Generate & Download PDF
                                </button>
                                <a href="{{ route('coordinator.dashboard') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>





