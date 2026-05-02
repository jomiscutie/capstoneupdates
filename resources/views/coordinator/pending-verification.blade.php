@extends('layouts.coordinator')

@section('title', 'Verify Students')

@push('styles')
<style>
    .verify-page .back-link { margin-bottom: 0.5rem; }
    .verify-page .page-title { font-size: 1.5rem; font-weight: 600; color: var(--dtr-text); margin-bottom: 0.25rem; text-align: center; }
    .verify-page .page-sub { font-size: 0.9rem; color: var(--dtr-muted); margin: 0 auto 1.5rem; text-align: center; max-width: 760px; }
    .verify-page .card-body { padding: 1.5rem; }
    .verify-page .section-title { font-size: 1rem; font-weight: 600; color: var(--dtr-text); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .verify-page .section-title i { color: var(--dtr-primary); }
    .verify-page .table { font-size: 0.9rem; }
    .verify-page .table thead th {
        background: var(--dtr-surface-soft);
        color: var(--dtr-muted);
        font-weight: 600;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--dtr-border-soft);
    }
    .verify-page .table tbody td { padding: 0.875rem 1rem; vertical-align: middle; border-bottom: 1px solid var(--dtr-border-soft); overflow-wrap: break-word; word-break: break-word; }
    .verify-page .table tbody tr:last-child td { border-bottom: none; }
    .verify-page .table tbody tr:hover { background: var(--dtr-hover-bg); }
    .verify-page .empty-state { text-align: center; padding: 2.5rem 1.5rem; color: var(--dtr-muted); }
    .verify-page .empty-state i { font-size: 2.5rem; color: var(--dtr-muted); opacity: 0.45; margin-bottom: 0.75rem; display: block; }
    /* Search bar */
    .verify-page .search-wrap { margin-bottom: 1rem; width: 100%; max-width: min(560px, 100%); }
    .verify-page .search-form { position: relative; width: 100%; max-width: 100%; }
    .verify-page .search-input {
        width: 100%;
        min-height: 40px;
        padding: 0.5rem 2.5rem 0.5rem 2.75rem;
        font-size: 0.9rem;
        border: 1.5px solid var(--dtr-input-border);
        border-radius: 10px;
        background: var(--dtr-input-bg);
        color: var(--dtr-text);
        transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    }
    .verify-page .search-input::placeholder { color: var(--dtr-muted); }
    .verify-page .search-input:focus { outline: none; border-color: var(--dtr-primary); box-shadow: 0 0 0 2px color-mix(in srgb, var(--dtr-primary) 22%, transparent); }
    .verify-page .search-icon { position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); color: var(--dtr-muted); font-size: 0.95rem; pointer-events: none; }
    .verify-page .search-clear { position: absolute; right: 0.2rem; top: 50%; transform: translateY(-50%); width: 24px; height: 24px; border: none; border-radius: 6px; background: transparent; color: var(--dtr-muted); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: color 0.15s ease; }
    .verify-page .search-clear:hover { background: transparent; color: var(--dtr-text); }
    .verify-page .search-hint { font-size: 0.8rem; color: var(--dtr-muted); margin-top: 0.35rem; }
    .verify-page .search-suggest { position: absolute; left: 0; right: 0; top: 100%; margin-top: 4px; background: var(--dtr-card-bg); border: 1px solid var(--dtr-border-soft); border-radius: 8px; box-shadow: var(--dtr-shadow-soft); max-height: 280px; overflow-y: auto; z-index: 100; display: none; }
    .verify-page .search-suggest.is-open { display: block; }
    .verify-page .search-suggest-item { display: block; width: 100%; padding: 0.6rem 1rem; text-align: left; border: none; background: none; font-size: 0.9rem; color: var(--dtr-text); cursor: pointer; transition: background 0.15s ease; border-bottom: 1px solid var(--dtr-border-soft); }
    .verify-page .search-suggest-item:last-child { border-bottom: none; }
    .verify-page .search-suggest-item:hover, .verify-page .search-suggest-item:focus { background: var(--dtr-hover-bg); outline: none; }
    .verify-page .search-suggest-item strong { display: block; }
    .verify-page .search-suggest-item span { font-size: 0.8rem; color: var(--dtr-muted); }
    .verify-page .search-suggest-empty { padding: 0.7rem 1rem; font-size: 0.85rem; color: var(--dtr-muted); }
</style>
@endpush

@section('content')
<div class="verify-page">
    <h1 class="page-title">Verify Students</h1>
    <p class="page-sub">Confirm that students who registered under your program belong to your class. Only verified students can log in and record attendance.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <h2 class="section-title"><i class="bi bi-people"></i> Pending verification</h2>
            <div class="search-wrap">
                <form action="{{ route('coordinator.pending.verification') }}" method="GET" class="search-form" role="search" id="verifySearchForm">
                    <i class="bi bi-search search-icon" aria-hidden="true"></i>
                    <input type="text"
                           name="q"
                           id="verifySearchInput"
                           class="search-input form-control"
                           placeholder="Search by name, student no, or course…"
                           value="{{ old('q', $search ?? '') }}"
                           autocomplete="off"
                           aria-label="Search pending students"
                           aria-autocomplete="list"
                           aria-controls="verifySearchSuggest"
                           aria-expanded="false">
                    <button type="button" class="search-clear" id="verifySearchClear" title="Clear search" aria-label="Clear search" style="display: none;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <div class="search-suggest" id="verifySearchSuggest" role="listbox" aria-label="Search suggestions"></div>
                </form>
                
            </div>
            @if($pending->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Student No</th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Face Enrollment</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pending as $student)
                                <tr>
                                    <td><span class="fw-medium">{{ $student->student_no }}</span></td>
                                    <td>{{ $student->name }}</td>
                                    <td><span class="text-muted">{{ $student->course }}</span></td>
                                    <td>
                                        @if(!empty($student->face_encoding))
                                            <span class="badge bg-success">Enrolled</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Missing</span>
                                        @endif
                                    </td>
                                    <td><span class="text-muted small">{{ $student->created_at?->format('M d, Y g:i A') }}</span></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <form action="{{ route('coordinator.pending.verification.verify', $student) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-verify" data-norsu-confirm="Verify that {{ e($student->name) }} belongs to your class? They will then be able to log in and record attendance.">
                                                    <i class="bi bi-check-circle"></i> Verify
                                                </button>
                                            </form>
                                            <form action="{{ route('coordinator.pending.verification.reject', $student) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-reject" data-norsu-confirm="Reject {{ e($student->name) }}? They will not be able to log in. You can verify them later if needed." data-norsu-variant="danger">
                                                    <i class="bi bi-x-circle"></i> Reject
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    @if(!empty($search))
                        <i class="bi bi-search"></i>
                        <p class="mb-0 fw-medium">No students match "{{ e($search) }}"</p>
                        <p class="small mt-1 mb-0">Try a different name, student number, or course, or <a href="{{ route('coordinator.pending.verification') }}">clear the search</a> to see all pending.</p>
                    @else
                        <i class="bi bi-inbox"></i>
                        <p class="mb-0 fw-medium">No students pending verification</p>
                        <p class="small mt-1 mb-0">When students register and choose your program, they will appear here for you to verify or reject.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@push('scripts')
<script>
(function () {
    var form = document.getElementById('verifySearchForm');
    var input = document.getElementById('verifySearchInput');
    var clearBtn = document.getElementById('verifySearchClear');
    var suggestEl = document.getElementById('verifySearchSuggest');
    var baseUrl = '{{ route("coordinator.pending.verification") }}';
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
@endsection
