@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('content')
<h1 class="page-title">Audit Logs</h1>
<p class="page-sub text-center">Chronological record of sensitive actions for compliance and accountability.</p>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Actor</th>
                        <th>Action</th>
                        <th>Target</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at?->format('M d, Y g:i A') }}</td>
                        <td>{{ $log->actor_label ?? (strtoupper($log->actor_type).' #'.($log->actor_id ?? '-')) }}</td>
                        <td><code>{{ $log->action }}</code></td>
                        <td>{{ $log->target_label ?? (($log->target_type ?? '-').' '.($log->target_id ? '#'.$log->target_id : '')) }}</td>
                        <td>{{ $log->details ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-muted">No audit logs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
