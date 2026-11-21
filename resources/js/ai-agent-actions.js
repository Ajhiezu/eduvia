import { isSafeAgentField } from './ai-agent.js';

const allowedTypes = new Set(['text', 'textarea', 'number', 'date', 'datetime-local', 'time', 'email', 'tel', 'url', 'select', 'radio', 'checkbox']);
const relationNames = new Set(['teacher_id', 'student_id', 'parent_id']);

export function hasExplicitDeleteInstruction(text) {
    return /\b(hapus|hapuskan|delete|remove|buang)\b/i.test(text)
        && !/\b(jangan|batal|batalkan|tidak|tanpa|never|do not|don't)\b[\s\S]{0,100}\b(hapus|hapuskan|delete|remove|buang)\b/i.test(text);
}

function typeOf(element) {
    return element.tagName === 'TEXTAREA' ? 'textarea' : element.tagName === 'SELECT' ? 'select' : element.type;
}

export function canExecuteField(element) {
    const type = typeOf(element);
    const allowedName = isSafeAgentField(element.name || '') || (relationNames.has(element.name) && ['select', 'radio'].includes(type));
    return allowedName && allowedTypes.has(type) && !element.disabled && !element.readOnly && element.getClientRects().length > 0 && !element.closest('[data-ai-ignore], [data-ai-agent]');
}

function labelOf(element) {
    return (element.getAttribute('aria-label') || element.labels?.[0]?.textContent || element.closest('div')?.querySelector('label')?.textContent || element.placeholder || element.name).replace(/\s+/g, ' ').trim().slice(0, 140);
}

function choicesFor(group, type) {
    const source = type === 'select' ? Array.from(group[0].options).filter(option => !option.disabled) : group;
    return source.map(option => ({ value: option.value, label: (type === 'select' ? option.textContent : option.labels?.[0]?.textContent || option.value).replace(/\s+/g, ' ').trim().slice(0, 140) }));
}

export function executableFormSchema(form) {
    const controls = Array.from(form.elements).filter(canExecuteField);
    const names = [...new Set(controls.map(element => element.name))].slice(0, 80);
    return names.map(name => {
        const group = controls.filter(element => element.name === name);
        const element = group[0];
        const type = typeOf(element);
        let value = element.value;
        if (type === 'radio') value = group.find(item => item.checked)?.value || '';
        if (type === 'checkbox') value = group.length > 1 ? group.filter(item => item.checked).map(item => item.value) : element.checked;
        if (type === 'select' && element.multiple) value = Array.from(element.selectedOptions).map(item => item.value);
        const field = { name, label: labelOf(element), type, required: group.some(item => item.required), value, multiple: (type === 'select' && element.multiple) || (type === 'checkbox' && group.length > 1) };
        if (['select', 'radio', 'checkbox'].includes(type)) {
            const choices = choicesFor(group, type);
            field.options = choices.slice(0, 80);
            field.option_count = choices.length;
        }
        if (element.min) field.min = element.min;
        if (element.max) field.max = element.max;
        if (element.maxLength > 0) field.max_length = element.maxLength;
        return field;
    });
}

export function fillExecutableForm(form, patches) {
    const controls = Array.from(form.elements).filter(canExecuteField);
    const applied = [];
    for (const patch of patches || []) {
        const group = controls.filter(element => element.name === patch.name);
        if (!group.length) throw new Error(`Kolom ${patch.name} tidak tersedia pada formulir ini.`);
        const element = group[0];
        const type = typeOf(element);
        const value = patch.value;
        if (['select', 'radio', 'checkbox'].includes(type) && !(type === 'checkbox' && group.length === 1 && typeof value === 'boolean')) {
            const choices = choicesFor(group, type).map(option => option.value);
            const requested = Array.isArray(value) ? value : [value];
            if (requested.some(item => typeof item !== 'string' || !choices.includes(item))) throw new Error(`Pilihan untuk ${labelOf(element)} tidak tersedia.`);
        } else if (typeof value !== 'string' && !(type === 'checkbox' && typeof value === 'boolean')) {
            throw new Error(`Nilai ${labelOf(element)} tidak sesuai jenis kolom.`);
        }
        if (type === 'checkbox') {
            for (const control of group) {
                control.checked = typeof value === 'boolean' ? value : (Array.isArray(value) ? value.includes(control.value) : control.value === value);
                control.dispatchEvent(new Event('change', { bubbles: true }));
            }
        } else if (type === 'radio') {
            const target = group.find(item => item.value === value);
            for (const control of group) control.checked = control === target;
            target.dispatchEvent(new Event('change', { bubbles: true }));
        } else if (type === 'select' && element.multiple) {
            if (!Array.isArray(value)) throw new Error(`Gunakan daftar pilihan untuk ${labelOf(element)}.`);
            for (const option of element.options) option.selected = value.includes(option.value);
            element.dispatchEvent(new Event('change', { bubbles: true }));
        } else {
            element.value = value;
            element.dispatchEvent(new Event('input', { bubbles: true }));
            element.dispatchEvent(new Event('change', { bubbles: true }));
        }
        applied.push(patch.name);
    }
    return applied;
}

export function modulePath(module, operation, recordId = null) {
    const template = module?.routes?.[operation];
    if (!template || !template.startsWith('/') || template.startsWith('//')) throw new Error('Menu atau operasi ini tidak tersedia.');
    if (template.includes('__ID__') && !/^[1-9][0-9]*$/.test(String(recordId))) throw new Error('Target data belum jelas.');
    const path = template.replace('__ID__', String(recordId));
    const url = new URL(path, window.location.origin);
    if (url.origin !== window.location.origin) throw new Error('Alamat tindakan tidak valid.');
    return url;
}

function recordIdFromPath(template, href) {
    if (!template) return null;
    const parts = template.split('__ID__');
    const path = new URL(href, window.location.origin).pathname;
    if (!path.startsWith(parts[0]) || !path.endsWith(parts[1] || '')) return null;
    const id = path.slice(parts[0].length, parts[1] ? -parts[1].length : undefined);
    return /^[1-9][0-9]*$/.test(id) ? Number(id) : null;
}

function csrf() {
    return document.querySelector('meta[name="csrf-token"]').content;
}

export function executableFormPayload(form) {
    const disabled = [];
    for (const control of form.elements) {
        if (control.disabled) continue;
        let ancestor = control.parentElement;
        while (ancestor && ancestor !== form) {
            if (window.getComputedStyle(ancestor).display === 'none') {
                control.disabled = true;
                disabled.push(control);
                break;
            }
            ancestor = ancestor.parentElement;
        }
    }
    try {
        const invalid = Array.from(form.elements).filter(element => !element.disabled && typeof element.checkValidity === 'function' && !element.checkValidity()).map(element => ({ name: element.name, label: labelOf(element), message: element.validationMessage }));
        return { invalid, body: new FormData(form) };
    } finally {
        disabled.forEach(element => { element.disabled = false; });
    }
}

function toolResultText(tool, result) {
    const output = { tool: tool.name, resource: tool.resource, result };
    let text = JSON.stringify(output);
    if (text.length > 23000 && result.fields) {
        output.result = { ...result, fields: result.fields.map(field => ({ ...field, value: typeof field.value === 'string' ? field.value.slice(0, 1000) : field.value, options: field.options?.slice(0, 8) })), note: 'Pilihan dipersingkat. Gunakan find_options untuk mencari opsi lainnya.' };
        text = JSON.stringify(output);
    }
    if (text.length > 23000) {
        output.result = { ok: false, message: 'Hasil terlalu panjang. Persempit pencarian atau gunakan formulir dengan lebih sedikit kolom.' };
        text = JSON.stringify(output);
    }
    return text;
}

function contextHeaders(schoolId) {
    return schoolId ? { 'X-School-ID': String(schoolId) } : {};
}

async function getPage(url, schoolId) {
    const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'text/html', ...contextHeaders(schoolId) } });
    if (!response.ok || new URL(response.url).pathname === '/login') throw new Error(response.status === 403 ? 'Akun Anda tidak memiliki izin untuk menu ini.' : 'Halaman belum dapat dibuka. Periksa sesi dan hak akses.');
    return new DOMParser().parseFromString(await response.text(), 'text/html');
}

async function writeAction(url, method, body, actionId, schoolId) {
    let response;
    try {
        response = await fetch(url, { method, body, credentials: 'same-origin', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Eduvia-Agent-Action': actionId, ...contextHeaders(schoolId) } });
    } catch {
        return { ok: false, uncertain: true, message: 'Koneksi terputus saat menjalankan tindakan. Periksa daftar data sebelum mengulangi perintah.' };
    }
    const data = await response.json().catch(() => null);
    if (!data || !response.headers.get('content-type')?.includes('application/json')) return { ok: false, uncertain: true, message: 'Hasil tindakan belum terkonfirmasi. Periksa daftar data sebelum mengulangi.' };
    if (!response.ok) return { ok: false, status: response.status, message: data.message || 'Tindakan ditolak aplikasi.', errors: data.errors, uncertain: !!data.uncertain || response.status >= 500 || response.status === 409 };
    return { ...data, ok: data.ok === true, uncertain: !!data.uncertain || data.ok !== true, message: data.message || 'Hasil tindakan belum terkonfirmasi.' };
}

export class AgentWorkspace {
    constructor(schoolId = null) { this.schoolId = schoolId; this.container = null; this.form = null; this.resource = null; this.recordId = null; this.knownRecords = new Map(); this.executed = new Set(); this.uncertain = false; }
    destroy() { this.container?.remove(); this.container = null; this.form = null; this.resource = null; this.recordId = null; }

    async browse(module, tool) {
        const url = modulePath(module, 'index');
        if (tool.query) url.searchParams.set('search', tool.query);
        if (tool.page) url.searchParams.set('page', tool.page);
        const doc = await getPage(url, this.schoolId);
        const records = new Map();
        for (const link of doc.querySelectorAll('main a[href]')) {
            const id = recordIdFromPath(module.routes.edit, link.getAttribute('href'));
            if (!id || records.has(id)) continue;
            const row = link.closest('tr') || link.closest('article') || link.parentElement.parentElement;
            const cells = Array.from(row.querySelectorAll('td')).slice(0, 4);
            const summary = (cells.length ? cells.map(cell => cell.textContent).join(' | ') : row.textContent).replace(/\s+/g, ' ').trim().slice(0, 320);
            records.set(id, { id, summary });
        }
        for (const form of doc.querySelectorAll('main form[action]')) {
            if (form.querySelector('[name="_method"]')?.value?.toUpperCase() !== 'DELETE') continue;
            const id = recordIdFromPath(module.routes.destroy, form.getAttribute('action'));
            if (id && !records.has(id)) records.set(id, { id, summary: (form.closest('tr')?.textContent || form.parentElement.textContent).replace(/\s+/g, ' ').trim().slice(0, 320) });
        }
        const known = this.knownRecords.get(tool.resource) || new Set();
        for (const id of records.keys()) known.add(id);
        this.knownRecords.set(tool.resource, known);
        const pages = [...new Set(Array.from(doc.querySelectorAll('main a[href]')).map(link => { try { const target = new URL(link.getAttribute('href'), window.location.origin); return target.pathname === url.pathname ? Number(target.searchParams.get('page')) : 0; } catch { return 0; } }).filter(page => page > 0))];
        return { ok: true, resource: tool.resource, records: Array.from(records.values()).slice(0, 30), page: tool.page || 1, available_pages: pages, query: tool.query || '', url: url.pathname + url.search };
    }

    async openForm(module, tool) {
        const preferredOperation = tool.record_id ? 'edit' : 'create';
        const operation = module.routes[preferredOperation] ? preferredOperation : 'index';
        const targetPath = modulePath(module, tool.record_id ? 'update' : 'store', tool.record_id).pathname;
        if (tool.record_id && !this.knownRecords.get(tool.resource)?.has(Number(tool.record_id))) throw new Error('Cari target melalui daftar data terlebih dahulu agar ID terverifikasi.');
        const doc = await getPage(modulePath(module, operation, tool.record_id), this.schoolId);
        const forms = Array.from(doc.querySelectorAll('main form')).filter(form => form.method.toLowerCase() === 'post' && new URL(form.getAttribute('action') || '', window.location.origin).pathname === targetPath);
        if (forms.length !== 1) throw new Error('Formulir menu ini belum dapat dioperasikan agen. Tidak ada data yang diubah.');
        const source = forms[0];
        let scope = source.closest('[x-data]');
        if (!scope || scope.tagName === 'BODY' || scope.contains(source.closest('main'))) scope = source;
        this.destroy();
        const container = document.createElement('div');
        container.dataset.aiWorkspace = 'true';
        container.setAttribute('aria-hidden', 'true');
        container.className = 'ai-execution-workspace';
        container.append(scope.cloneNode(true));
        container.querySelectorAll('script, style, link, iframe').forEach(element => element.remove());
        const mountedForm = Array.from(container.querySelectorAll('form')).find(form => new URL(form.getAttribute('action') || '', window.location.origin).pathname === targetPath);
        let ancestor = mountedForm;
        while (ancestor && ancestor !== container) {
            ancestor.removeAttribute('x-show');
            ancestor.removeAttribute('x-cloak');
            ancestor.style.removeProperty('display');
            if (ancestor.tagName === 'DIALOG') ancestor.open = true;
            ancestor = ancestor.parentElement;
        }
        container.querySelectorAll('[autofocus]').forEach(element => element.removeAttribute('autofocus'));
        container.querySelectorAll('input, select, textarea, button, a').forEach(element => element.tabIndex = -1);
        document.body.append(container);
        window.Alpine.initTree(container);
        await window.Alpine.nextTick();
        this.container = container;
        this.form = mountedForm;
        this.resource = tool.resource;
        this.recordId = tool.record_id ? Number(tool.record_id) : null;
        if (!this.form) { this.destroy(); throw new Error('Formulir tidak berhasil dimuat.'); }
        if (tool.fields?.length) {
            fillExecutableForm(this.form, tool.fields);
            await window.Alpine.nextTick();
        }
        return { ok: true, resource: this.resource, record_id: this.recordId, fields: executableFormSchema(this.form), required_files: Array.from(this.form.querySelectorAll('input[type=file][required]')).map(element => ({ name: element.name, label: labelOf(element) })) };
    }

    requireForm(tool) {
        if (!this.form?.isConnected || this.resource !== tool.resource || this.recordId !== (tool.record_id ? Number(tool.record_id) : null)) throw new Error('Buka formulir tujuan terlebih dahulu.');
    }

    async findOptions(tool) {
        this.requireForm(tool);
        const group = Array.from(this.form.elements).filter(element => element.name === tool.field && canExecuteField(element));
        if (!group.length || !['select', 'radio', 'checkbox'].includes(typeOf(group[0]))) throw new Error('Kolom pilihan tidak ditemukan.');
        const query = (tool.query || '').toLocaleLowerCase('id');
        return { ok: true, field: tool.field, options: choicesFor(group, typeOf(group[0])).filter(option => option.label.toLocaleLowerCase('id').includes(query)).slice(0, 80) };
    }

    async saveForm(module, tool) {
        this.requireForm(tool);
        if (this.uncertain) throw new Error('Ada tindakan dengan status belum pasti. Periksa daftar data sebelum melanjutkan penulisan.');
        if (!tool.fields?.length) throw new Error('Belum ada kolom yang diminta untuk disimpan.');
        fillExecutableForm(this.form, tool.fields);
        await window.Alpine.nextTick();
        const { invalid, body } = executableFormPayload(this.form);
        if (invalid.length) return { ok: false, message: 'Lengkapi atau perbaiki isian wajib terlebih dahulu.', invalid_fields: invalid, fields: executableFormSchema(this.form) };
        const url = modulePath(module, tool.record_id ? 'update' : 'store', tool.record_id);
        if (new URL(this.form.action).pathname !== url.pathname) throw new Error('Alamat formulir tidak cocok dengan menu tujuan.');
        const fingerprint = JSON.stringify([url.pathname, tool.record_id || 'new', [...tool.fields].sort((first, second) => first.name.localeCompare(second.name))]);
        if (this.executed.has(fingerprint)) throw new Error('Tindakan yang sama sudah dijalankan pada perintah ini. Tidak disimpan ulang.');
        this.executed.add(fingerprint);
        const expectedMethod = tool.record_id ? 'PUT' : 'POST';
        const actualMethod = String(body.get('_method') || 'POST').toUpperCase();
        if (!['PUT', 'PATCH'].includes(actualMethod) && expectedMethod === 'PUT') throw new Error('Metode edit formulir tidak sesuai.');
        if (expectedMethod === 'POST' && actualMethod !== 'POST') throw new Error('Metode tambah formulir tidak sesuai.');
        const result = await writeAction(url, 'POST', body, crypto.randomUUID(), this.schoolId);
        this.uncertain = !!result.uncertain;
        if (result.ok) this.destroy();
        return result;
    }

    async deleteRecord(module, tool, userInstruction) {
        if (this.uncertain) throw new Error('Periksa tindakan sebelumnya yang belum terkonfirmasi terlebih dahulu.');
        if (!hasExplicitDeleteInstruction(userInstruction)) throw new Error('Penghapusan memerlukan perintah hapus yang eksplisit dari pengguna.');
        if (!this.knownRecords.get(tool.resource)?.has(Number(tool.record_id))) throw new Error('Cari dan pastikan target penghapusan terlebih dahulu.');
        const url = modulePath(module, 'destroy', tool.record_id);
        const fingerprint = `delete:${url.pathname}`;
        if (this.executed.has(fingerprint)) throw new Error('Target ini sudah diproses. Tidak dihapus ulang.');
        this.executed.add(fingerprint);
        const result = await writeAction(url, 'DELETE', undefined, crypto.randomUUID(), this.schoolId);
        this.uncertain = !!result.uncertain;
        if (result.ok) this.knownRecords.get(tool.resource).delete(Number(tool.record_id));
        return result;
    }

    async execute(catalog, tool, userInstruction) {
        const module = catalog[tool.resource];
        if (!module) throw new Error('Menu ini tidak tersedia untuk akun Anda.');
        switch (tool.name) {
            case 'browse': return this.browse(module, tool);
            case 'open_form': return this.openForm(module, tool);
            case 'find_options': return this.findOptions(tool);
            case 'save_form': return this.saveForm(module, tool);
            case 'delete_record': return this.deleteRecord(module, tool, userInstruction);
            default: throw new Error('Alat agen tidak dikenali.');
        }
    }
}

export function aiAgentChat(runUrl, statusUrl) {
    let workspace = new AgentWorkspace();
    return {
        prompt: '', messages: [], history: [], status: null, checking: false, busy: false, error: '', progress: '', stopRequested: false, changed: false, lastUrl: '', returnFocus: null,
        async openPanel(detail = {}) {
            this.returnFocus = document.activeElement;
            if (detail.prompt) this.prompt = detail.prompt;
            if (!this.$refs.dialog.open) this.$refs.dialog.showModal();
            this.$nextTick(() => this.$refs.prompt.focus({ preventScroll: true }));
            await this.checkStatus();
        },
        async checkStatus() {
            if (this.checking) return false;
            this.checking = true; this.error = '';
            try {
                const response = await fetch(statusUrl, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
                const data = await response.json().catch(() => null);
                if ([401, 419].includes(response.status) || response.redirected || !data) throw new Error('Sesi Anda berakhir. Masuk kembali untuk menggunakan AI Agent.');
                if (!response.ok) throw new Error(response.status === 403 ? data.message || 'Akun ini belum memiliki akses ke AI Agent.' : 'Server belum dapat membaca konfigurasi AI. Tekan Coba lagi.');
                if (typeof data.enabled !== 'boolean' || typeof data.configured !== 'boolean') throw new Error('Respons konfigurasi belum valid. Tekan Coba lagi.');
                this.status = data;
                workspace.schoolId = data.school_id ?? null;
                return true;
            } catch (error) { this.status = null; this.error = error.message === 'Failed to fetch' ? 'Koneksi terputus. Tekan Coba lagi untuk memeriksa konfigurasi.' : error.message; return false; }
            finally { this.checking = false; }
        },
        handleComposerKeydown(event) {
            if (event.key !== 'Enter' || event.shiftKey || event.isComposing || event.keyCode === 229) return;
            event.preventDefault();
            return this.send();
        },
        closePanel() { this.stopRequested = true; this.$refs.dialog.close(); this.returnFocus?.focus({ preventScroll: true }); },
        newConversation() {
            if (this.busy) return;
            workspace.destroy(); workspace = new AgentWorkspace(this.status?.school_id); this.messages = []; this.history = []; this.error = ''; this.prompt = ''; this.progress = ''; this.lastUrl = ''; this.changed = false;
        },
        scrollConversation() { this.$nextTick(() => { const body = this.$refs.conversation; body.scrollTop = body.scrollHeight; }); },
        async send() {
            if (this.busy || this.checking || !this.prompt.trim()) return;
            if (!this.status && !await this.checkStatus()) return;
            if (!this.status.enabled || !this.status.configured) { this.error = 'Gemini belum aktif. Periksa pengaturan AI Agent sekolah.'; return; }
            const text = this.prompt.trim();
            this.prompt = ''; this.busy = true; this.error = ''; this.stopRequested = false; workspace.executed.clear();
            this.messages.push({ role: 'user', text }); this.history.push({ role: 'user', text }); this.scrollConversation();
            try {
                for (let step = 0; step < 10; step++) {
                    if (this.stopRequested) { this.progress = 'Dihentikan setelah langkah terakhir selesai.'; break; }
                    this.progress = 'Memahami perintah dan menentukan langkah…';
                    const response = await fetch(runUrl, { method: 'POST', credentials: 'same-origin', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), ...contextHeaders(this.status.school_id) }, body: JSON.stringify({ messages: this.history, page: window.location.pathname }) });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) throw new Error(response.status === 429 ? 'Batas permintaan tercapai. Tunggu sebentar, lalu lanjutkan percakapan.' : Object.values(data.errors || {}).flat()[0] || data.message || 'Permintaan belum berhasil.');
                    if (typeof data.reply !== 'string' || !Object.hasOwn(data, 'tool')) throw new Error('Respons agen belum sesuai format.');
                    if (!data.tool) {
                        this.messages.push({ role: 'assistant', text: data.reply }); this.history.push({ role: 'assistant', text: data.reply }); this.progress = ''; this.scrollConversation(); break;
                    }
                    if (this.stopRequested) { this.progress = 'Dihentikan sebelum tindakan berikutnya.'; break; }
                    this.progress = data.reply;
                    this.history.push({ role: 'assistant', text: JSON.stringify({ reply: data.reply, tool: data.tool }) });
                    let result;
                    try { result = await workspace.execute(data.catalog, data.tool, text); }
                    catch (error) { result = { ok: false, message: error.message }; }
                    this.history.push({ role: 'tool', text: toolResultText(data.tool, result) });
                    if (['save_form', 'delete_record'].includes(data.tool.name)) {
                        this.messages.push({ role: 'action', ok: result.ok, text: result.message || 'Tindakan belum selesai.' });
                        if (result.ok) this.changed = true;
                        if (result.redirect) { const target = new URL(result.redirect, window.location.origin); if (target.origin === window.location.origin) this.lastUrl = target.pathname + target.search; }
                    }
                    this.scrollConversation();
                    if (result.uncertain) { this.error = result.message; this.progress = ''; break; }
                    if (step === 9) this.progress = 'Batas 10 langkah per pesan tercapai. Ketik “lanjutkan” untuk meneruskan dari hasil terakhir.';
                }
            } catch (error) { this.error = error.message; this.progress = ''; }
            finally { this.busy = false; this.scrollConversation(); }
        },
        stop() { this.stopRequested = true; this.progress = 'Menghentikan setelah langkah yang sedang berjalan selesai…'; },
    };
}
