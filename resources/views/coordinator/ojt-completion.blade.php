@extends('layouts.coordinator')

@section('title', 'OJT Completion')

@push('styles')
<style>
    .ojt-page .dashboard-header { text-align: center; display: flex; justify-content: center; }
    .ojt-page .dashboard-header > div { margin: 0 auto; width: 100%; }
    .ojt-page .dashboard-header h2,
    .ojt-page .dashboard-header p { text-align: center; }
    .ojt-page .ojt-completion-table thead th {
        background: var(--dtr-surface-soft);
        color: color-mix(in srgb, var(--dtr-heading) 85%, var(--dtr-primary) 15%);
        font-weight: 700;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--dtr-border-soft);
        white-space: nowrap;
    }
    html[data-theme="dark"] .ojt-page .ojt-completion-table thead th {
        color: color-mix(in srgb, var(--dtr-heading) 92%, var(--dtr-primary) 8%);
    }
    .ojt-page .ojt-completion-table td { padding: 0.75rem 1rem; vertical-align: middle; border-bottom: 1px solid var(--dtr-border-soft); }
    .ojt-page .ojt-completion-table tbody tr:last-child td { border-bottom: none; }
    .ojt-page .ojt-completion-table tbody tr:hover { background: var(--dtr-hover-bg); }
    .ojt-page .progress.ojt-progress-track {
        height: 0.625rem;
        border-radius: 999px;
        border: none;
        width: 100%;
        overflow: hidden;
        background: color-mix(in srgb, var(--dtr-surface-2) 82%, var(--dtr-muted) 18%);
        box-shadow:
            inset 0 1px 2px rgba(15, 23, 42, 0.12),
            inset 0 -1px 1px rgba(255, 255, 255, 0.35);
    }
    html[data-theme="dark"] .ojt-page .progress.ojt-progress-track {
        background: color-mix(in srgb, var(--dtr-card-bg) 52%, #0f172a 48%);
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.55);
    }
    .ojt-page .progress-wrap {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }
    .ojt-page .progress-wrap .progress { flex: 1 1 auto; min-width: 96px; }
    .ojt-page .progress-value {
        min-width: 2.75rem;
        text-align: right;
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--dtr-heading);
        font-variant-numeric: tabular-nums;
    }
    html[data-theme="dark"] .ojt-page .progress-value { color: var(--dtr-heading); }
    .ojt-page .progress-bar.ojt-progress-fill {
        height: 100%;
        border-radius: inherit;
        transition: width 0.35s ease;
        box-shadow: none;
        min-width: 0;
        background-image: none;
    }
    .ojt-page .progress-bar.ojt-progress-fill--complete {
        background: linear-gradient(90deg, #047857 0%, #059669 40%, #10b981 100%);
        box-shadow: 0 0 6px color-mix(in srgb, #10b981 38%, transparent);
    }
    html[data-theme="dark"] .ojt-page .progress-bar.ojt-progress-fill--complete {
        background: linear-gradient(90deg, #065f46 0%, #059669 45%, #34d399 100%);
        box-shadow: 0 0 8px color-mix(in srgb, #34d399 22%, transparent);
    }
    .ojt-page .progress-bar.ojt-progress-fill--in-progress {
        background: linear-gradient(90deg, #15803d 0%, #16a34a 55%, #22c55e 100%);
    }
    html[data-theme="dark"] .ojt-page .progress-bar.ojt-progress-fill--in-progress {
        background: linear-gradient(90deg, #166534 0%, #15803d 50%, #4ade80 100%);
    }
    .ojt-page .progress-bar.ojt-progress-fill--empty {
        background: transparent;
    }
    .ojt-page .ojt-status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.38rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.69rem;
        letter-spacing: 0.045em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .ojt-page .badge-confirmed.ojt-status-pill {
        background: #059669;
        color: #fff;
        box-shadow: 0 1px 2px rgba(5, 150, 105, 0.25);
    }
    .ojt-page .badge-reached.ojt-status-pill {
        background: #16a34a;
        color: #fff;
        box-shadow: 0 1px 2px rgba(22, 163, 74, 0.25);
    }
    .ojt-page .badge-not-reached.ojt-status-pill {
        background: #c0392b;
        color: #fff;
        box-shadow: 0 1px 2px rgba(192, 57, 43, 0.28);
    }

    /* Action column — minimalist tonal buttons (overrides Bootstrap + classic-ui) */
    .layout-wrap .main-content .ojt-page .ojt-action-stack {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
    }
    .layout-wrap .main-content .ojt-page .ojt-completion-table td .ojt-action-stack .ojt-action-meta {
        display: block;
        max-width: 15rem;
        margin: 0;
        text-align: center;
        font-size: 0.72rem;
        line-height: 1.35;
        color: var(--dtr-muted) !important;
    }
    .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.45rem !important;
        min-height: 35px !important;
        padding: 0.35rem 0.95rem !important;
        border-radius: 10px !important;
        font-size: 0.8125rem !important;
        font-weight: 600 !important;
        letter-spacing: 0.02em !important;
        line-height: 1.2 !important;
        border-style: solid !important;
        border-width: 1px !important;
        background-image: none !important;
        box-shadow: none !important;
        text-decoration: none !important;
        transition:
            border-color 0.2s ease,
            background 0.2s ease,
            color 0.18s ease,
            transform 0.12s ease !important;
    }
    .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn i {
        font-size: 1rem !important;
        line-height: 1 !important;
    }
    /* Certificate download — emerald tonal */
    .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--cert {
        border-color: color-mix(in srgb, #059669 52%, var(--dtr-input-border) 48%) !important;
        background: color-mix(in srgb, #059669 11%, var(--dtr-card-bg) 89%) !important;
        color: #047857 !important;
    }
    .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--cert i {
        color: color-mix(in srgb, #059669 85%, var(--dtr-heading) 15%) !important;
    }
    .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--cert:hover,
    .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--cert:focus {
        border-color: color-mix(in srgb, #059669 72%, var(--dtr-input-border) 28%) !important;
        background: color-mix(in srgb, #059669 20%, var(--dtr-card-bg) 80%) !important;
        color: var(--dtr-heading) !important;
    }
    .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--cert:hover i,
    .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--cert:focus i {
        color: #059669 !important;
    }
    .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--cert:focus-visible {
        outline: none !important;
        box-shadow:
            0 0 0 2px var(--dtr-card-bg),
            0 0 0 4px color-mix(in srgb, #059669 34%, transparent) !important;
    }
    html[data-theme="dark"] .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--cert {
        border-color: color-mix(in srgb, #34d399 42%, var(--dtr-input-border) 58%) !important;
        background: color-mix(in srgb, #059669 18%, var(--dtr-card-bg) 82%) !important;
        color: color-mix(in srgb, #6ee7b7 82%, var(--dtr-heading) 18%) !important;
    }
    html[data-theme="dark"] .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--cert:hover,
    html[data-theme="dark"] .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--cert:focus {
        background: color-mix(in srgb, #059669 28%, var(--dtr-card-bg) 72%) !important;
        color: var(--dtr-heading) !important;
    }
    html[data-theme="dark"] .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--cert:focus-visible {
        box-shadow:
            0 0 0 2px var(--dtr-card-bg),
            0 0 0 4px color-mix(in srgb, #34d399 30%, transparent) !important;
    }
    /* Confirm — primary tonal (matches other coordinator outline CTAs) */
    .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--confirm {
        border-color: color-mix(in srgb, var(--dtr-primary) 52%, var(--dtr-input-border) 48%) !important;
        background: color-mix(in srgb, var(--dtr-primary) 11%, var(--dtr-card-bg) 89%) !important;
        color: var(--dtr-primary-dark, var(--dtr-primary)) !important;
    }
    .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--confirm i {
        color: color-mix(in srgb, var(--dtr-primary) 88%, var(--dtr-heading) 12%) !important;
    }
    .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--confirm:hover,
    .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--confirm:focus {
        border-color: color-mix(in srgb, var(--dtr-primary) 70%, var(--dtr-input-border) 30%) !important;
        background: color-mix(in srgb, var(--dtr-primary) 20%, var(--dtr-card-bg) 80%) !important;
        color: var(--dtr-heading) !important;
    }
    .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--confirm:hover i,
    .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--confirm:focus i {
        color: var(--dtr-primary) !important;
    }
    .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--confirm:focus-visible {
        outline: none !important;
        box-shadow:
            0 0 0 2px var(--dtr-card-bg),
            0 0 0 4px color-mix(in srgb, var(--dtr-primary) 32%, transparent) !important;
    }
    html[data-theme="dark"] .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--confirm {
        border-color: color-mix(in srgb, var(--dtr-primary) 48%, var(--dtr-input-border) 52%) !important;
        background: color-mix(in srgb, var(--dtr-primary) 17%, var(--dtr-card-bg) 83%) !important;
        color: color-mix(in srgb, var(--dtr-primary) 68%, #e8ecff 32%) !important;
    }
    html[data-theme="dark"] .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--confirm:hover,
    html[data-theme="dark"] .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--confirm:focus {
        background: color-mix(in srgb, var(--dtr-primary) 26%, var(--dtr-card-bg) 74%) !important;
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn--confirm:focus-visible {
        box-shadow:
            0 0 0 2px var(--dtr-card-bg),
            0 0 0 4px color-mix(in srgb, var(--dtr-primary) 42%, transparent) !important;
    }
    .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn:active {
        transform: scale(0.985);
    }
    @media (prefers-reduced-motion: reduce) {
        .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn {
            transition: border-color 0.2s ease, background 0.2s ease, color 0.18s ease !important;
        }
        .layout-wrap .main-content .ojt-page .ojt-completion-table .btn.ojt-action-btn:active {
            transform: none;
        }
    }

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
                    <table class="table table-hover align-middle mb-0 ojt-completion-table">
                        <thead>
                            <tr>
                                <th>Student No</th>
                                <th>Name</th>
                                <th>Total Rendered</th>
                                <th>Required</th>
                                <th style="min-width: 160px;">Progress</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
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
                                    $progressFill = $pct >= 100 ? 'complete' : ($pct > 0 ? 'in-progress' : 'empty');
                                    $confirmed = $student->isOjtCompletionConfirmed($activeAssignment);
                                    $confirmedBy = $activeAssignment?->confirmedBy;
                                @endphp
                                <tr>
                                    <td>{{ $student->student_no }}</td>
                                    <td>{{ $student->name }}</td>
                                    <td><strong>{{ number_format($total, 1) }}</strong> hrs</td>
                                    <td>{{ number_format($required, 1) }} hrs</td>
                                    <td>
                                        <div class="progress-wrap">
                                            <div class="progress ojt-progress-track">
                                                <div class="progress-bar ojt-progress-fill ojt-progress-fill--{{ $progressFill }}" role="progressbar" data-pct="{{ $pct }}" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $pct }} percent progress"></div>
                                            </div>
                                            <span class="progress-value">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($confirmed)
                                            <span class="badge badge-confirmed ojt-status-pill"><i class="bi bi-check-circle me-1" aria-hidden="true"></i>Confirmed</span>
                                        @elseif($reached)
                                            <span class="badge badge-reached ojt-status-pill"><i class="bi bi-clock me-1" aria-hidden="true"></i>Reached</span>
                                        @else
                                            <span class="badge badge-not-reached ojt-status-pill">Not reached</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="ojt-action-stack">
                                            @if($confirmed)
                                                <a href="{{ route('coordinator.ojt.completion.certificate', $student) }}" class="btn ojt-action-btn ojt-action-btn--cert" target="_blank" rel="noopener noreferrer">
                                                    <i class="bi bi-download" aria-hidden="true"></i>Certificate
                                                </a>
                                                <small class="ojt-action-meta">
                                                    By {{ $confirmedBy->name ?? 'Coordinator' }} · {{ $activeAssignment?->confirmed_at?->format('M d, Y') }}
                                                </small>
                                            @elseif($reached)
                                                <form action="{{ route('coordinator.ojt.completion.confirm', $student) }}" method="POST" class="d-inline-flex m-0">
                                                    @csrf
                                                    <button type="submit" class="btn ojt-action-btn ojt-action-btn--confirm" data-norsu-confirm="Confirm that {{ e($student->name) }} has completed the required {{ number_format($required, 0) }} hours?" data-norsu-variant="warning">
                                                        <i class="bi bi-patch-check" aria-hidden="true"></i>Confirm
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
