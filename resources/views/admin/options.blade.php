@extends('layouts.admin')

@section('title', 'Options')

@section('content')
<h1 class="page-title">Options Management</h1>
<p class="page-sub text-center">Manage dynamic Sections used in registration and coordinator assignment forms.</p>

<div class="card options-card mb-4">
    <div class="card-body p-4">
        <div class="options-grid">
            <section class="option-panel">
                <div class="option-panel-head">
                    <h2 class="h5 mb-0">Sections</h2>
                    <span class="option-count">{{ $dynamicSections->count() }} active</span>
                </div>
                <form action="{{ route('admin.options.sections.store') }}" method="POST" class="option-form">
                    @csrf
                    <label class="form-label">Add Section</label>
                    <div class="option-form-row">
                        <input type="text" name="value" class="form-control" placeholder="e.g. C / 3A / IT-1" required>
                        <button type="submit" class="btn btn-primary option-add-btn">
                            <i class="bi bi-plus-lg me-1"></i>Add
                        </button>
                    </div>
                </form>
                <div class="option-list">
                    @forelse($dynamicSections as $option)
                        <div class="option-item">
                            <div class="option-item-copy">
                                <span class="option-item-value">{{ \App\Models\Student::sectionOptionLabel($option->value) }}</span>
                                <small class="text-muted">Active</small>
                            </div>
                            <form action="{{ route('admin.options.deactivate', $option) }}" method="POST" class="m-0" data-norsu-confirm="Remove this section permanently? It will disappear from registration lists." data-norsu-variant="danger">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">No dynamic section added yet.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .options-card {
        border: 1px solid color-mix(in srgb, var(--dtr-border-soft) 82%, transparent);
        border-radius: 16px;
        background: color-mix(in srgb, var(--dtr-card-bg) 92%, transparent);
        box-shadow: 0 10px 28px -20px rgba(15, 23, 42, 0.35);
    }
    .options-card .card-body {
        padding: 1rem !important;
    }
    .options-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 0.85rem;
    }
    .option-panel {
        border: 1px solid color-mix(in srgb, var(--dtr-border-soft) 78%, transparent);
        border-radius: 14px;
        background: color-mix(in srgb, var(--dtr-surface-soft) 88%, transparent);
        padding: 0.9rem;
        display: grid;
        gap: 0.8rem;
    }
    .option-panel-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.55rem;
        padding-bottom: 0.45rem;
        border-bottom: 1px solid color-mix(in srgb, var(--dtr-border-soft) 72%, transparent);
    }
    .option-panel-head .h5 {
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: var(--dtr-heading);
    }
    .option-count {
        font-size: 0.72rem;
        color: var(--dtr-muted);
        border: 1px solid color-mix(in srgb, var(--dtr-border-soft) 78%, transparent);
        border-radius: 999px;
        padding: 0.18rem 0.52rem;
        background: color-mix(in srgb, var(--dtr-card-solid) 92%, transparent);
        font-weight: 600;
        letter-spacing: 0.01em;
    }
    .option-form {
        display: grid;
        gap: 0.45rem;
    }
    .option-form .form-label {
        margin-bottom: 0;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--dtr-muted);
        letter-spacing: 0.01em;
    }
    .option-form-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 0.55rem;
        align-items: stretch;
    }
    .option-form-row .form-control {
        min-height: 42px;
        border-radius: 10px;
        border: 1px solid color-mix(in srgb, var(--dtr-input-border) 82%, transparent);
        background: color-mix(in srgb, var(--dtr-input-bg) 95%, transparent);
    }
    .option-add-btn {
        min-height: 42px;
        min-width: 94px;
        border-radius: 10px;
        font-weight: 600;
        box-shadow: none !important;
    }
    .option-list {
        display: grid;
        gap: 0.5rem;
        max-height: 420px;
        overflow: auto;
        padding-right: 0.1rem;
    }
    .option-item {
        border: 1px solid color-mix(in srgb, var(--dtr-border-soft) 76%, transparent);
        border-radius: 10px;
        background: color-mix(in srgb, var(--dtr-card-solid) 94%, transparent);
        padding: 0.58rem 0.62rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.62rem;
        transition: background-color 0.18s ease, border-color 0.18s ease, transform 0.18s ease;
    }
    .option-item:hover {
        background: color-mix(in srgb, var(--dtr-card-solid) 88%, var(--dtr-primary-soft) 12%);
        border-color: color-mix(in srgb, var(--dtr-border-soft) 56%, var(--dtr-primary) 44%);
        transform: translateY(-1px);
    }
    .option-item.is-inactive {
        opacity: 0.55;
    }
    .option-item-copy {
        min-width: 0;
        display: grid;
        gap: 0.12rem;
    }
    .option-item-value {
        font-weight: 600;
        font-size: 0.92rem;
        color: var(--dtr-heading);
        word-break: break-word;
    }
    .option-item-copy small {
        font-size: 0.72rem;
        font-weight: 500;
    }
    .option-item .btn {
        border-radius: 9px;
        min-height: 34px;
        min-width: 84px;
        padding: 0.36rem 0.72rem;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .option-list::-webkit-scrollbar {
        width: 7px;
    }
    .option-list::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: color-mix(in srgb, var(--dtr-border-soft) 72%, var(--dtr-primary) 28%);
    }
    @media (max-width: 960px) {
        .options-grid {
            grid-template-columns: 1fr;
        }
        .option-form-row {
            grid-template-columns: 1fr;
        }
        .option-add-btn {
            width: 100%;
        }
    }
    @media (max-width: 576px) {
        .options-card .card-body {
            padding: 0.78rem !important;
        }
        .option-panel {
            padding: 0.72rem;
        }
        .option-item {
            flex-direction: column;
            align-items: flex-start;
        }
        .option-item .btn {
            width: 100%;
        }
    }
</style>
@endpush

