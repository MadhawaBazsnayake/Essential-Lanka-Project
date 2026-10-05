/**
 * Essential Lanka - Modern Pill Alert & Confirmation System
 * Lightweight, Promise-based, glassmorphism UI
 */

(function () {
    // 1. Ensure Toast Container Exists
    function getToastContainer() {
        let container = document.getElementById('elp-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'elp-toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    // 2. Icon Mapper
    function getIconHtml(type) {
        switch (type) {
            case 'success':
                return '<i class="ph-bold ph-check"></i>';
            case 'error':
            case 'danger':
                return '<i class="ph-bold ph-x"></i>';
            case 'warning':
                return '<i class="ph-bold ph-warning"></i>';
            default:
                return '<i class="ph-bold ph-info"></i>';
        }
    }

    // 3. Pill Toast Notification
    window.elpToast = function (message, type = 'success', duration = 3500) {
        const container = getToastContainer();
        const toast = document.createElement('div');
        toast.className = `elp-toast-pill ${type === 'danger' ? 'error' : type}`;

        toast.innerHTML = `
            <div class="elp-toast-icon">${getIconHtml(type)}</div>
            <span class="elp-toast-msg">${message}</span>
            <button class="elp-toast-close" title="Close"><i class="ph-bold ph-x"></i></button>
        `;

        container.appendChild(toast);

        // Animate entrance
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        let timer = null;
        const removeToast = () => {
            if (timer) clearTimeout(timer);
            toast.classList.remove('show');
            toast.classList.add('hide');
            setTimeout(() => {
                if (toast.parentElement) toast.parentElement.removeChild(toast);
            }, 350);
        };

        toast.querySelector('.elp-toast-close').addEventListener('click', (e) => {
            e.stopPropagation();
            removeToast();
        });

        toast.addEventListener('click', removeToast);

        if (duration > 0) {
            timer = setTimeout(removeToast, duration);
        }
    };

    // 4. Pill Confirmation Dialog (Promise-based)
    window.elpConfirm = function (options = {}) {
        return new Promise((resolve) => {
            const {
                title = 'Are you sure?',
                message = 'Do you want to proceed with this action?',
                type = 'warning', // 'warning', 'danger', 'success', 'info'
                confirmText = 'Confirm',
                cancelText = 'Cancel',
                icon = null
            } = typeof options === 'string' ? { message: options } : options;

            const displayIcon = icon || (type === 'danger' ? 'ph-trash' : (type === 'success' ? 'ph-check-circle' : 'ph-question'));

            // Remove existing backdrop if any
            const existing = document.getElementById('elp-confirm-backdrop');
            if (existing) existing.remove();

            const backdrop = document.createElement('div');
            backdrop.id = 'elp-confirm-backdrop';
            backdrop.className = 'elp-confirm-backdrop';

            backdrop.innerHTML = `
                <div class="elp-confirm-pill" role="dialog" aria-modal="true">
                    <div class="elp-confirm-header">
                        <div class="elp-confirm-avatar ${type}">
                            <i class="ph-fill ${displayIcon}"></i>
                        </div>
                        <div class="elp-confirm-title-area">
                            <h3 class="elp-confirm-title">${title}</h3>
                            <p class="elp-confirm-subtitle">Confirmation required</p>
                        </div>
                    </div>
                    <div class="elp-confirm-body">
                        ${message}
                    </div>
                    <div class="elp-confirm-actions">
                        <button type="button" class="elp-btn-pill elp-btn-pill-cancel" id="elp-btn-cancel">
                            <i class="ph-bold ph-x"></i> ${cancelText}
                        </button>
                        <button type="button" class="elp-btn-pill elp-btn-pill-confirm ${type === 'danger' ? 'danger' : ''}" id="elp-btn-confirm">
                            <i class="ph-bold ${type === 'danger' ? 'ph-trash' : 'ph-check'}"></i> ${confirmText}
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(backdrop);

            // Animate open
            requestAnimationFrame(() => {
                backdrop.classList.add('active');
            });

            const closeDialog = (result) => {
                backdrop.classList.remove('active');
                setTimeout(() => {
                    if (backdrop.parentElement) backdrop.parentElement.removeChild(backdrop);
                    resolve(result);
                }, 280);
            };

            const cancelBtn = backdrop.querySelector('#elp-btn-cancel');
            const confirmBtn = backdrop.querySelector('#elp-btn-confirm');

            cancelBtn.addEventListener('click', () => closeDialog(false));
            confirmBtn.addEventListener('click', () => closeDialog(true));

            // Close on clicking backdrop outside pill
            backdrop.addEventListener('click', (e) => {
                if (e.target === backdrop) closeDialog(false);
            });

            // Close on ESC key
            const escListener = (e) => {
                if (e.key === 'Escape') {
                    window.removeEventListener('keydown', escListener);
                    closeDialog(false);
                }
            };
            window.addEventListener('keydown', escListener);
        });
    };

    // 5. Auto Detect URL Search Params for ?success= or ?error=
    document.addEventListener('DOMContentLoaded', () => {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const successMsg = urlParams.get('success');
            const errorMsg = urlParams.get('error');
            const infoMsg = urlParams.get('info');

            const statusMsg = urlParams.get('status');
            const messageParam = urlParams.get('message');

            const statusMap = {
                'profile_updated': { text: 'Profile updated successfully!', type: 'success' },
                'job_posted': { text: 'Your job has been published!', type: 'success' },
                'cancelled': { text: 'Job cancelled successfully.', type: 'warning' },
                'completed': { text: 'Job marked as complete.', type: 'success' },
                'invalid_file_type': { text: 'Invalid file type. Please upload JPG or PNG.', type: 'error' },
                'database_error': { text: 'A database error occurred. Please try again.', type: 'error' }
            };

            if (statusMsg && statusMap[statusMsg]) {
                elpToast(statusMap[statusMsg].text, statusMap[statusMsg].type);
            } else if (errorMsg && statusMap[errorMsg]) {
                elpToast(statusMap[errorMsg].text, statusMap[errorMsg].type);
            } else if (successMsg) {
                elpToast(decodeURIComponent(successMsg), 'success');
            } else if (errorMsg) {
                elpToast(decodeURIComponent(errorMsg), 'error');
            } else if (statusMsg) {
                elpToast(decodeURIComponent(statusMsg).replace(/_/g, ' '), 'info');
            } else if (messageParam) {
                elpToast(decodeURIComponent(messageParam), 'info');
            } else if (infoMsg) {
                elpToast(decodeURIComponent(infoMsg), 'info');
            }

            // Clean up URL without reload so refresh doesn't reshow
            if (successMsg || errorMsg || infoMsg || statusMsg || messageParam) {
                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
            }
        } catch (e) {
            console.warn('URL params parsing notice:', e);
        }
    });

})();
