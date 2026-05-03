{{-- Mirrors manual-request-review-modal visuals; scoped to .office-req-review-modal --}}
@push('styles')
<style>
    .office-req-review-modal .modal-content {
        border-radius: 16px;
        border: 1px solid var(--dtr-border-soft);
        background: var(--dtr-card-bg);
        color: var(--dtr-text);
        box-shadow: var(--dtr-shadow-strong, 0 18px 50px rgba(15, 23, 42, 0.2));
        overflow: hidden;
    }
    .office-req-review-modal .modal-header {
        border-bottom: 1px solid var(--dtr-border-soft);
        padding: 1rem 1.15rem;
        gap: 0.75rem;
        align-items: flex-start;
    }
    .office-req-review-modal .mrr-modal-head {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        flex: 1;
        min-width: 0;
    }
    .office-req-review-modal .mrr-modal-icon {
        flex: 0 0 auto;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: var(--dtr-heading);
        background: var(--dtr-surface-soft);
        border: 1px solid var(--dtr-border-soft);
    }
    .office-req-review-modal .mrr-modal-head .modal-title {
        color: var(--dtr-heading);
        font-weight: 700;
        font-size: 1.0625rem;
        letter-spacing: -0.02em;
        line-height: 1.3;
        margin-bottom: 0;
    }
    .office-req-review-modal .mrr-modal-sub {
        color: var(--dtr-muted);
        font-size: 0.835rem;
        line-height: 1.45;
        margin-top: 0.2rem;
        margin-bottom: 0;
    }
    .office-req-review-modal .orc-summary-line {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem 0.65rem;
        align-items: center;
        margin-top: 0.65rem;
    }
    .office-req-review-modal .orc-chip {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        padding: 0.35rem 0.62rem;
        border-radius: 8px;
        font-size: 0.8125rem;
        font-weight: 600;
        letter-spacing: 0.015em;
        word-break: break-word;
        color: var(--dtr-heading);
        background: var(--dtr-surface-soft);
        border: 1px solid var(--dtr-border-soft);
    }
    .office-req-review-modal .orc-chip .orc-chip-muted {
        font-weight: 500;
        color: var(--dtr-muted);
        margin-right: 0.25rem;
    }
    .office-req-review-modal .orc-reason-panel {
        margin-top: 0.85rem;
        padding: 0.82rem 0.92rem;
        border-radius: 10px;
        border: 1px solid #34d399;
        background: #ecfdf5;
        box-shadow: none;
    }
    .office-req-review-modal .orc-reason-panel .orc-reason-label {
        font-size: 0.68rem;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #047857;
        margin-bottom: 0.42rem;
    }
    .office-req-review-modal .orc-reason-panel .orc-reason-body {
        font-size: 0.9rem;
        line-height: 1.52;
        font-weight: 500;
        color: #064e3b;
        margin: 0;
        white-space: pre-wrap;
        word-break: break-word;
    }
    html[data-theme="dark"] .office-req-review-modal .orc-reason-panel {
        border-color: rgba(52, 211, 153, 0.5);
        background: rgba(16, 185, 129, 0.16);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
    }
    html[data-theme="dark"] .office-req-review-modal .orc-reason-panel .orc-reason-label {
        color: #6ee7b7;
    }
    html[data-theme="dark"] .office-req-review-modal .orc-reason-panel .orc-reason-body {
        color: #ecfdf5;
        font-weight: 500;
    }
    .office-req-review-modal .modal-body {
        padding: 1rem 1.15rem 1.1rem;
    }
    .office-req-review-modal .modal-body .form-label {
        color: var(--dtr-heading);
        font-weight: 600;
        font-size: 0.82rem;
    }
    .office-req-review-modal .modal-body textarea.form-control {
        background: var(--dtr-input-bg);
        border-color: var(--dtr-input-border);
        color: var(--dtr-text);
        border-radius: 10px;
        min-height: 100px;
        font-size: 0.9rem;
        resize: vertical;
    }
    .office-req-review-modal .modal-body textarea.form-control::placeholder {
        color: var(--dtr-muted);
        opacity: 0.88;
    }
    .office-req-review-modal .modal-body textarea.form-control:focus {
        border-color: color-mix(in srgb, var(--dtr-primary) 55%, var(--dtr-input-border));
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--dtr-primary) 20%, transparent);
        outline: none;
        background: var(--dtr-input-bg);
    }
    .office-req-review-modal .modal-footer {
        border-top: 1px solid var(--dtr-border-soft);
        padding: 0.85rem 1.1rem 1rem;
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    @media (max-width: 520px) {
        .office-req-review-modal .modal-footer {
            flex-wrap: wrap;
        }
        .office-req-review-modal .modal-footer .orc-action-submit {
            flex: 1 1 auto;
            min-width: calc(50% - 0.25rem);
        }
    }
    .office-req-review-modal .orc-modal-btn-text {
        white-space: nowrap;
    }
    html[data-theme="dark"] .office-req-review-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
</style>
@endpush

<div class="modal fade office-req-review-modal" id="officeRequestReviewModal" tabindex="-1" aria-labelledby="officeRequestReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="officeReviewForm" action="">
                @csrf
                <div class="modal-header">
                    <div class="mrr-modal-head">
                        <span class="mrr-modal-icon" aria-hidden="true"><i class="bi bi-building-check"></i></span>
                        <div class="min-w-0">
                            <h5 class="modal-title" id="officeRequestReviewModalLabel">Review office request</h5>
                            <p class="mrr-modal-sub">Approve to apply the requested office immediately in coordinator filters.</p>
                            <div class="orc-summary-line">
                                <span class="orc-chip"><span class="orc-chip-muted">Student</span><span id="officeReviewStudent">Student</span></span>
                                <span class="orc-chip"><span class="orc-chip-muted">From</span><span id="officeReviewCurrent">-</span></span>
                                <span class="orc-chip"><span class="orc-chip-muted">To</span><span id="officeReviewRequested">-</span></span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="orc-reason-panel">
                        <div class="orc-reason-label">Student reason</div>
                        <p id="officeReviewReason" class="orc-reason-body mb-0">-</p>
                    </div>
                    <label for="officeReviewAdminRemarks" class="form-label mt-3">Admin remarks <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea
                        id="officeReviewAdminRemarks"
                        name="admin_remarks"
                        class="form-control"
                        rows="3"
                        maxlength="1000"
                        placeholder="Optional notes visible in the request record."
                    ></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn dtr-mbtn dtr-mbtn--cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit"
                            name="decision"
                            value="reject"
                            class="btn dtr-mbtn dtr-mbtn--danger-soft orc-action-submit"
                            title="Reject this office request"
                            aria-label="Reject office assignment request">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                        <span class="orc-modal-btn-text">Reject</span>
                    </button>
                    <button type="submit"
                            name="decision"
                            value="approve"
                            class="btn dtr-mbtn dtr-mbtn--success orc-action-submit"
                            title="Approve and apply the requested office in coordinator filters"
                            aria-label="Approve and apply requested office assignment">
                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                        <span class="orc-modal-btn-text">Approve</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
