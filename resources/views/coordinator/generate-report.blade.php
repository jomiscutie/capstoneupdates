@extends('layouts.coordinator')

@section('title', 'Generate Monthly Report')

@push('styles')
<style>
    .dtr-report .dashboard-header { text-align: center; display: flex; flex-direction: column; align-items: center; }
    .dtr-report .dashboard-header > div { margin: 0 auto; width: 100%; }
    .dtr-report .dashboard-header h2,
    .dtr-report .dashboard-header p { text-align: center; }
    .dtr-report .program-badge-inline { display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem; width: 100%; margin-top: 0.5rem; }
    .dtr-report .report-shell { max-width: 820px; margin: 0 auto; }
    .dtr-report .report-card { border-radius: 1.5rem; box-shadow: var(--dtr-shadow-soft); border: 1px solid var(--dtr-border-soft); background: var(--dtr-card-bg); overflow: hidden; }
    .dtr-report .report-card-header {
        background: linear-gradient(135deg, var(--dtr-primary) 0%, var(--dtr-primary-dark) 40%, #1e40af 100%);
        color: #fff; padding: 1.35rem 1.75rem; border: none; position: relative; overflow: hidden;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.15);
    }
    .dtr-report .report-card-header::before {
        content: ''; position: absolute; top: -30%; right: -8%; width: 220px; height: 220px;
        background: radial-gradient(circle, rgba(255,255,255,0.16) 0%, transparent 65%); border-radius: 50%;
    }
    .dtr-report .report-card-header h3 { margin: 0; font-weight: 600; font-size: 1.25rem; position: relative; z-index: 1; }
    .dtr-report .report-card-header p { margin: 0.5rem 0 0; font-size: 0.88rem; opacity: 0.92; position: relative; z-index: 1; line-height: 1.45; }
    .dtr-report .report-card-body { padding: 1.75rem 1.75rem 2rem; }
    .dtr-report .form-label { font-weight: 600; color: var(--dtr-text); margin-bottom: 0.45rem; font-size: 0.9rem; }
    .dtr-report .form-control, .dtr-report .form-select { border-radius: var(--dtr-radius); border: 1px solid var(--dtr-input-border); background: var(--dtr-input-bg); color: var(--dtr-text); }
    .dtr-report .report-mode-wrap { margin-bottom: 1.25rem; }
    .dtr-report .report-mode-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--dtr-muted); font-weight: 700; margin-bottom: 0.5rem; }
    .dtr-report .report-mode-group {
        display: flex; gap: 0; padding: 4px; border-radius: 14px; background: var(--dtr-input-bg); border: 1px solid var(--dtr-input-border);
    }
    .dtr-report .report-mode-group input { position: absolute; opacity: 0; pointer-events: none; }
    .dtr-report .report-mode-group label {
        flex: 1; text-align: center; padding: 0.65rem 0.75rem; margin: 0; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 0.9rem;
        color: var(--dtr-muted); transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    }
    .dtr-report .report-mode-group input:checked + label {
        background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark)); color: #fff; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
    }
    .dtr-report .report-mode-group label:hover { color: var(--dtr-text); }
    .dtr-report .report-mode-group input:checked + label:hover { color: #fff; }
    .dtr-report .report-mode-badge { font-size: 0.65rem; font-weight: 700; vertical-align: middle; margin-left: 0.25rem; opacity: 0.95; }
    .dtr-report .report-summary {
        border-radius: var(--dtr-radius); border: 1px solid var(--dtr-input-border); background: var(--dtr-input-bg);
        padding: 1rem 1.15rem; margin-bottom: 1.25rem; display: grid; gap: 0.5rem; font-size: 0.88rem;
    }
    .dtr-report .report-summary-row { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; flex-wrap: wrap; }
    .dtr-report .report-summary-row span:first-child { color: var(--dtr-muted); font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em; }
    .dtr-report .report-summary-val { font-weight: 600; color: var(--dtr-text); text-align: right; }
    .dtr-report .report-student-search {
        border-radius: var(--dtr-radius); border: 1px solid var(--dtr-input-border); background: var(--dtr-input-bg); color: var(--dtr-text);
        padding: 0.55rem 0.85rem; font-size: 0.9rem; width: 100%;
    }
    .dtr-report .report-search-hint { font-size: 0.78rem; color: var(--dtr-muted); margin-top: 0.35rem; line-height: 1.4; }
    .dtr-report .report-list-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.5rem; }
    .dtr-report .report-student-list {
        max-height: min(52vh, 340px); overflow-y: auto; border: 1px solid var(--dtr-input-border); border-radius: var(--dtr-radius);
        background: var(--dtr-input-bg); padding: 0.45rem 0.55rem;
    }
    .dtr-report .student-picker-row {
        display: flex; align-items: flex-start; gap: 0.55rem; padding: 0.4rem 0.35rem; margin: 0; border-radius: 10px; cursor: pointer; font-size: 0.9rem;
    }
    .dtr-report .student-picker-row:hover { background: var(--dtr-hover-bg); }
    .dtr-report .student-picker-label { flex: 1; line-height: 1.35; }
    .dtr-report .student-picker-row.is-filtered-out { display: none !important; }
    .dtr-report .mode-batch-only { display: none !important; }
    .dtr-report.is-batch .mode-batch-only { display: flex !important; }
    .dtr-report .mode-single-only { display: block; }
    .dtr-report.is-batch .mode-single-only { display: none !important; }
    .dtr-report .btn-submit-report {
        background: linear-gradient(135deg, var(--dtr-primary), var(--dtr-primary-dark)); border: none;
        padding: 0.8rem 1.5rem; font-weight: 600; border-radius: var(--dtr-radius); width: 100%; margin-top: 1.25rem;
        transition: transform var(--dtr-transition), box-shadow var(--dtr-transition);
    }
    .dtr-report .btn-submit-report:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(37, 99, 235, 0.35); }
    .dtr-report .btn-submit-report:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }
    .dtr-report .btn-outline-dtr { border-radius: var(--dtr-radius); padding: 0.4rem 0.75rem; font-weight: 500; font-size: 0.8rem; border: 1px solid var(--dtr-input-border); background: var(--dtr-card-bg); color: var(--dtr-text); }
    .dtr-report .btn-outline-dtr:hover { background: var(--dtr-hover-bg); }
</style>
@endpush

@section('content')
@php
    $maxBatch = \App\Http\Requests\BatchGenerateReportRequest::MAX_STUDENTS;
    $monthDefault = old('month', request('month') ?? now()->format('Y-m'));
@endphp
<div class="dtr-report" id="dtrReportPage">
    <div class="dashboard-header">
        <div>
            <h2><i class="bi bi-file-earmark-pdf me-2"></i>Generate Monthly Report</h2>
            <p>Pick a month, choose <strong>one student</strong> for a PDF or <strong>several</strong> for a ZIP. Same list and search for both. PDF generation needs your server running (e.g. XAMPP).</p>
        </div>
        @if(auth()->guard('coordinator')->user()->major)
            <div class="program-badge-inline">
                <i class="bi bi-mortarboard me-1"></i><strong>Program:</strong> {{ auth()->guard('coordinator')->user()->major }}
            </div>
        @endif
    </div>

    @if(session('error'))
        <div class="alert alert-danger mx-auto mb-4 report-shell">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="report-shell">
        <div class="report-card">
            <div class="report-card-header">
                <h3><i class="bi bi-calendar-check me-2"></i>Export DTR</h3>
                <p>One form: switch between a single PDF and a ZIP of PDFs for the same month.</p>
            </div>
            <div class="report-card-body">
                <form
                    method="POST"
                    id="reportExportForm"
                    action="{{ route('coordinator.generate.report.submit') }}"
                    data-single-action="{{ route('coordinator.generate.report.submit') }}"
                    data-batch-action="{{ route('coordinator.generate.report.batch') }}"
                >
                    @csrf
                    <div class="mb-4">
                        <label for="report_month" class="form-label"><i class="bi bi-calendar3 me-1"></i>Month</label>
                        <input type="month" name="month" id="report_month" class="form-control" value="{{ $monthDefault }}" required>
                        @error('month')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="report-mode-wrap">
                        <div class="report-mode-label">Export type</div>
                        <div class="report-mode-group" role="radiogroup" aria-label="Export type">
                            <input type="radio" name="export_mode_ui" id="mode_single" value="single" checked autocomplete="off">
                            <label for="mode_single"><i class="bi bi-file-earmark-pdf me-1"></i>Single PDF</label>
                            <input type="radio" name="export_mode_ui" id="mode_batch" value="batch" autocomplete="off">
                            <label for="mode_batch"><i class="bi bi-file-zip me-1"></i>ZIP<span class="report-mode-badge">max {{ $maxBatch }}</span></label>
                        </div>
                    </div>

                    <div class="report-summary" id="reportSummary" aria-live="polite">
                        <div class="report-summary-row">
                            <span>Month</span>
                            <span class="report-summary-val" id="summaryMonth">—</span>
                        </div>
                        <div class="report-summary-row">
                            <span>Output</span>
                            <span class="report-summary-val" id="summaryMode">One PDF file</span>
                        </div>
                        <div class="report-summary-row">
                            <span>Selection</span>
                            <span class="report-summary-val" id="summarySelection">Choose a student below</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="report_student_search" class="form-label"><i class="bi bi-search me-1"></i>Find students</label>
                        <input type="search" id="report_student_search" class="report-student-search" placeholder="Name or ID of the students" autocomplete="off" enterkeyhint="search">
                    </div>

                    <div class="report-list-toolbar mode-batch-only">
                        <span class="form-label mb-0"><i class="bi bi-people me-1"></i>Students</span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn-outline-dtr" id="batchSelectVisible">Select all</button>
                            <button type="button" class="btn-outline-dtr" id="batchSelectNone">Clear all</button>
                        </div>
                    </div>
                    <div class="mb-1">
                        <span class="form-label mode-single-only"><i class="bi bi-person me-1"></i>Student</span>
                    </div>

                    <div class="report-student-list" id="reportStudentList">
                        @forelse($students as $student)
                            @php
                                $searchLine = \Illuminate\Support\Str::lower($student->student_no.' '.$student->name);
                                $oldSingle = (string) old('student_id') === (string) $student->id;
                                $oldBatch = is_array(old('student_ids')) && in_array((string) $student->id, array_map('strval', old('student_ids')), true);
                            @endphp
                            <label class="student-picker-row" data-student-search="{{ $searchLine }}" data-student-name="{{ \Illuminate\Support\Str::lower($student->name) }}">
                                <input
                                    type="radio"
                                    name="student_id"
                                    value="{{ $student->id }}"
                                    class="form-check-input picker-single"
                                    data-student-label="{{ $student->student_no }} — {{ $student->name }}"
                                    {{ $oldSingle ? 'checked' : '' }}
                                >
                                <input
                                    type="checkbox"
                                    name="student_ids[]"
                                    value="{{ $student->id }}"
                                    class="form-check-input picker-batch"
                                    data-student-label="{{ $student->student_no }} — {{ $student->name }}"
                                    {{ $oldBatch ? 'checked' : '' }}
                                >
                                <span class="student-picker-label"><span class="fw-semibold">{{ $student->student_no }}</span> — {{ $student->name }}</span>
                            </label>
                        @empty
                            <p class="text-muted mb-0 px-2 py-3">No verified students in your program yet.</p>
                        @endforelse
                    </div>
                    @error('student_id')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                    @error('student_ids')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                    @error('student_ids.*')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror

                    <button type="submit" class="btn btn-primary btn-submit-report" id="reportSubmitBtn" @if($students->isEmpty()) disabled @endif>
                        <i class="bi bi-download me-2" id="reportSubmitIcon"></i><span id="reportSubmitLabel">Download PDF</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var root = document.getElementById('dtrReportPage');
    var form = document.getElementById('reportExportForm');
    if (!form || !root) return;

    var monthInput = document.getElementById('report_month');
    var searchInput = document.getElementById('report_student_search');
    var modeSingle = document.getElementById('mode_single');
    var modeBatch = document.getElementById('mode_batch');
    var summaryMonth = document.getElementById('summaryMonth');
    var summaryMode = document.getElementById('summaryMode');
    var summarySelection = document.getElementById('summarySelection');
    var submitBtn = document.getElementById('reportSubmitBtn');
    var submitLabel = document.getElementById('reportSubmitLabel');
    var submitIcon = document.getElementById('reportSubmitIcon');

    var singleUrl = form.getAttribute('data-single-action');
    var batchUrl = form.getAttribute('data-batch-action');

    function normalizePattern(s) {
        return (s || '').trim().replace(/%/g, '*');
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
            if (seg === '') continue;
            var j = lower.indexOf(seg, pos);
            if (j === -1) return false;
            pos = j + seg.length;
        }
        return true;
    }

    function usesWildcardTokens(raw) {
        var s = (raw || '').trim();
        return /[*?%_]/.test(s);
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

    function diceBigramCoefficient(s, t) {
        if (s.length < 2 || t.length < 2) return 0;
        var counts = Object.create(null);
        var i, pair;
        for (i = 0; i < s.length - 1; i++) {
            pair = s.slice(i, i + 2);
            counts[pair] = (counts[pair] || 0) + 1;
        }
        var matches = 0;
        for (i = 0; i < t.length - 1; i++) {
            pair = t.slice(i, i + 2);
            if (counts[pair]) {
                matches++;
                counts[pair]--;
            }
        }
        return (2 * matches) / (s.length + t.length - 2);
    }

    function stringsFuzzyClose(a, b) {
        var la = a.length, lb = b.length;
        var max = Math.max(la, lb, 1);
        if (max < 3) return a === b;
        var threshold = Math.max(1, Math.floor(max * 0.28));
        if (max <= 255 && /^[\x00-\x7F]*$/.test(a) && /^[\x00-\x7F]*$/.test(b)) {
            return levenshtein(a, b) <= threshold;
        }
        return diceBigramCoefficient(a, b) >= 0.35;
    }

    function fuzzyNameMatch(name, query) {
        query = (query || '').trim().toLowerCase();
        if (!query) return false;
        var nameLower = (name || '').trim().toLowerCase();
        if (!nameLower) return false;
        if (nameLower.indexOf(query) !== -1) return true;
        var qCompact = query.replace(/\s+/g, '');
        var compact = nameLower.replace(/\s+/g, '');
        if (qCompact && compact.indexOf(qCompact) !== -1) return true;
        var words = (name || '').trim().split(/\s+/);
        for (var w = 0; w < words.length; w++) {
            var word = words[w].toLowerCase();
            if (!word) continue;
            if (word.indexOf(query) !== -1 || query.indexOf(word) !== -1) return true;
            if (stringsFuzzyClose(word, query)) return true;
        }
        return qCompact && stringsFuzzyClose(compact, qCompact);
    }

    function rowMatchesSearch(row, q) {
        var hay = row.getAttribute('data-student-search') || '';
        if (matchesWildcard(hay, q)) return true;
        if (usesWildcardTokens(q)) return false;
        var nameOnly = row.getAttribute('data-student-name') || '';
        return fuzzyNameMatch(nameOnly, (q || '').trim());
    }

    function isBatch() {
        return modeBatch && modeBatch.checked;
    }

    function setModeClass() {
        root.classList.toggle('is-batch', isBatch());
    }

    function visibleRows() {
        return form.querySelectorAll('.student-picker-row[data-student-search]');
    }

    function filterList() {
        var q = searchInput ? searchInput.value : '';
        visibleRows().forEach(function (row) {
            var hay = row.getAttribute('data-student-search') || '';
            row.classList.toggle('is-filtered-out', !matchesWildcard(hay, q));
        });
        updateSummary();
    }

    function formatMonthDisplay(ym) {
        if (!ym || ym.length < 7) return '—';
        var parts = ym.split('-');
        var y = parts[0];
        var m = parseInt(parts[1], 10);
        if (!y || !m) return ym;
        var names = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        return names[m - 1] + ' ' + y;
    }

    function updateSummary() {
        if (summaryMonth && monthInput) {
            summaryMonth.textContent = formatMonthDisplay(monthInput.value);
        }
        if (summaryMode) {
            summaryMode.textContent = isBatch() ? 'ZIP archive (one PDF per student)' : 'One PDF file';
        }
        if (!summarySelection) return;
        if (isBatch()) {
            var n = form.querySelectorAll('.picker-batch:checked').length;
            summarySelection.textContent = n === 0 ? 'No students selected' : (n === 1 ? '1 student (ZIP with one PDF)' : n + ' students');
        } else {
            var r = form.querySelector('.picker-single:checked');
            summarySelection.textContent = r && r.getAttribute('data-student-label') ? r.getAttribute('data-student-label') : 'Choose a student below';
        }
    }

    function applyModeInputs() {
        var batch = isBatch();
        form.action = batch ? batchUrl : singleUrl;
        form.querySelectorAll('.picker-single').forEach(function (el) {
            el.disabled = batch;
        });
        form.querySelectorAll('.picker-batch').forEach(function (el) {
            el.disabled = !batch;
        });
        if (submitLabel) {
            submitLabel.textContent = batch ? 'Download ZIP' : 'Download PDF';
        }
        if (submitIcon) {
            submitIcon.className = 'bi ' + (batch ? 'bi-file-zip' : 'bi-download') + ' me-2';
        }
        setModeClass();
        updateSummary();
    }

    if (modeSingle) modeSingle.addEventListener('change', applyModeInputs);
    if (modeBatch) modeBatch.addEventListener('change', applyModeInputs);
    if (monthInput) monthInput.addEventListener('input', updateSummary);
    if (monthInput) monthInput.addEventListener('change', updateSummary);

    form.querySelectorAll('.picker-single').forEach(function (el) {
        el.addEventListener('change', updateSummary);
    });
    form.querySelectorAll('.picker-batch').forEach(function (el) {
        el.addEventListener('change', updateSummary);
    });

    if (searchInput) {
        searchInput.addEventListener('input', filterList);
        filterList();
    }

    document.getElementById('batchSelectVisible')?.addEventListener('click', function () {
        if (!isBatch()) return;
        visibleRows().forEach(function (row) {
            if (row.classList.contains('is-filtered-out')) return;
            var cb = row.querySelector('.picker-batch');
            if (cb && !cb.disabled) cb.checked = true;
        });
        updateSummary();
    });
    document.getElementById('batchSelectNone')?.addEventListener('click', function () {
        form.querySelectorAll('.picker-batch').forEach(function (el) {
            if (!el.disabled) el.checked = false;
        });
        updateSummary();
    });

    /* Restore batch mode from old() */
    @if(old('student_ids') && is_array(old('student_ids')) && count(old('student_ids')) > 0)
    if (modeBatch) modeBatch.checked = true;
    @elseif(old('student_id'))
    if (modeSingle) modeSingle.checked = true;
    @endif

    applyModeInputs();
    if (searchInput) filterList();
    updateSummary();

    form.addEventListener('submit', function (e) {
        if (isBatch()) {
            var n = form.querySelectorAll('.picker-batch:checked').length;
            if (n < 1) {
                e.preventDefault();
                alert('Select at least one student for the ZIP download.');
                return;
            }
        } else {
            var sel = form.querySelector('.picker-single:checked');
            if (!sel) {
                e.preventDefault();
                alert('Select a student to download the PDF.');
                return;
            }
        }
    });
})();
</script>
@endpush
