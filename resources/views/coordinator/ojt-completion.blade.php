@extends('layouts.coordinator')

@section('title', 'OJT Completion')

@push('styles')
<style>
    .ojt-page .dashboard-header { text-align: center; display: flex; justify-content: center; }
    .ojt-page .dashboard-header > div { margin: 0 auto; width: 100%; }
    .ojt-page .dashboard-header h2,
    .ojt-page .dashboard-header p { text-align: center; }
    .ojt-page .table thead th { background: var(--dtr-surface-soft); color: var(--dtr-muted); font-weight: 600; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em; padding: 0.75rem 1rem; border-bottom: 1px solid var(--dtr-border-soft); }
    .ojt-page .table td { padding: 0.75rem 1rem; vertical-align: middle; border-bottom: 1px solid var(--dtr-border-soft); }
    .ojt-page .table tbody tr:last-child td { border-bottom: none; }
    .ojt-page .table tbody tr:hover { background: var(--dtr-hover-bg); }
    .ojt-page .progress {
        height: 1.15rem;
        border-radius: 999px;
        background: #1e293b;
        border: 1px solid #334155;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.35);
        width: 100%;
    }
    .ojt-page .progress-wrap {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .ojt-page .progress-wrap .progress {
        flex: 1 1 auto;
        min-width: 90px;
    }
    .ojt-page .progress-value {
        min-width: 42px;
        text-align: right;
        font-size: 0.76rem;
        font-weight: 700;
        color: var(--dtr-text);
        font-variant-numeric: tabular-nums;
    }
    .ojt-page .progress-bar {
        font-size: 0.7rem;
        font-weight: 600;
        color: #f8fafc;
        text-shadow: 0 1px 1px rgba(0,0,0,0.45);
    }
    .ojt-page .progress-bar.bg-success {
        background: linear-gradient(90deg, #16a34a 0%, #22c55e 100%) !important;
    }
    .ojt-page .progress-bar.bg-warning {
        background: linear-gradient(90deg, #d97706 0%, #f59e0b 100%) !important;
    }
    .ojt-page .progress-bar.bg-secondary {
        background: linear-gradient(90deg, #334155 0%, #475569 100%) !important;
    }
    .ojt-page .badge-confirmed { background: #059669; color: #fff; padding: 0.35rem 0.65rem; border-radius: 6px; font-weight: 500; }
    .ojt-page .badge-reached { background: #16a34a; color: #fff; padding: 0.35rem 0.65rem; border-radius: 6px; font-weight: 500; }
    .ojt-page .badge-not-reached { background: #dc2626; color: #fff; padding: 0.35rem 0.65rem; border-radius: 6px; font-weight: 500; }
    .ojt-page .search-wrap { margin-bottom: 1rem; }
    .ojt-page .search-form { position: relative; max-width: 280px; }
    .ojt-page .search-input {
        width: 100%; padding: 0.4rem 2rem 0.4rem 2rem;
        font-size: 0.875rem;
        border: none;
        border-bottom: 2px solid var(--dtr-input-border);
        border-radius: 0;
        background: transparent;
        color: var(--dtr-text);
        transition: border-color 0.15s ease, background-color 0.15s ease;
    }
    .ojt-page .search-input::placeholder { color: var(--dtr-muted); }
    .ojt-page .search-input:hover { border-color: var(--dtr-text); }
    .ojt-page .search-input:focus {
        outline: none; border-color: var(--dtr-primary);
        background-color: rgba(37, 99, 235, 0.04);
        box-shadow: none;
    }
    .ojt-page .search-icon {
        position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%);
        color: var(--dtr-muted); font-size: 0.95rem; pointer-events: none;
    }
    .ojt-page .search-clear {
        position: absolute; right: 0.2rem; top: 50%; transform: translateY(-50%);
        width: 24px; height: 24px; border: none; border-radius: 6px;
        background: transparent; color: var(--dtr-muted);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: color 0.15s ease;
    }
    .ojt-page .search-clear:hover { background: transparent; color: var(--dtr-text); }
    .ojt-page .search-hint { font-size: 0.8rem; color: var(--dtr-muted); margin-top: 0.35rem; }
    .ojt-page .search-suggest {
        position: absolute; left: 0; right: 0; top: 100%; margin-top: 4px;
        background: var(--dtr-card-bg); border: 1px solid var(--dtr-border-soft); border-radius: 12px;
        box-shadow: var(--dtr-shadow-soft); max-height: 280px; overflow-y: auto; z-index: 100; display: none;
    }
    .ojt-page .search-suggest.is-open { display: block; }
    .ojt-page .search-suggest-item {
        display: block; width: 100%; padding: 0.65rem 1rem; text-align: left; border: none; background: none;
        font-size: 0.9rem; color: var(--dtr-text); cursor: pointer; transition: background 0.15s ease;
        border-bottom: 1px solid var(--dtr-border-soft);
    }
    .ojt-page .search-suggest-item:last-child { border-bottom: none; }
    .ojt-page .search-suggest-item:hover, .ojt-page .search-suggest-item:focus { background: var(--dtr-hover-bg); outline: none; }
    .ojt-page .search-suggest-item strong { display: block; }
    .ojt-page .search-suggest-item span { font-size: 0.8rem; color: var(--dtr-muted); }
    .ojt-page .search-suggest-empty { padding: 0.75rem 1rem; font-size: 0.85rem; color: var(--dtr-muted); }
</style>
@endpush

@section('content')
    <div class="ojt-page">
    <div class="dashboard-header">
        <div>
            <h2><i class="bi bi-patch-check me-2"></i>OJT Completion</h2>
            <p>Confirm students who have reached their required hours</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-0"><i class="bi bi-people me-2"></i>Students — Required Hours & Confirmation</h5>
            <div class="search-wrap">
                <form action="{{ route('coordinator.ojt.completion') }}" method="GET" class="search-form" role="search" id="ojtSearchForm">
                    <i class="bi bi-search search-icon" aria-hidden="true"></i>
                    <input type="text"
                           name="q"
                           id="ojtSearchInput"
                           class="search-input form-control"
                           placeholder="Search by name, student no, or course…"
                           value="{{ old('q', $search ?? '') }}"
                           autocomplete="off"
                           aria-label="Search students"
                           aria-autocomplete="list"
                           aria-controls="ojtSearchSuggest"
                           aria-expanded="false">
                    <button type="button" class="search-clear" id="ojtSearchClear" title="Clear search" aria-label="Clear search" style="display: none;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <div class="search-suggest" id="ojtSearchSuggest" role="listbox" aria-label="Search suggestions"></div>
                </form>
                
            </div>
            @if($students->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Student No</th>
                                <th>Name</th>
                                <th>Total Rendered</th>
                                <th>Required</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                                @php
                                    $activeAssignment = $student->activeTermAssignment;
                                    $total = $student->renderedHoursForAssignment($activeAssignment);
                                    $required = $student->requiredHoursForAssignment($activeAssignment);
                                    $pct = $required > 0 ? min(100, round(($total / $required) * 100)) : 0;
                                    $reached = $total >= $required;
                                    $progressClass = $pct >= 100 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-secondary');
                                    $confirmed = $student->isOjtCompletionConfirmed($activeAssignment);
                                    $confirmedBy = $activeAssignment?->confirmedBy;
                                @endphp
                                <tr>
                                    <td>{{ $student->student_no }}</td>
                                    <td>{{ $student->name }}</td>
                                    <td><strong>{{ number_format($total, 1) }}</strong> hrs</td>
                                    <td>{{ number_format($required, 1) }} hrs</td>
                                    <td style="min-width: 150px;">
                                        <div class="progress-wrap">
                                            <div class="progress">
                                                <div class="progress-bar {{ $progressClass }}" role="progressbar" data-pct="{{ $pct }}" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $pct }} percent progress"></div>
                                            </div>
                                            <span class="progress-value">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($confirmed)
                                            <span class="badge badge-confirmed"><i class="bi bi-check-circle me-1"></i>Confirmed</span>
                                        @elseif($reached)
                                            <span class="badge badge-reached"><i class="bi bi-clock me-1"></i>Reached</span>
                                        @else
                                            <span class="badge badge-not-reached">Not reached</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1 align-items-center">
                                            @if($confirmed)
                                                <a href="{{ route('coordinator.ojt.completion.certificate', $student) }}" class="btn btn-sm btn-success text-white" target="_blank" rel="noopener">
                                                    <i class="bi bi-download me-1"></i>Certificate
                                                </a>
                                                <small class="text-muted">
                                                    By {{ $confirmedBy->name ?? 'Coordinator' }} · {{ $activeAssignment?->confirmed_at?->format('M d, Y') }}
                                                </small>
                                            @elseif($reached)
                                                <form action="{{ route('coordinator.ojt.completion.confirm', $student) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-confirm" data-norsu-confirm="Confirm that {{ e($student->name) }} has completed the required {{ number_format($required, 0) }} hours?" data-norsu-variant="warning">
                                                        <i class="bi bi-patch-check me-1"></i>Confirm
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    @if(!empty($search))
                        <i class="bi bi-search d-block fs-2 mb-2" style="color: var(--dtr-muted); opacity: 0.45;"></i>
                        <p class="mb-0 fw-medium">No students match "{{ e($search) }}"</p>
                        <p class="small mt-1 mb-0">Try a different name, student number, or course, or <a href="{{ route('coordinator.ojt.completion') }}">clear the search</a> to see all students.</p>
                    @else
                        <p class="mb-0">No students in your program. Assign a program to your coordinator account to see students here.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    document.querySelectorAll('.progress-bar[data-pct]').forEach(function (el) {
        var pct = parseFloat(el.getAttribute('data-pct') || '0');
        el.style.width = pct + '%';
    });

    var form = document.getElementById('ojtSearchForm');
    var input = document.getElementById('ojtSearchInput');
    var clearBtn = document.getElementById('ojtSearchClear');
    var suggestEl = document.getElementById('ojtSearchSuggest');
    var baseUrl = '{{ route("coordinator.ojt.completion") }}';
    var suggestTimer = null;

    function updateClearVisibility() {
        clearBtn.style.display = (input.value.trim() !== '') ? 'flex' : 'none';
    }
    updateClearVisibility();

    clearBtn.addEventListener('click', function () {
        input.value = '';
        updateClearVisibility();
        suggestEl.classList.remove('is-open');
        suggestEl.innerHTML = '';
        window.location = baseUrl;
    });

    input.addEventListener('input', function () {
        updateClearVisibility();
        var q = this.value.trim();
        clearTimeout(suggestTimer);
        suggestEl.classList.remove('is-open');
        suggestEl.innerHTML = '';
        if (q.length < 2) return;
        suggestTimer = setTimeout(function () {
            fetch(baseUrl + '?q=' + encodeURIComponent(q) + '&suggest=1', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    suggestEl.innerHTML = '';
                    if (data.students && data.students.length) {
                        data.students.forEach(function (s) {
                            var btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'search-suggest-item';
                            btn.setAttribute('role', 'option');
                            btn.innerHTML = '<strong>' + escapeHtml(s.name) + '</strong><span>' + escapeHtml(s.student_no) + (s.course ? ' · ' + escapeHtml(s.course) : '') + '</span>';
                            btn.addEventListener('click', function () {
                                input.value = s.name;
                                suggestEl.classList.remove('is-open');
                                form.submit();
                            });
                            suggestEl.appendChild(btn);
                        });
                    } else {
                        var empty = document.createElement('div');
                        empty.className = 'search-suggest-empty';
                        empty.textContent = 'No matches. Try another term or press Enter to search.';
                        suggestEl.appendChild(empty);
                    }
                    suggestEl.classList.add('is-open');
                    input.setAttribute('aria-expanded', 'true');
                })
                .catch(function () {
                    suggestEl.classList.remove('is-open');
                });
        }, 280);
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            suggestEl.classList.remove('is-open');
            suggestEl.innerHTML = '';
            input.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('click', function (e) {
        if (!form.contains(e.target)) {
            suggestEl.classList.remove('is-open');
            input.setAttribute('aria-expanded', 'false');
        }
    });

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
})();
</script>
@endpush
