/**
 * Theme-aware confirmations & alerts using Bootstrap Modal.
 * Depends on Bootstrap 5 bundle (already on admin/coordinator/student layouts).
 */
(function () {
    'use strict';

    var modalEl;
    var modalInstance;
    var mode = 'confirm';
    var resolvePending;

    function getBootstrapModal() {
        if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            throw new Error('norsu-dtr-dialogs: Bootstrap Modal not found');
        }
        return bootstrap.Modal;
    }

    function ensureDom() {
        if (modalEl) return;
        modalEl = document.createElement('div');
        modalEl.id = 'norsuDtrDialogModal';
        modalEl.className = 'modal fade norsu-dtr-dialog';
        modalEl.tabIndex = -1;
        modalEl.setAttribute('aria-labelledby', 'norsuDtrDialogTitle');
        modalEl.innerHTML =
            '<div class="modal-dialog modal-dialog-centered">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<span class="norsu-dtr-dialog__icon-area" aria-hidden="true"><i class="bi"></i></span>' +
            '<h5 class="modal-title norsu-dtr-dialog__title flex-grow-1" id="norsuDtrDialogTitle"></h5>' +
            '<button type="button" class="btn-close-norsu norsu-dtr-dialog-dismiss" aria-label="Close"><i class="bi bi-x-lg"></i></button>' +
            '</div>' +
            '<div class="modal-body">' +
            '<div class="norsu-dtr-dialog__body" id="norsuDtrDialogBody"></div>' +
            '</div>' +
            '<div class="modal-footer">' +
            '<button type="button" class="btn norsu-dtr-dialog-btn norsu-dtr-dialog-btn--cancel norsu-dtr-dialog-cancel" id="norsuDtrCancelBtn"></button>' +
            '<button type="button" class="btn norsu-dtr-dialog-btn norsu-dtr-dialog-confirm norsu-dtr-dialog-btn--ok" id="norsuDtrConfirmBtn"></button>' +
            '</div>' +
            '</div></div>';
        document.body.appendChild(modalEl);

        modalInstance = new (getBootstrapModal())(modalEl, { backdrop: 'static', keyboard: true });

        modalEl.addEventListener('hidden.bs.modal', function () {
            modalEl.classList.remove(
                'norsu-dtr-dialog--neutral',
                'norsu-dtr-dialog--warning',
                'norsu-dtr-dialog--danger'
            );
            if (!resolvePending) return;
            var r = resolvePending;
            resolvePending = null;
            if (mode === 'confirm') {
                r(false);
            } else {
                r();
            }
        });

        modalEl.querySelector('.norsu-dtr-dialog-dismiss').addEventListener('click', function () {
            modalInstance.hide();
        });
        modalEl.querySelector('.norsu-dtr-dialog-cancel').addEventListener('click', function () {
            modalInstance.hide();
        });
        modalEl.querySelector('.norsu-dtr-dialog-confirm').addEventListener('click', function () {
            if (resolvePending && mode === 'confirm') {
                var r = resolvePending;
                resolvePending = null;
                r(true);
            } else if (resolvePending && mode === 'alert') {
                var ar = resolvePending;
                resolvePending = null;
                ar();
            }
            modalInstance.hide();
        });
    }

    function normalizeArgs(first, second) {
        var message = '';
        var opts = {};
        if (typeof first === 'object' && first !== null && !Array.isArray(first)) {
            opts = first;
            message = opts.message != null ? String(opts.message) : '';
        } else {
            message = first != null ? String(first) : '';
            opts = typeof second === 'object' && second !== null ? second : {};
        }
        var variant = opts.variant;
        if (
            variant !== 'danger' &&
            variant !== 'warning' &&
            variant !== 'neutral'
        ) {
            variant = 'neutral';
        }
        return {
            message: message,
            title: opts.title != null ? String(opts.title) : '',
            variant: variant,
            confirmText:
                opts.confirmText != null ? String(opts.confirmText) : null,
            cancelText: opts.cancelText != null ? String(opts.cancelText) : null
        };
    }

    function setBody(bodyEl, text) {
        bodyEl.innerHTML = '';
        var lines = String(text || '').split('\n');
        for (var i = 0; i < lines.length; i++) {
            var p = document.createElement('p');
            p.className = 'norsu-dtr-dialog__line mb-2';
            p.textContent = lines[i];
            bodyEl.appendChild(p);
        }
        var paras = bodyEl.querySelectorAll('p');
        if (paras.length) paras[paras.length - 1].classList.remove('mb-2');
    }

    function defaultTitle(kind, variant) {
        if (kind === 'alert') {
            return variant === 'danger' ? 'Something went wrong' : variant === 'warning' ? 'Please note' : 'Notice';
        }
        return variant === 'danger' ? 'Confirm action' : variant === 'warning' ? 'Please confirm' : 'Confirm';
    }

    function iconClass(variant) {
        if (variant === 'danger') return 'bi bi-exclamation-octagon-fill';
        if (variant === 'warning') return 'bi bi-exclamation-triangle-fill';
        return 'bi bi-info-circle-fill';
    }

    function showDialog(kind, first, second) {
        ensureDom();
        var n = normalizeArgs(first, second);
        mode = kind;

        modalEl.classList.remove(
            'norsu-dtr-dialog--neutral',
            'norsu-dtr-dialog--warning',
            'norsu-dtr-dialog--danger'
        );
        modalEl.classList.add(
            'norsu-dtr-dialog--' + (n.variant === 'danger' ? 'danger' : n.variant === 'warning' ? 'warning' : 'neutral')
        );

        var titleEl = modalEl.querySelector('.norsu-dtr-dialog__title');
        var bodyEl = document.getElementById('norsuDtrDialogBody');
        var iconWrap = modalEl.querySelector('.norsu-dtr-dialog__icon-area i');
        var cancelBtn = modalEl.querySelector('.norsu-dtr-dialog-cancel');
        var confirmBtn = modalEl.querySelector('.norsu-dtr-dialog-confirm');

        titleEl.textContent = n.title || defaultTitle(kind, n.variant);
        setBody(bodyEl, n.message);
        if (iconWrap) {
            iconWrap.className = iconClass(n.variant);
        }

        var confirmLabel =
            n.confirmText ||
            (kind === 'alert'
                ? 'OK'
                : n.variant === 'danger'
                  ? 'Yes, proceed'
                  : 'Confirm');
        confirmBtn.textContent = confirmLabel;
        cancelBtn.textContent =
            n.cancelText != null ? n.cancelText : 'Cancel';

        confirmBtn.style.display = 'inline-flex';

        cancelBtn.style.display =
            kind === 'confirm' ? 'inline-flex' : 'none';

        confirmBtn.className =
            'btn norsu-dtr-dialog-btn norsu-dtr-dialog-confirm ' +
            (kind === 'alert'
                ? 'norsu-dtr-dialog-btn--ok'
                : n.variant === 'danger'
                  ? 'norsu-dtr-dialog-btn--confirm-danger'
                  : n.variant === 'warning'
                    ? 'norsu-dtr-dialog-btn--confirm-warning'
                    : 'norsu-dtr-dialog-btn--confirm-neutral');

        return new Promise(function (resolve) {
            resolvePending = resolve;
            modalInstance.show();
        });
    }

    function attachFormConfirmDelegates() {
        document.addEventListener(
            'submit',
            function (e) {
                var form = e.target;
                if (!(form instanceof HTMLFormElement)) return;
                if (form.dataset.norsuConfirmBypass === '1') {
                    delete form.dataset.norsuConfirmBypass;
                    return;
                }
                var sub = e.submitter || null;
                var msgAttr = '';
                var variantAttr = '';
                if (sub && sub.getAttribute) {
                    msgAttr =
                        sub.getAttribute('data-norsu-confirm') || '';
                    variantAttr =
                        sub.getAttribute('data-norsu-variant') || '';
                }
                if (!msgAttr && form.getAttribute) {
                    msgAttr = form.getAttribute('data-norsu-confirm') || '';
                }
                if (!msgAttr) return;
                if (!variantAttr && form.getAttribute) {
                    variantAttr =
                        form.getAttribute('data-norsu-variant') || '';
                }

                var variant =
                    variantAttr === 'danger' || variantAttr === 'warning'
                        ? variantAttr
                        : 'neutral';

                e.preventDefault();
                e.stopPropagation();

                confirm(msgAttr, { variant: variant }).then(function (ok) {
                    if (!ok) return;
                    form.dataset.norsuConfirmBypass = '1';
                    if (
                        sub &&
                        typeof HTMLFormElement.prototype.requestSubmit ===
                            'function'
                    ) {
                        try {
                            form.requestSubmit(sub);
                        } catch (err) {
                            form.submit();
                        }
                    } else {
                        form.submit();
                    }
                });
            },
            true
        );
    }

    function confirm(first, second) {
        return showDialog('confirm', first, second);
    }

    function alert(first, second) {
        return showDialog('alert', first, second);
    }

    window.norsuPrompt = {
        confirm: confirm,
        alert: alert,
        _ready: true
    };

    function init() {
        attachFormConfirmDelegates();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
