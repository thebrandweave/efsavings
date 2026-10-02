<!-- admin/components/delete_undo.php -->
<style>
/* 4-Second Persistent Undo Bottom Fadeup Toast */
.bottom-undo-toast {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(15, 23, 42, 0.96);
    backdrop-filter: blur(12px);
    color: #f8fafc;
    padding: 14px 18px 16px 18px;
    border-radius: 12px;
    box-shadow: 0 14px 35px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.12);
    z-index: 99999999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: calc(100% - 32px);
    max-width: 440px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    animation: undoFadeUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    pointer-events: auto;
}

.bottom-undo-toast.fade-down {
    animation: undoFadeDown 0.3s cubic-bezier(0.7, 0, 0.84, 0) forwards;
}

@keyframes undoFadeUp {
    0% {
        opacity: 0;
        transform: translate(-50%, 40px) scale(0.96);
    }
    100% {
        opacity: 1;
        transform: translate(-50%, 0) scale(1);
    }
}

@keyframes undoFadeDown {
    0% {
        opacity: 1;
        transform: translate(-50%, 0) scale(1);
    }
    100% {
        opacity: 0;
        transform: translate(-50%, 35px) scale(0.96);
    }
}

.bottom-undo-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.bottom-undo-text {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: #f1f5f9;
    font-weight: 500;
}

.bottom-undo-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

.bottom-undo-badge {
    background: rgba(245, 158, 11, 0.25);
    color: #fbbf24;
    padding: 2px 7px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 13px;
    font-variant-numeric: tabular-nums;
}

.bottom-undo-btn {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #ffffff !important;
    border: none;
    padding: 7px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35);
    transition: all 0.2s ease;
    flex-shrink: 0;
    text-decoration: none;
}

.bottom-undo-btn:hover {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(245, 158, 11, 0.5);
    color: #ffffff !important;
}

.bottom-undo-btn:active {
    transform: translateY(0);
}

.bottom-undo-progress-track {
    width: 100%;
    height: 4px;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 4px;
    overflow: hidden;
}

.bottom-undo-progress-bar {
    height: 100%;
    width: 100%;
    background: linear-gradient(90deg, #f59e0b, #ef4444);
    border-radius: 4px;
}

/* Toast notice */
.bottom-undo-notice {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    background: #065f46;
    color: #ecfdf5;
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    z-index: 99999999;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.15);
    animation: undoFadeUp 0.25s ease forwards;
}
</style>

<script>
(function() {
    const STORAGE_KEY = 'efsavings_pending_delete';
    const DEFAULT_DURATION = 4000; // 4 seconds
    let activeUndo = null;

    function mountElement(el) {
        if (document.body) {
            document.body.appendChild(el);
        } else {
            document.addEventListener('DOMContentLoaded', () => {
                document.body.appendChild(el);
            });
        }
    }

    function showNotice(html, bg) {
        const notice = document.createElement('div');
        notice.className = 'bottom-undo-notice';
        if (bg) notice.style.background = bg;
        notice.innerHTML = html;
        mountElement(notice);
        setTimeout(() => {
            notice.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            notice.style.opacity = '0';
            notice.style.transform = 'translate(-50%, 25px)';
            setTimeout(() => notice.remove(), 300);
        }, 2500);
    }

    function isDeleteTarget(el) {
        if (!el) return null;
        const target = el.closest('a, button, [role="button"], input[type="submit"]');
        if (!target) return null;
        if (target.closest('.bottom-undo-toast') || target.closest('.bottom-undo-notice')) return null;

        const href = target.getAttribute('href') || '';
        const classList = target.className || '';
        const name = target.getAttribute('name') || '';
        const onclickAttr = target.getAttribute('onclick') || '';
        const titleAttr = target.getAttribute('title') || '';

        // Form check
        const isFormEl = (target.type === 'submit' || target.tagName === 'BUTTON' || target.tagName === 'INPUT') && !!target.form;
        const form = isFormEl ? target.form : null;
        const formOnsubmit = form ? (form.getAttribute('onsubmit') || '') : '';
        const formAction = form ? (form.getAttribute('action') || '') : '';

        const isDeleteHref = /(\?|&)delete=|\baction=delete\b|\bdelete\.php\b/i.test(href);
        const isDeleteClass = /\b(delete-btn|btn-delete|delete)\b/i.test(classList);
        const isDeleteName = /delete/i.test(name);
        const isDeleteOnclick = /confirm.*delete/i.test(onclickAttr);
        const isDeleteTitle = /delete/i.test(titleAttr);
        const isDeleteForm = form && (/confirm.*delete/i.test(formOnsubmit) || /(\?|&)delete=|\baction=delete\b/i.test(formAction));

        if (isDeleteHref || isDeleteClass || isDeleteName || isDeleteOnclick || isDeleteTitle || isDeleteForm) {
            // Build full absolute URL
            let fullUrl = '';
            if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
                try {
                    fullUrl = new URL(href, window.location.href).href;
                } catch(e) {
                    fullUrl = target.href || href;
                }
            }

            // Build form details if submit
            let formDetails = null;
            if (isFormEl && form) {
                const formData = {};
                try {
                    new FormData(form).forEach((val, key) => {
                        formData[key] = val;
                    });
                } catch(e) {}
                if (target.name) {
                    formData[target.name] = target.value || '1';
                }
                let fullAction = window.location.href;
                try {
                    fullAction = new URL(form.getAttribute('action') || window.location.href, window.location.href).href;
                } catch(e) {}

                formDetails = {
                    action: fullAction,
                    data: formData
                };
            }

            return {
                element: target,
                url: fullUrl,
                isForm: isFormEl,
                form: form,
                formDetails: formDetails,
                hasInlineConfirm: /confirm\s*\(/i.test(onclickAttr)
            };
        }
        return null;
    }

    // Intercept click on document during bubble phase
    document.addEventListener('click', function(e) {
        const info = isDeleteTarget(e.target);
        if (!info) return;

        // If user cancelled the confirmation dialog, inline onclick returned false
        if (e.defaultPrevented) {
            return;
        }

        // If already counting down on this same element, ignore double click
        if (activeUndo && activeUndo.element === info.element) {
            e.preventDefault();
            e.stopPropagation();
            return;
        }

        // Handle form onsubmit confirm if button didn't have inline confirm
        if (info.isForm && info.form && !info.hasInlineConfirm && typeof info.form.onsubmit === 'function') {
            const confirmResult = info.form.onsubmit.call(info.form);
            if (confirmResult === false) {
                e.preventDefault();
                e.stopPropagation();
                return;
            }
            info.hasInlineConfirm = true;
        }

        // If the element did not have an inline confirm dialog, show one now
        if (!info.hasInlineConfirm) {
            const confirmed = confirm('Are you sure you want to delete this? This action cannot be undone.');
            if (!confirmed) {
                e.preventDefault();
                e.stopPropagation();
                return;
            }
        }

        // Confirmation accepted! Prevent immediate deletion and show 4s undo fadeup
        e.preventDefault();
        e.stopPropagation();

        // If another delete is currently in countdown, execute it immediately
        const existingRaw = localStorage.getItem(STORAGE_KEY);
        if (existingRaw) {
            try {
                const existing = JSON.parse(existingRaw);
                executeDelete(existing);
            } catch(err) {}
        }

        const now = Date.now();
        const pending = {
            id: 'del_' + now,
            url: info.url,
            originUrl: window.location.href,
            isForm: info.isForm,
            formAction: info.formDetails ? info.formDetails.action : '',
            formData: info.formDetails ? info.formDetails.data : null,
            startedAt: now,
            expiresAt: now + DEFAULT_DURATION,
            duration: DEFAULT_DURATION
        };

        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(pending));
        } catch(err) {}

        showUndoToast(pending, DEFAULT_DURATION, info.element);
    }, false);

    function showUndoToast(pending, remainingMs, sourceElement) {
        if (!pending) return;

        const duration = pending.duration || DEFAULT_DURATION;
        const initialSeconds = Math.max(1, Math.ceil(remainingMs / 1000));
        const initialPercent = Math.max(0, Math.min(100, (remainingMs / duration) * 100));

        // Create toast element
        const toast = document.createElement('div');
        toast.className = 'bottom-undo-toast';
        toast.innerHTML = `
            <div class="bottom-undo-content">
                <div class="bottom-undo-text">
                    <span class="bottom-undo-icon"><i class="fas fa-trash-alt"></i></span>
                    <span>Deleting in <span class="bottom-undo-badge" id="undoSecCount">${initialSeconds}s</span></span>
                </div>
                <button type="button" class="bottom-undo-btn" id="undoActionBtn">
                    <i class="fas fa-undo"></i> Undo
                </button>
            </div>
            <div class="bottom-undo-progress-track">
                <div class="bottom-undo-progress-bar" id="undoProgressBar" style="width: ${initialPercent}%;"></div>
            </div>
        `;

        mountElement(toast);

        // Start smooth progress bar transition to 0%
        setTimeout(() => {
            const progressBar = toast.querySelector('#undoProgressBar');
            if (progressBar) {
                progressBar.style.transition = `width ${remainingMs / 1000}s linear`;
                progressBar.style.width = '0%';
            }
        }, 30);

        const countdownEl = toast.querySelector('#undoSecCount');
        const undoBtn = toast.querySelector('#undoActionBtn');
        let isDone = false;

        function cleanupToast() {
            toast.classList.add('fade-down');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }

        const timer = setInterval(() => {
            const msLeft = pending.expiresAt - Date.now();
            if (msLeft <= 0) {
                if (isDone) return;
                isDone = true;
                clearInterval(timer);
                cleanupToast();
                executeDelete(pending);
            } else {
                const secs = Math.max(1, Math.ceil(msLeft / 1000));
                if (countdownEl) {
                    countdownEl.textContent = secs + 's';
                }
            }
        }, 150);

        function handleCancel() {
            if (isDone) return;
            isDone = true;
            clearInterval(timer);
            activeUndo = null;
            try {
                localStorage.removeItem(STORAGE_KEY);
            } catch(err) {}
            cleanupToast();
            showNotice('<i class="fas fa-check-circle" style="margin-right: 6px;"></i> Deletion cancelled', '#065f46');
        }

        undoBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            handleCancel();
        });

        activeUndo = {
            element: sourceElement || null,
            executeNow: () => {
                if (isDone) return;
                isDone = true;
                clearInterval(timer);
                cleanupToast();
                executeDelete(pending);
            },
            cancelNow: handleCancel,
            cleanupToast: cleanupToast
        };
    }

    function executeDelete(pending) {
        if (!pending) return;
        activeUndo = null;

        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch(err) {}

        const isSamePage = (window.location.href === pending.originUrl);

        if (isSamePage) {
            // Still on the original page: navigate/submit natively so page refreshes with server messages
            const globalLoader = document.getElementById('globalLoader');
            if (globalLoader) {
                globalLoader.classList.remove('hide');
            }

            if (pending.isForm && pending.formAction) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = pending.formAction;
                for (const [key, val] of Object.entries(pending.formData || {})) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = val;
                    form.appendChild(input);
                }
                document.body.appendChild(form);
                form.submit();
            } else if (pending.url) {
                window.location.href = pending.url;
            }
        } else {
            // User changed sidebar/page! Execute via background fetch so user is not forcibly redirected away
            if (pending.isForm && pending.formAction) {
                try {
                    const body = new URLSearchParams(pending.formData || {});
                    fetch(pending.formAction, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: body,
                        credentials: 'same-origin'
                    }).catch(console.error);
                } catch(err) {}
            } else if (pending.url) {
                try {
                    fetch(pending.url, {
                        method: 'GET',
                        credentials: 'same-origin'
                    }).catch(console.error);
                } catch(err) {}
            }

            // Show confirmation on current page
            showNotice('<i class="fas fa-check-circle" style="margin-right: 6px;"></i>Deleted successfully', '#065f46');
        }
    }

    function checkPendingDeleteOnLoad() {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return;

        try {
            const pending = JSON.parse(raw);
            if (!pending || !pending.expiresAt) {
                localStorage.removeItem(STORAGE_KEY);
                return;
            }

            const now = Date.now();
            const msLeft = pending.expiresAt - now;

            // If stale by more than 30s (e.g. browser closed for hours), discard safely
            if (now - pending.expiresAt > 30000) {
                localStorage.removeItem(STORAGE_KEY);
                return;
            }

            if (msLeft <= 50) {
                // Expired during page navigation: execute now
                executeDelete(pending);
            } else {
                // Resume toast with remaining time
                showUndoToast(pending, msLeft, null);
            }
        } catch(err) {
            localStorage.removeItem(STORAGE_KEY);
        }
    }

    // Check for active pending delete as soon as DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkPendingDeleteOnLoad);
    } else {
        checkPendingDeleteOnLoad();
    }

    // Cross-tab sync: if another tab cancels or executes, dismiss toast here too
    window.addEventListener('storage', function(e) {
        if (e.key === STORAGE_KEY && !e.newValue && activeUndo) {
            activeUndo.cleanupToast();
            activeUndo = null;
        }
    });
})();
</script>
