@extends('layouts.coordinator')

@section('title', 'Generate Monthly Report')

@push('styles')
<style>
    .dtr-report .card { border-radius: 1.5rem; box-shadow: 0 24px 56px -16px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.04); border: 1px solid rgba(255,255,255,0.5); background: rgba(255,255,255,0.85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); overflow: hidden; }
    .dtr-report .card-header { background: linear-gradient(135deg, var(--dtr-primary) 0%, var(--dtr-primary-dark) 40%, #1e40af 100%); color: #fff; padding: 1.5rem 2rem; border: none; position: relative; overflow: hidden; box-shadow: inset 0 1px 0 rgba(255,255,255,0.15); }
    .dtr-report .card-header::before { content: ''; position: absolute; top: -20%; right: -10%; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 65%); border-radius: 50%; }
    .dtr-report .card-header h4 { margin: 0; font-weight: 600; font-size: 1.2rem; position: relative; z-index: 1; }
    .dtr-report .card-body { padding: 2rem; }
    .dtr-report .form-label { font-weight: 600; color: #334155; margin-bottom: 0.5rem; }
    .dtr-report .form-control, .dtr-report .form-select { border-radius: var(--dtr-radius); border: 1px solid var(--dtr-border); }
    .dtr-report .btn-primary { background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark)); border: none; padding: 0.75rem 1.75rem; font-weight: 600; border-radius: var(--dtr-radius); transition: transform var(--dtr-transition), box-shadow var(--dtr-transition); }
    .dtr-report .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37,99,235,0.35); }
    .dtr-report .btn-secondary { border-radius: var(--dtr-radius); padding: 0.75rem 1.75rem; font-weight: 500; border: 1px solid var(--dtr-border); background: #f8fafc; color: #334155; transition: transform var(--dtr-transition), background var(--dtr-transition); }
    .dtr-report .btn-secondary:hover { background: #f1f5f9; transform: translateY(-1px); }
</style>
@endpush

@section('content')
<div class="dtr-report">
    <div class="dashboard-header">
        <div>
            <a href="{{ route('coordinator.dashboard') }}" class="back-link mb-2 d-inline-block">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
            <h2><i class="bi bi-file-earmark-pdf me-2"></i>Generate Monthly Report</h2>
            <p>Select a student and month to download the attendance report as PDF</p>
        </div>
        @if(auth()->guard('coordinator')->user()->major)
            <div class="program-badge-inline">
                <i class="bi bi-mortarboard me-1"></i><strong>Program:</strong> {{ auth()->guard('coordinator')->user()->major }}
            </div>
        @endif
    </div>

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
                            <label for="student_id" class="form-label"><i class="bi bi-person me-1"></i>Select Student</label>
                            <select name="student_id" id="student_id" class="form-select" required>
                                <option value="">-- Choose a student --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->student_no }} - {{ $student->name }}</option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="month" class="form-label"><i class="bi bi-calendar3 me-1"></i>Select Month</label>
                            <input type="month" name="month" id="month" class="form-control" value="{{ request('month') ?? now()->format('Y-m') }}" required>
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
@endsection
