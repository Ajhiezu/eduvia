const protectedNames = new Set(['id', 'password', 'password_confirmation', 'token', '_token', '_method', 'school_id', 'tenant_id', 'user_id', 'teacher_id', 'student_id', 'parent_id', 'role', 'roles', 'permissions', 'is_admin', 'is_super_admin', 'api_key']);
const supportedTypes = new Set(['text', 'textarea', 'number', 'date', 'datetime-local', 'time', 'select', 'radio']);

export function isSafeAgentField(name) {
    return /^[a-zA-Z][a-zA-Z0-9_.\[\]-]*$/.test(name) && !name.toLowerCase().split(/[.\[\]]+/).some(part => protectedNames.has(part));
}

function fieldType(element) {
    return element.tagName === 'TEXTAREA' ? 'textarea' : element.tagName === 'SELECT' ? 'select' : element.type;
}

function isAvailableField(element) {
    return isSafeAgentField(element.name || '') && supportedTypes.has(fieldType(element)) && !element.disabled && !element.readOnly && element.getClientRects().length > 0 && !element.closest('[data-ai-ignore], [data-ai-agent], [inert]');
}

export function describeAgentForm(form) {
    const elements = Array.from(form.elements).filter(isAvailableField);
    const seen = new Set();
    return elements.flatMap(element => {
        if (seen.has(element.name) || seen.size >= 30) return [];
        seen.add(element.name);
        const type = fieldType(element);
        const label = element.getAttribute('aria-label') || element.labels?.[0]?.textContent || element.closest('div')?.querySelector('label')?.textContent || element.placeholder || element.name;
        const field = { name: element.name, label: label.replace(/\s+/g, ' ').trim().slice(0, 120), type };
        if (type === 'select') {
            field.options = Array.from(element.options).filter(option => option.value && !option.disabled).slice(0, 50).map(option => ({ value: option.value.slice(0, 200), label: option.textContent.trim().slice(0, 200) }));
        } else if (type === 'radio') {
            field.options = elements.filter(item => item.name === element.name && item.type === 'radio').slice(0, 50).map(item => ({ value: item.value, label: item.value }));
        }
        return [field];
    });
}

export function applyAgentFields(form, patches) {
    const changes = [];
    for (const patch of patches) {
        if (!isSafeAgentField(patch.name) || typeof patch.value !== 'string') continue;
        const group = Array.from(form.elements).filter(element => element.name === patch.name && isAvailableField(element));
        const element = group[0];
        if (!element) continue;
        const type = fieldType(element);
        const before = type === 'radio' ? (group.find(item => item.checked)?.value ?? '') : element.value;
        if (type === 'radio' && patch.value === '') {
            element._x_model?.set('');
            for (const radio of group) {
                radio.checked = false;
                radio.dispatchEvent(new Event('change', { bubbles: true }));
            }
            changes.push({ name: patch.name, value: before });
            continue;
        }
        const target = type === 'radio' ? group.find(item => item.value === patch.value) : element;
        if (!target || (type === 'select' && !Array.from(target.options).some(option => option.value === patch.value && !option.disabled))) continue;
        if (type === 'radio') {
            for (const radio of group) radio.checked = false;
            target.checked = true;
        } else {
            target.value = patch.value;
            if (patch.value && !target.value) { target.value = before; continue; }
        }
        target.dispatchEvent(new Event('input', { bubbles: true }));
        target.dispatchEvent(new Event('change', { bubbles: true }));
        changes.push({ name: patch.name, value: before });
    }
    return changes;
}

async function jsonRequest(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, ...options.headers },
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
        const firstError = Object.values(data.errors || {}).flat()[0];
        throw new Error(response.status === 401 ? 'Sesi Anda berakhir. Muat ulang halaman untuk masuk kembali.' : firstError || data.message || (response.status === 429 ? 'Batas permintaan tercapai. Coba lagi nanti.' : 'Permintaan belum berhasil. Coba lagi.'));
    }
    return data;
}

export function aiAgent(generateUrl, statusUrl) {
    return {
        prompt: '', result: null, error: '', notice: '', busy: false, checking: false,
        status: null, forms: [], selectedForm: 0, resultForm: null, undoFields: [], returnFocus: null,
        async openPanel(detail = {}) {
            this.returnFocus = document.activeElement;
            this.forms = Array.from(document.querySelectorAll('main form')).filter(form => form.method.toLowerCase() === 'post' && !form.closest('[data-ai-ignore], [data-ai-agent]') && describeAgentForm(form).length > 0).map((form, index) => ({ form, label: form.getAttribute('aria-label') || `Formulir ${index + 1}` }));
            this.selectedForm = 0;
            if (detail.prompt) { this.prompt = detail.prompt; this.result = null; this.undoFields = []; }
            this.error = '';
            if (!this.$refs.dialog.open) this.$refs.dialog.showModal();
            this.$nextTick(() => this.$refs.prompt.focus({ preventScroll: true }));
            this.checking = true;
            try { this.status = await jsonRequest(statusUrl); } catch (error) { this.error = error.message; this.status = null; }
            finally { this.checking = false; }
        },
        closePanel() {
            this.$refs.dialog.close();
            this.returnFocus?.focus({ preventScroll: true });
        },
        formChanged() { this.result = null; this.undoFields = []; this.notice = ''; },
        async generate() {
            if (this.busy || this.checking || !this.prompt.trim()) return;
            this.busy = true; this.error = ''; this.notice = ''; this.result = null; this.undoFields = [];
            this.resultForm = this.forms[this.selectedForm]?.form ?? null;
            try {
                const data = await jsonRequest(generateUrl, { method: 'POST', body: JSON.stringify({ prompt: this.prompt.trim(), form_fields: this.resultForm ? describeAgentForm(this.resultForm) : [] }) });
                if (typeof data.reply !== 'string' || !Array.isArray(data.fields)) throw new Error('Respons AI belum sesuai format. Coba lagi.');
                this.result = data;
            } catch (error) { this.error = error.message; }
            finally { this.busy = false; }
        },
        applyDraft() {
            if (this.undoFields.length) return;
            if (!this.resultForm?.isConnected || !this.result) { this.error = 'Formulir sudah berubah. Buat ulang draf pada formulir yang aktif.'; return; }
            this.undoFields = applyAgentFields(this.resultForm, this.result.fields);
            this.notice = `${this.undoFields.length} kolom terisi. Belum disimpan. Tinjau formulir sebelum menekan Simpan.`;
        },
        undoDraft() {
            if (this.resultForm?.isConnected) applyAgentFields(this.resultForm, this.undoFields);
            this.undoFields = []; this.notice = 'Pengisian dibatalkan. Nilai sebelumnya telah dipulihkan.';
        },
        async copyReply() {
            try { await navigator.clipboard.writeText(this.result.reply); this.notice = 'Draf disalin.'; }
            catch { this.error = 'Belum dapat menyalin otomatis. Pilih teks draf untuk menyalinnya.'; }
        },
    };
}

export function aiAgentSettingsForm(url, enabled, configured = false) {
    return {
        apiKey: '', enabled, configured, busy: false, error: '', notice: '',
        async save() {
            if (this.busy) return;
            this.busy = true; this.error = ''; this.notice = '';
            try {
                const data = await jsonRequest(url, { method: 'PUT', body: JSON.stringify({ api_key: this.apiKey || null, enabled: this.enabled }) });
                this.apiKey = ''; this.configured = data.configured; this.notice = data.message;
            } catch (error) { this.error = error.message; }
            finally { this.busy = false; }
        },
    };
}
