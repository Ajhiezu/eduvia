export function childSwitcher() {
    return {
        busyChildId: null,
        errorMessage: '',
        async selectChild(switchUrl, childId, returnUrl) {
            if (this.busyChildId !== null) {
                return;
            }
            this.busyChildId = childId;
            this.errorMessage = '';
            try {
                const response = await fetch(switchUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'text/html',
                    },
                });
                if (!response.ok) {
                    throw new Error('Child selection failed');
                }
                if (new URL(response.url, window.location.href).pathname === '/login') {
                    window.location.assign(response.url);
                    return;
                }
                window.location.assign(returnUrl);
            } catch {
                this.errorMessage = 'Anak belum berhasil diganti. Periksa koneksi lalu coba kembali.';
                this.busyChildId = null;
            }
        },
    };
}

export function toastNotifications(initial = {}) {
    return {
        toasts: [],
        nextId: 0,
        timers: new Map(),
        init() {
            this.$nextTick(() => {
                for (const [type, message] of Object.entries(initial)) {
                    const inlineFeedback = Array.from(document.querySelectorAll('main [data-feedback]'));
                    if (message && !inlineFeedback.some((element) => element.textContent.includes(message))) {
                        this.add(message, type);
                    }
                }
            });
        },
        add(message, type = 'info') {
            if (!message) {
                return;
            }
            type = type === 'error' ? 'danger' : type;
            if (!['success', 'danger', 'warning', 'info'].includes(type)) {
                type = 'info';
            }
            if (this.toasts.length >= 5) {
                this.remove(this.toasts[0].id);
            }
            const toast = { id: ++this.nextId, message: String(message), type };
            this.toasts.push(toast);
            this.resume(toast);
        },
        pause(id) {
            clearTimeout(this.timers.get(id));
            this.timers.delete(id);
        },
        resume(toast) {
            this.pause(toast.id);
            if (toast.type !== 'danger') {
                this.timers.set(toast.id, setTimeout(() => this.remove(toast.id), 7000));
            }
        },
        remove(id) {
            this.pause(id);
            this.toasts = this.toasts.filter((toast) => toast.id !== id);
        },
        destroy() {
            for (const id of this.timers.keys()) {
                this.pause(id);
            }
        },
    };
}

export function initializeFormFeedback(root = document) {
    const confirmedForms = new WeakSet();
    const busyForms = new Map();
    const dialog = root.querySelector('#application-confirm-dialog');
    let pendingConfirmation = null;

    const restoreForms = () => {
        for (const [form, state] of busyForms) {
            if (state.busy === null) {
                form.removeAttribute('aria-busy');
            } else {
                form.setAttribute('aria-busy', state.busy);
            }
            for (const [button, disabled] of state.buttons) {
                button.classList.remove('is-submitting');
                button.removeAttribute('aria-busy');
                if (disabled === null) {
                    button.removeAttribute('aria-disabled');
                } else {
                    button.setAttribute('aria-disabled', disabled);
                }
            }
        }
        busyForms.clear();
    };

    root.addEventListener('submit', (event) => {
        if (busyForms.has(event.target)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);

    root.addEventListener('submit', (event) => {
        const form = event.target;
        if (event.defaultPrevented || !(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.dataset.confirm && !confirmedForms.delete(form)) {
            if (dialog && typeof dialog.showModal === 'function') {
                event.preventDefault();
                if (dialog.open) {
                    return;
                }
                pendingConfirmation = { form, submitter: event.submitter };
                dialog.querySelector('[data-confirm-message]').textContent = form.dataset.confirm;
                dialog.showModal();
                dialog.querySelector('[data-confirm-cancel]').focus();
                return;
            }
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
                return;
            }
        }

        const target = event.submitter?.getAttribute('formtarget') || form.target;
        const method = event.submitter?.getAttribute('formmethod') || form.method;
        if (method.toLowerCase() === 'get' || method.toLowerCase() === 'dialog' || (target && target !== '_self')) {
            return;
        }
        const buttons = Array.from(form.querySelectorAll('button:not([type]), button[type="submit"], input[type="submit"]'));
        busyForms.set(form, {
            busy: form.getAttribute('aria-busy'),
            buttons: buttons.map((button) => [button, button.getAttribute('aria-disabled')]),
        });
        form.setAttribute('aria-busy', 'true');
        for (const button of buttons) {
            // Keep submitter names and values available to the native submission.
            button.setAttribute('aria-disabled', 'true');
            if (button === event.submitter) {
                button.classList.add('is-submitting');
                button.setAttribute('aria-busy', 'true');
            }
        }
    });

    if (dialog) {
        dialog.querySelector('[data-confirm-cancel]').addEventListener('click', () => dialog.close());
        dialog.querySelector('[data-confirm-accept]').addEventListener('click', () => {
            const pending = pendingConfirmation;
            dialog.close();
            if (pending?.form.isConnected) {
                confirmedForms.add(pending.form);
                pending.form.requestSubmit(pending.submitter || undefined);
            }
        });
        dialog.addEventListener('close', () => {
            pendingConfirmation = null;
        });
    }

    window.addEventListener('pageshow', restoreForms);
    root.querySelector('main form .form-control[aria-invalid="true"]:not(:disabled)')?.focus();
}
