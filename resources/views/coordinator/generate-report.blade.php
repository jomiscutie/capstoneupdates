<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Monthly Report - NORSU OJT DTR</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            background-image: url('/images/negrosorientalstateuniversity_cover.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            position: relative;
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
        
        .container {
            position: relative;
            z-index: 1;
            padding: 2rem 0;
        }
        
        .card {
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            border: none;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #357ABD 0%, #2c5ca8 100%);
            color: white;
            padding: 1.5rem 2rem;
            border: none;
        }
        
        .card-header h4 {
            margin: 0;
            font-weight: 600;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #357ABD 0%, #2c5ca8 100%);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 8px;
            transition: transform 0.2s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(53, 122, 189, 0.3);
        }
        
        .btn-secondary {
            border-radius: 8px;
            padding: 0.75rem 2rem;
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




