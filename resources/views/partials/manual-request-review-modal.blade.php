@push('styles')
<style>
    .manual-req-review-modal .modal-content {
        border-radius: 14px;
        border: 1px solid var(--dtr-border-soft);
        background: var(--dtr-card-bg);
        color: var(--dtr-text);
        box-shadow: var(--dtr-shadow-strong, 0 18px 50px rgba(15, 23, 42, 0.2));
        overflow: hidden;
    }
    .manual-req-review-modal .modal-header {
        border-bottom: 1px solid var(--dtr-border-soft);
        padding: 1rem 1.15rem;
        gap: 0.75rem;
        align-items: flex-start;
    }
    .manual-req-review-modal .mrr-modal-head {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        flex: 1;
        min-width: 0;
    }
    .manual-req-review-modal .mrr-modal-icon {
        flex: 0 0 auto;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }
    .manual-req-review-modal .mrr-modal-icon--approve {
        color: #047857;
        background: color-mix(in srgb, #059669 14%, transparent);
        border: 1px solid color-mix(in srgb, #059669 32%, var(--dtr-border-soft));
    }
    .manual-req-review-modal .mrr-modal-icon--reject {
        color: #dc2626;
        background: color-mix(in srgb, #dc2626 14%, transparent);
        border: 1px solid color-mix(in srgb, #dc2626 28%, var(--dtr-border-soft));
    }
    html[data-theme="dark"] .manual-req-review-modal .mrr-modal-icon--approve {
        color: #6ee7b7;
        background: rgba(16, 185, 129, 0.14);
        border-color: rgba(52, 211, 153, 0.35);
    }
    html[data-theme="dark"] .manual-req-review-modal .mrr-modal-icon--reject {
        color: #fca5a5;
        background: rgba(248, 113, 113, 0.12);
        border-color: rgba(248, 113, 113, 0.35);
    }
    .manual-req-review-modal .mrr-modal-head .modal-title {
        color: var(--dtr-heading);
        font-weight: 700;
        font-size: 1.0625rem;
        letter-spacing: -0.02em;
        line-height: 1.3;
        margin-bottom: 0;
    }
    .manual-req-review-modal .mrr-modal-sub {
        color: var(--dtr-muted);
        font-size: 0.835rem;
        line-height: 1.45;
        margin-top: 0.2rem;
        margin-bottom: 0;
    }
    .manual-req-review-modal .mrr-modal-target {
        display: inline-block;
        margin-top: 0.65rem;
        padding: 0.35rem 0.62rem;
        border-radius: 8px;
        font-size: 0.8125rem;
        font-weight: 600;
        letter-spacing: 0.015em;
        color: var(--dtr-heading);
        background: var(--dtr-surface-soft);
        border: 1px solid var(--dtr-border-soft);
        word-break: break-word;
        max-width: 100%;
    }
    .manual-req-review-modal .modal-body .form-label {
        color: var(--dtr-heading);
        font-weight: 600;
        font-size: 0.82rem;
    }
    .manual-req-review-modal .modal-body textarea.form-control {
        background: var(--dtr-input-bg);
        border-color: var(--dtr-input-border);
        color: var(--dtr-text);
        border-radius: 10px;
        min-height: 118px;
        font-size: 0.9rem;
        resize: vertical;
    }
    .manual-req-review-modal .modal-body textarea.form-control::placeholder {
        color: var(--dtr-muted);
        opacity: 0.88;
    }
    .manual-req-review-modal .modal-body textarea.form-control:focus {
        border-color: color-mix(in srgb, var(--dtr-primary) 55%, var(--dtr-input-border));
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--dtr-primary) 20%, transparent);
        outline: none;
        color: var(--dtr-text);
        background: var(--dtr-input-bg);
    }
    .manual-req-review-modal .modal-footer {
        border-top: 1px solid var(--dtr-border-soft);
        padding: 0.95rem 1.15rem 1.05rem;
        gap: 0.65rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    html[data-theme="dark"] .manual-req-review-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
</style>
@endpush

<div class="modal fade manual-req-review-modal" id="manualRequestReviewModal" tabindex="-1" aria-labelledby="manualRequestReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="manualRequestReviewForm">
                @csrf
                <input type="hidden" name="decision" id="manualRequestReviewDecision" value="">
                <div class="modal-header">
                    <div class="mrr-modal-head">
                        <span class="mrr-modal-icon mrr-modal-icon--approve" id="manualRequestReviewIconWrap" aria-hidden="true">
                            <i class="bi bi-check-lg" id="manualRequestReviewIconI"></i>
                        </span>
                        <div class="min-w-0">
                            <h5 class="modal-title" id="manualRequestReviewModalLabel">Review request</h5>
                            <p class="mrr-modal-sub" id="manualRequestReviewSub"></p>
                            <span class="mrr-modal-target" id="manualRequestReviewSummary"></span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="manualRequestReviewNote" class="form-label">Coordinator / admin note <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea id="manualRequestReviewNote" name="coordinator_note" class="form-control" rows="4" maxlength="1500" placeholder="Visible on the request history — e.g. approved per logbook, rejected as duplicate, etc."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn dtr-mbtn dtr-mbtn--cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn dtr-mbtn dtr-mbtn--success" id="manualRequestReviewConfirm">
                        <i class="bi bi-send" aria-hidden="true"></i>
                        <span id="manualRequestReviewConfirmLabel">Confirm</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var form = document.getElementById('manualRequestReviewForm');
    var decisionInput = document.getElementById('manualRequestReviewDecision');
    var summaryEl = document.getElementById('manualRequestReviewSummary');
    var subEl = document.getElementById('manualRequestReviewSub');
    var titleEl = document.getElementById('manualRequestReviewModalLabel');
    var confirmBtn = document.getElementById('manualRequestReviewConfirm');
    var confirmLabel = document.getElementById('manualRequestReviewConfirmLabel');
    var iconWrap = document.getElementById('manualRequestReviewIconWrap');
    var iconI = document.getElementById('manualRequestReviewIconI');
    var noteTa = document.getElementById('manualRequestReviewNote');
    if (!form || !decisionInput || !summaryEl || !subEl || !titleEl || !confirmBtn || !confirmLabel || !iconWrap || !iconI || !noteTa) return;

    var defaultSub = {
        approve: 'Posting creates or updates attendance for the filing date. Add an optional note for the student or audit trail.',
        reject: 'The student’s record for that date is unchanged. A short note helps them understand the decision.'
    };

    document.querySelectorAll('.js-open-manual-review-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var action = btn.getAttribute('data-action') || '#';
            form.setAttribute('action', action);

            var decision = btn.getAttribute('data-decision') || '';
            decisionInput.value = decision;

            titleEl.textContent = btn.getAttribute('data-modal-title') || 'Review request';
            summaryEl.textContent = btn.getAttribute('data-target-summary') || '';
            var customSub = btn.getAttribute('data-modal-sub');
            subEl.textContent = customSub || (decision === 'approve' ? defaultSub.approve : defaultSub.reject);

            noteTa.value = '';

            var isApprove = decision === 'approve';
            confirmBtn.className =
                'btn dtr-mbtn ' + (isApprove ? 'dtr-mbtn--success' : 'dtr-mbtn--danger-soft');
            iconWrap.className = 'mrr-modal-icon ' + (isApprove ? 'mrr-modal-icon--approve' : 'mrr-modal-icon--reject');
            iconI.className = 'bi ' + (isApprove ? 'bi-check-lg' : 'bi-x-lg');

            confirmLabel.textContent = isApprove ? 'Approve & post attendance' : 'Reject request';

            var cMsg = btn.getAttribute('data-confirm-message') || '';
            var cVar = btn.getAttribute('data-confirm-variant') || 'neutral';
            if (cMsg) {
                confirmBtn.setAttribute('data-norsu-confirm', cMsg);
                confirmBtn.setAttribute('data-norsu-variant', cVar);
            } else {
                confirmBtn.removeAttribute('data-norsu-confirm');
                confirmBtn.removeAttribute('data-norsu-variant');
            }
        });
    });
})();
</script>
@endpush
