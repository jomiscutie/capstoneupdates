@extends('layouts.coordinator')

@section('title', 'Enrolled Students')

@push('styles')
<style>
    .students-page {
        color: var(--dtr-text);
    }
    .students-page .dashboard-header {
        text-align: center;
        display: flex;
        justify-content: center;
        margin-bottom: 1.25rem;
    }
    .students-page .dashboard-header > div {
        width: 100%;
    }
    .students-page .dashboard-header h2,
    .students-page .dashboard-header p {
        text-align: center;
    }
    .students-page .students-card {
        border: 1px solid var(--dtr-border-soft);
        border-radius: 18px;
        background: var(--dtr-card-bg);
        box-shadow: var(--dtr-shadow-soft);
        overflow: hidden;
    }
    .students-page .students-card .card-body {
        padding: 1.35rem;
    }
    .students-page .card-title {
        font-weight: 700;
        color: var(--dtr-text);
    }
    .students-page .card-sub {
        color: var(--dtr-muted);
        font-size: 0.9rem;
        margin-top: 0.25rem;
    }
    .students-page .summary-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 0.9rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.1);
        color: var(--dtr-primary);
        font-weight: 600;
        font-size: 0.88rem;
    }
    .students-page .form-select,
    .students-page .form-control,
    .students-page .search-input {
        border-color: var(--dtr-input-border);
        color: var(--dtr-text);
        background: var(--dtr-input-bg);
    }
    .students-page .coord-roster-table thead th {
        color: var(--dtr-heading);
        border-bottom-color: var(--dtr-border-strong);
    }
    .students-page .coord-roster-table tbody td {
        color: var(--dtr-text);
        border-bottom-color: var(--dtr-row-divider);
    }
    .students-page .search-wrap {
        margin: 1rem 0 1.15rem;
    }
    .students-page .filter-search-row {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 0.65rem 0.85rem;
        width: 100%;
        min-width: 0;
    }
    .students-page .filter-search-row .search-group {
        flex: 1 1 280px;
        min-width: 0;
    }
    .students-page .filter-search-row .filter-group {
        flex: 1 1 150px;
        min-width: 0;
        max-width: 100%;
    }
    .students-page .filter-search-row .filter-group.filter-group-program {
        flex: 2 1 200px;
    }
    .students-page .filter-search-row .filter-action-wrap {
        flex: 0 0 auto;
        display: flex;
        align-items: flex-end;
    }
    .students-page .filter-group {
        min-width: 0;
    }
    .students-page .filter-group.filter-group-program {
        min-width: 0;
    }
    .students-page .search-group {
        min-width: 0;
    }
    .students-page .filter-group .form-label {
        font-size: 0.75rem;
        color: var(--dtr-muted);
        margin-bottom: 0.25rem;
    }
    .students-page .search-group .form-label {
        font-size: 0.75rem;
        color: var(--dtr-muted);
        margin-bottom: 0.25rem;
    }
    .students-page .filter-apply-btn {
        font-weight: 600;
        border: 1px solid color-mix(in srgb, var(--dtr-primary) 65%, var(--dtr-border-soft));
        background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark));
        color: #fff;
    }
    .students-page .filter-apply-btn:hover,
    .students-page .filter-apply-btn:focus {
        color: #fff;
        filter: brightness(1.05);
    }
    .students-page .filter-action-wrap {
        padding-top: 0;
        display: flex;
        align-items: end;
    }
    .students-page .filter-reset-btn {
        font-weight: 600;
        border: 1px solid color-mix(in srgb, var(--dtr-text) 24%, var(--dtr-input-border));
        color: color-mix(in srgb, var(--dtr-text) 86%, var(--dtr-primary) 14%);
        background: color-mix(in srgb, var(--dtr-card-bg) 84%, var(--dtr-text) 16%);
    }
    .students-page .filter-reset-btn:hover,
    .students-page .filter-reset-btn:focus {
        color: var(--dtr-heading);
        background: color-mix(in srgb, var(--dtr-hover-bg) 70%, var(--dtr-primary) 30%);
        border-color: color-mix(in srgb, var(--dtr-input-border) 45%, var(--dtr-primary) 55%);
    }
    .students-page .search-form {
        width: 100%;
        max-width: none;
    }
    .students-page .search-inner {
        position: relative;
        min-width: 0;
    }
    .students-page .search-input {
        width: 100%;
        min-height: 38px;
        padding: 0.5rem 2rem 0.5rem 2rem;
        font-size: 0.9rem;
        border: 1px solid var(--dtr-input-border);
        border-radius: 10px;
        background: var(--dtr-input-bg);
        color: var(--dtr-text);
        transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    }
    .students-page .search-input::placeholder {
        color: var(--dtr-muted);
    }
    .students-page .search-input:focus {
        outline: none;
        border-color: var(--dtr-primary);
        background: color-mix(in srgb, var(--dtr-input-bg) 86%, #ffffff 14%);
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--dtr-primary) 24%, transparent);
    }
    .students-page .search-icon {
        position: absolute;
        left: 0.65rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--dtr-muted);
        pointer-events: none;
    }
    .students-page .search-clear {
        position: absolute;
        right: 0.3rem;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        color: var(--dtr-muted);
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }
    .students-page .search-clear:hover {
        background: var(--dtr-hover-bg);
        color: var(--dtr-text);
    }
    .students-page .table thead th {
        background: var(--dtr-surface-soft);
        color: var(--dtr-muted);
        font-weight: 600;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.8rem 1rem;
        border-bottom: 1px solid var(--dtr-border-soft);
    }
    .students-page .table td {
        padding: 0.9rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--dtr-border-soft);
    }
    .students-page .table tbody tr:last-child td {
        border-bottom: none;
    }
    .students-page .table tbody tr:hover {
        background: var(--dtr-hover-bg);
    }
    .students-page .student-name {
        font-weight: 600;
        color: var(--dtr-text);
    }
    .students-page .student-meta {
        color: var(--dtr-muted);
        font-size: 0.82rem;
    }
    /* Roster table: stable columns, centered metrics, wrapped program — no overlap */
    .students-page .coord-roster-table {
        table-layout: fixed;
        width: 100%;
        font-size: 0.9300rem;
    }
    .students-page .coord-roster-table thead th {
        font-size: 0.62rem;
        padding: 0.5rem 0.65rem;
    }
    .students-page .coord-roster-table tbody td {
        padding: 0.55rem 0.7rem;
        border-bottom: 1px solid color-mix(in srgb, var(--dtr-border-soft) 38%, transparent) !important;
    }
    .students-page .coord-roster-table > :not(caption) > * > * {
        border-bottom-width: 1px !important;
        border-bottom-style: solid !important;
        border-bottom-color: color-mix(in srgb, var(--dtr-border-soft) 38%, transparent) !important;
    }
    .students-page .coord-roster-table .student-meta {
        font-size: 0.72rem;
    }
    .students-page .coord-roster-table col.coord-roster-col-sn { width: 12%; }
    .students-page .coord-roster-table col.coord-roster-col-name { width: 24%; }
    .students-page .coord-roster-table col.coord-roster-col-program { width: 18%; }
    .students-page .coord-roster-table col.coord-roster-col-term { width: 12%; }
    .students-page .coord-roster-table col.coord-roster-col-section { width: 10%; }
    .students-page .coord-roster-table col.coord-roster-col-office { width: 14%; }
    .students-page .coord-roster-table col.coord-roster-col-hours { width: 10%; }
    .students-page .coord-roster-table thead th {
        vertical-align: middle;
    }
    .students-page .coord-roster-table thead th:nth-child(1),
    .students-page .coord-roster-table thead th:nth-child(2),
    .students-page .coord-roster-table thead th:nth-child(3),
    .students-page .coord-roster-table thead th:nth-child(4) {
        text-align: left;
    }
    .students-page .coord-roster-table thead th:nth-child(5),
    .students-page .coord-roster-table thead th:nth-child(7) {
        text-align: center;
    }
    .students-page .coord-roster-table td.coord-roster-sn {
        white-space: nowrap;
        vertical-align: middle;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: left;
        border-bottom: 1px solid color-mix(in srgb, var(--dtr-border-soft) 38%, transparent) !important;
    }
    .students-page .coord-roster-table td.coord-roster-name,
    .students-page .coord-roster-table td.coord-roster-program {
        min-width: 0;
        vertical-align: middle;
        overflow-wrap: anywhere;
        word-break: break-word;
        text-align: left;
    }
    .students-page .coord-roster-table td.coord-roster-term,
    .students-page .coord-roster-table td.coord-roster-section,
    .students-page .coord-roster-table td.coord-roster-hours {
        vertical-align: middle;
    }
    .students-page .coord-roster-table td.coord-roster-term {
        text-align: left;
    }
    .students-page .coord-roster-table td.coord-roster-section,
    .students-page .coord-roster-table td.coord-roster-hours {
        text-align: center;
    }
    .students-page .coord-roster-table thead th,
    .students-page .coord-roster-table tbody td {
        padding-left: 0.65rem;
        padding-right: 0.65rem;
    }
    .students-page .coord-roster-table .coord-roster-hours .tabular-nums {
        font-variant-numeric: tabular-nums;
    }
    .students-page .empty-state {
        text-align: center;
        padding: 2rem 1rem;
        color: var(--dtr-muted);
    }
    @media (max-width: 576px) {
        .students-page .filter-search-row .filter-action-wrap {
            flex: 1 1 100%;
        }
        .students-page .filter-search-row .filter-action-wrap .btn {
            width: 100%;
            justify-content: center;
        }
    }
    @media (max-width: 768px) {
        .students-page .students-card .card-body {
            padding: 1rem;
        }
        .students-page .search-form {
            max-width: none;
        }
        .students-page .filter-search-row {
            align-items: stretch;
        }
        .students-page .filter-group {
            width: 100%;
        }
        .students-page .filter-group.filter-group-program {
            min-width: 0;
        }
        .students-page .search-group {
            width: 100%;
            min-width: 0;
        }
        .students-page .filter-action-wrap {
            padding-top: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="students-page">
    <div class="dashboard-header">
        <div>
            <h2><i class="bi bi-people me-2"></i>Enrolled Students</h2>
            <p>Verified students currently assigned to your coordinator scope.</p>
        </div>
    </div>

    <div class="students-card card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h5 class="card-title mb-0">List of Students</h5>
                    <p class="card-sub mb-0">Search by name, student number, program, school year, term, or section.</p>
                </div>
                <div class="summary-chip">
                    <i class="bi bi-person-lines-fill"></i>
                    <span>{{ $students->count() }} enrolled student{{ $students->count() === 1 ? '' : 's' }}</span>
                </div>
            </div>

            <div class="search-wrap">
                <form action="{{ route('coordinator.students') }}" method="GET" class="search-form" role="search">
                    <div class="filter-search-row">
                        <div class="search-group">
                            <label for="studentsSearchInput" class="form-label small text-muted mb-1">Search</label>
                            <div class="search-inner">
                                <i class="bi bi-search search-icon" aria-hidden="true"></i>
                                <input type="text"
                                       id="studentsSearchInput"
                                       name="q"
                                       class="search-input form-control"
                                       placeholder="Search enrolled students"
                                       value="{{ $search ?? '' }}"
                                       aria-label="Search enrolled students">
                                @if(!empty($search))
                                    <a href="{{ route('coordinator.students', array_filter([
                                        'enrollment_filter' => ($enrollmentFilter ?? 'all') !== 'all' ? ($enrollmentFilter ?? 'all') : null,
                                        'program_filter' => !empty($programFilter ?? '') ? ($programFilter ?? '') : null,
                                        'section_filter' => !empty($sectionFilter ?? '') ? ($sectionFilter ?? '') : null,
                                        'office_filter' => !empty($officeFilter ?? '') ? ($officeFilter ?? '') : null,
                                    ])) }}" class="search-clear" aria-label="Clear search">
                                        <i class="bi bi-x-lg"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="filter-group">
                            <label for="enrollmentFilter" class="form-label small text-muted mb-1">Filter</label>
                            <select name="enrollment_filter" id="enrollmentFilter" class="form-select form-select-sm">
                                <option value="all" {{ ($enrollmentFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Students</option>
                                <option value="newly_enrolled" {{ ($enrollmentFilter ?? '') === 'newly_enrolled' ? 'selected' : '' }}>Newly Enrolled (last {{ $newlyEnrolledDays ?? 30 }} days)</option>
                            </select>
                        </div>
                        <div class="filter-group filter-group-program">
                            <label for="programFilter" class="form-label small text-muted mb-1">Program</label>
                            <select name="program_filter" id="programFilter" class="form-select form-select-sm">
                                <option value="">All Programs</option>
                                @foreach(($programOptions ?? collect()) as $programOption)
                                    <option value="{{ $programOption }}" {{ ($programFilter ?? '') === $programOption ? 'selected' : '' }}>
                                        {{ $programOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="sectionFilter" class="form-label small text-muted mb-1">Section</label>
                            @php
                                $normalizedSectionFilter = preg_replace('/^section\s+/i', '', (string) ($sectionFilter ?? ''));
                                $normalizedSectionOptions = collect(\App\Models\Student::getSectionOptions())
                                    ->map(function ($sectionOption) {
                                        $raw = trim((string) $sectionOption);
                                        $clean = trim((string) (preg_replace('/^section\s+/i', '', $raw) ?? $raw));
                                        return [
                                            'value' => $clean,
                                            'label' => \App\Models\Student::sectionOptionLabel($raw),
                                            'canonical' => mb_strtoupper($clean, 'UTF-8'),
                                        ];
                                    })
                                    ->filter(fn ($option) => $option['value'] !== '' && $option['canonical'] !== 'ALL')
                                    ->unique('canonical')
                                    ->values();
                            @endphp
                            <select name="section_filter" id="sectionFilter" class="form-select form-select-sm">
                                <option value="">All Sections</option>
                                @foreach($normalizedSectionOptions as $sectionOption)
                                    <option value="{{ $sectionOption['value'] }}" {{ $normalizedSectionFilter === $sectionOption['value'] ? 'selected' : '' }}>
                                        {{ $sectionOption['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="officeFilter" class="form-label small text-muted mb-1">Assigned Office</label>
                            <select name="office_filter" id="officeFilter" class="form-select form-select-sm">
                                <option value="">All Offices</option>
                                @foreach(($officeOptions ?? collect()) as $officeOption)
                                    <option value="{{ $officeOption }}" {{ ($officeFilter ?? '') === $officeOption ? 'selected' : '' }}>
                                        {{ $officeOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-action-wrap">
                            <button type="submit" class="btn btn-sm btn-primary filter-apply-btn">
                                <i class="bi bi-funnel me-1"></i>Apply
                            </button>
                        </div>
                        @if(!empty($search) || ($enrollmentFilter ?? 'all') !== 'all' || !empty($programFilter ?? '') || !empty($sectionFilter ?? '') || !empty($officeFilter ?? ''))
                            <div class="filter-action-wrap">
                                <a href="{{ route('coordinator.students') }}" class="btn btn-sm btn-outline-secondary filter-reset-btn">Reset</a>
                            </div>
                        @endif
                    </div>
                    @if(empty($search) && (($enrollmentFilter ?? 'all') !== 'all' || !empty($programFilter ?? '') || !empty($sectionFilter ?? '') || !empty($officeFilter ?? '')))
                        <div class="mt-2">
                            <a href="{{ route('coordinator.students') }}" class="small text-muted text-decoration-none">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Clear active filters
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            @if($students->count() > 0)
                <div class="coord-roster-wrap">
                    <table class="table align-middle mb-0 coord-roster-table">
                        <colgroup>
                            <col class="coord-roster-col-sn">
                            <col class="coord-roster-col-name">
                            <col class="coord-roster-col-program">
                            <col class="coord-roster-col-term">
                            <col class="coord-roster-col-section">
                            <col class="coord-roster-col-office">
                            <col class="coord-roster-col-hours">
                        </colgroup>
                        <thead>
                            <tr>
                                <th scope="col" class="text-nowrap">Student No</th>
                                <th scope="col">Name</th>
                                <th scope="col">Program</th>
                                <th scope="col" class="text-nowrap">Current Term</th>
                                <th scope="col" class="text-center text-nowrap">Section</th>
                                <th scope="col">Assigned Office</th>
                                <th scope="col" class="text-center text-nowrap">Required Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                                @php($activeAssignment = $student->activeTermAssignment)
                                @php($rowSearch = \Illuminate\Support\Str::lower(implode(' ', [
                                    $student->student_no,
                                    $student->name,
                                    $activeAssignment?->course ?: $student->display_course ?: '',
                                    $activeAssignment?->school_year ?: '',
                                    $activeAssignment?->term ?: '',
                                    $activeAssignment?->section ?: $student->display_section ?: '',
                                    $student->assigned_office ?: '',
                                ])))
                                @php($cleanSection = preg_replace('/^section\s+/i', '', (string) ($activeAssignment?->section ?: $student->display_section ?: '-')))
                                <tr data-live-row data-live-search="{{ $rowSearch }}" data-live-name="{{ \Illuminate\Support\Str::lower($student->name) }}">
                                    <td class="fw-semibold coord-roster-sn" title="{{ e($student->student_no) }}">{{ $student->student_no }}</td>
                                    <td class="coord-roster-name">
                                        <div class="student-name">{{ $student->name }}</div>
                                        <div class="student-meta">{{ $activeAssignment?->school_year ?: 'School year not set' }}</div>
                                    </td>
                                    <td class="coord-roster-program">{{ $activeAssignment?->course ?: $student->display_course ?: '-' }}</td>
                                    <td class="coord-roster-term">
                                        @if($activeAssignment?->term)
                                            <span class="fw-medium">{{ $activeAssignment->term }}</span>
                                        @else
                                            <span class="text-muted">Not assigned</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap coord-roster-section">{{ $cleanSection }}</td>
                                    <td>{{ $student->assigned_office ?: '-' }}</td>
                                    <td class="text-nowrap coord-roster-hours"><span class="tabular-nums">{{ number_format((float) $student->current_required_hours, 1) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    @if(!empty($search))
                        <p class="mb-1 fw-medium">No enrolled students match "{{ e($search) }}".</p>
                        <p class="mb-0">Try another search or <a href="{{ route('coordinator.students', array_filter([
                            'enrollment_filter' => ($enrollmentFilter ?? 'all') !== 'all' ? ($enrollmentFilter ?? 'all') : null,
                            'program_filter' => !empty($programFilter ?? '') ? ($programFilter ?? '') : null,
                            'section_filter' => !empty($sectionFilter ?? '') ? ($sectionFilter ?? '') : null,
                            'office_filter' => !empty($officeFilter ?? '') ? ($officeFilter ?? '') : null,
                        ])) }}">clear the filter</a>.</p>
                    @else
                        <p class="mb-1 fw-medium">No enrolled students in your assigned scope yet.</p>
                        <p class="mb-0">Once students are verified and assigned to your program, term, and section, they will appear here.</p>
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
    var root = document.querySelector('.students-page');
    if (!root) return;

    var input = root.querySelector('.search-input');
    var enrollmentFilter = root.querySelector('#enrollmentFilter');
    var rows = Array.prototype.slice.call(root.querySelectorAll('tr[data-live-row]'));
    if (!input || !rows.length) return;

    function normalizePattern(s) {
        return (s || '').trim().replace(/%/g, '*');
    }
    function usesWildcardTokens(raw) {
        return /[*?%_]/.test((raw || '').trim());
    }
    function matchesWildcard(text, raw) {
        var p = normalizePattern(raw);
        if (!p) return true;
        var lower = (text || '').toLowerCase();
        var pattern = p.toLowerCase();
        if (pattern.indexOf('*') === -1) {
            return lower.indexOf(pattern) !== -1;
        }
        var parts = pattern.split('*');
        var pos = 0;
        for (var i = 0; i < parts.length; i++) {
            var seg = parts[i];
            if (!seg) continue;
            var j = lower.indexOf(seg, pos);
            if (j === -1) return false;
            pos = j + seg.length;
        }
        return true;
    }
    function levenshtein(a, b) {
        if (a === b) return 0;
        if (!a.length) return b.length;
        if (!b.length) return a.length;
        var v0 = new Array(b.length + 1);
        var v1 = new Array(b.length + 1);
        var i, j;
        for (j = 0; j <= b.length; j++) v0[j] = j;
        for (i = 0; i < a.length; i++) {
            v1[0] = i + 1;
            for (j = 0; j < b.length; j++) {
                var cost = a[i] === b[j] ? 0 : 1;
                v1[j + 1] = Math.min(v1[j] + 1, v0[j + 1] + 1, v0[j] + cost);
            }
            var t = v0; v0 = v1; v1 = t;
        }
        return v0[b.length];
    }
    function fuzzyNameMatch(name, query) {
        query = (query || '').trim().toLowerCase();
        if (!query) return false;
        var n = (name || '').trim().toLowerCase();
        if (!n) return false;
        if (n.indexOf(query) !== -1 || n.replace(/\s+/g, '').indexOf(query.replace(/\s+/g, '')) !== -1) return true;
        var words = n.split(/\s+/);
        for (var i = 0; i < words.length; i++) {
            var w = words[i];
            if (!w) continue;
            if (w.indexOf(query) !== -1 || query.indexOf(w) !== -1) return true;
            var max = Math.max(w.length, query.length, 1);
            if (max <= 255 && /^[\x00-\x7F]*$/.test(w) && /^[\x00-\x7F]*$/.test(query)) {
                if (levenshtein(w, query) <= Math.max(1, Math.floor(max * 0.28))) return true;
            }
        }
        return false;
    }
    function rowMatches(row, q) {
        var hay = row.getAttribute('data-live-search') || '';
        if (matchesWildcard(hay, q)) return true;
        if (usesWildcardTokens(q)) return false;
        return fuzzyNameMatch(row.getAttribute('data-live-name') || '', q);
    }
    function filterRows() {
        var q = input.value || '';
        rows.forEach(function (row) {
            row.style.display = rowMatches(row, q) ? '' : 'none';
        });
    }

    input.addEventListener('input', filterRows);
    if (enrollmentFilter) {
        enrollmentFilter.addEventListener('change', function () {
            if (enrollmentFilter.form) {
                enrollmentFilter.form.submit();
            }
        });
    }
    filterRows();
})();
</script>
@endpush
