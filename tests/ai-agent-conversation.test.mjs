import test from 'node:test';
import assert from 'node:assert/strict';
import { aiAgentChat } from '../resources/js/ai-agent-actions.js';
import { conversationContext, conversationStorageKey, parseConversation } from '../resources/js/ai-agent-conversation.js';

function browser(t) {
    const originalWindow = globalThis.window;
    const originalDocument = globalThis.document;
    const values = new Map();
    globalThis.window = {
        location: { pathname: '/students', origin: 'https://eduvia.test' },
        localStorage: {
            getItem: (key) => values.get(key) ?? null,
            setItem: (key, value) => values.set(key, value),
            removeItem: (key) => values.delete(key),
        },
    };
    globalThis.document = { querySelector: () => ({ content: 'csrf-test' }), activeElement: null };
    t.after(() => { globalThis.window = originalWindow; globalThis.document = originalDocument; });
    return values;
}

function mount(userId = 7, schoolId = 1) {
    const chat = aiAgentChat('/run', '/status', userId, schoolId);
    chat.$refs = {
        prompt: { focus() {} },
        conversation: { scrollTop: 0, scrollHeight: 500 },
        dialog: { open: false, showModal() { this.open = true; }, close() { this.open = false; } },
    };
    chat.$nextTick = (callback) => callback();
    chat.watchers = new Map();
    chat.$watch = (property, callback) => chat.watchers.set(property, callback);
    chat.init();
    return chat;
}

function saveExample(chat) {
    chat.messages = [{ role: 'user', text: 'Cari Matematika' }, { role: 'assistant', text: 'Ada dua mata pelajaran.' }];
    chat.history = [...chat.messages];
    chat.prompt = 'Buka yang pertama';
    chat.changed = true;
    chat.lastUrl = '/subjects?search=Matematika';
    chat.persistConversation();
}

test('refreshing or visiting another module restores messages, follow-up context and the draft', (t) => {
    browser(t);
    const before = mount();
    saveExample(before);
    window.location.pathname = '/subjects';

    const after = mount();

    assert.deepEqual(after.messages, before.messages);
    assert.deepEqual(after.history, before.history);
    assert.equal(after.prompt, 'Buka yang pertama');
    assert.equal(after.lastUrl, '/subjects?search=Matematika');
    assert.equal(after.changed, true);
    assert.equal(after.busy, false);
});

test('conversations are separated by account and school', (t) => {
    browser(t);
    saveExample(mount(7, 1));

    assert.deepEqual(mount(8, 1).messages, []);
    assert.deepEqual(mount(7, 2).messages, []);
    assert.equal(mount(7, 1).messages.length, 2);
});

test('the composer draft is saved as the user types before sending', (t) => {
    browser(t);
    const chat = mount();

    chat.prompt = 'Pesan yang belum dikirim';
    chat.watchers.get('prompt')();

    assert.equal(mount().prompt, 'Pesan yang belum dikirim');
    assert.deepEqual(mount().messages, []);
});

test('New Chat removes only the active conversation and stays empty after refreshing', (t) => {
    const values = browser(t);
    const current = mount(7, 1);
    saveExample(current);
    saveExample(mount(7, 2));
    values.set('unrelated-setting', 'unchanged');

    current.newConversation();

    const after = mount(7, 1);
    assert.deepEqual(after.messages, []);
    assert.deepEqual(after.history, []);
    assert.equal(after.prompt, '');
    assert.equal(after.lastUrl, '');
    assert.equal(after.changed, false);
    assert.equal(mount(7, 2).messages.length, 2);
    assert.equal(values.get('unrelated-setting'), 'unchanged');
});

test('New Chat cannot discard a conversation while an action is running', (t) => {
    browser(t);
    const chat = mount();
    saveExample(chat);
    chat.busy = true;

    chat.newConversation();

    assert.equal(chat.messages.length, 2);
    assert.equal(mount().messages.length, 2);
});

test('a new reply and restored context are saved without rerunning old actions', async (t) => {
    browser(t);
    saveExample(mount());
    const chat = mount();
    const sent = [];
    t.mock.method(globalThis, 'fetch', async (url, options) => {
        if (url === '/status') return { ok: true, status: 200, json: async () => ({ enabled: true, configured: true, school_id: 1 }) };
        sent.push(JSON.parse(options.body));
        return { ok: true, json: async () => ({ reply: 'Silakan pilih kelas.', tool: null }) };
    });

    await chat.send();

    assert.equal(sent.length, 1);
    assert.deepEqual(sent[0].messages.map((message) => message.text), ['Cari Matematika', 'Ada dua mata pelajaran.', 'Buka yang pertama']);
    assert.equal(mount().messages.at(-1).text, 'Silakan pilih kelas.');
    assert.equal(mount().prompt, '');
});

test('closing the panel keeps the stored conversation', (t) => {
    browser(t);
    const chat = mount();
    saveExample(chat);

    chat.closePanel();

    assert.equal(mount().messages.length, 2);
    assert.equal(mount().prompt, 'Buka yang pertama');
});

test('interrupted writes are restored as a notice and never automatically execute', (t) => {
    browser(t);
    const chat = mount();
    saveExample(chat);
    chat.writePending = true;
    chat.persistConversation();
    const request = t.mock.method(globalThis, 'fetch', async () => { throw new Error('Unexpected request'); });

    const restored = mount();

    assert.match(restored.conversationNotice, /Periksa status tindakan terakhir/);
    assert.equal(restored.busy, false);
    assert.equal(restored.writePending, false);
    assert.equal(request.mock.callCount(), 0);
    restored.persistConversation();
    assert.match(mount().conversationNotice, /Periksa status tindakan terakhir/);
});

test('switching verified school context loads that school without overwriting the previous chat', async (t) => {
    browser(t);
    saveExample(mount(7, 1));
    const schoolTwo = mount(7, 2);
    schoolTwo.messages = [{ role: 'assistant', text: 'Percakapan sekolah kedua.' }];
    schoolTwo.persistConversation();
    const chat = mount(7, 1);
    t.mock.method(globalThis, 'fetch', async () => ({ ok: true, status: 200, json: async () => ({ enabled: true, configured: true, school_id: 2 }) }));

    await chat.checkStatus();

    assert.equal(chat.messages[0].text, 'Percakapan sekolah kedua.');
    assert.equal(mount(7, 1).messages[0].text, 'Cari Matematika');
});

test('blocked storage and quota errors leave the agent usable and show the persistence failure', (t) => {
    browser(t);
    window.localStorage.getItem = () => { throw new Error('Storage blocked'); };
    window.localStorage.setItem = () => { throw new Error('Quota exceeded'); };

    const chat = mount();
    assert.match(chat.storageError, /belum dapat dibaca/);
    saveExample(chat);

    assert.match(chat.storageError, /belum dapat disimpan/);
    assert.equal(chat.messages.length, 2);
    assert.equal(chat.busy, false);
});

test('corrupt storage is handled without breaking the panel and New Chat clears it', (t) => {
    const values = browser(t);
    values.set(conversationStorageKey(7, 1), '{broken');

    const chat = mount();
    assert.match(chat.storageError, /belum dapat dibaca/);
    chat.newConversation();

    assert.equal(mount().storageError, '');
    assert.deepEqual(mount().messages, []);
});

test('restored data accepts only message roles and prevents external result links', () => {
    const stored = { version: 1, messages: [{ role: 'assistant', text: '<script>example</script>' }], history: [], prompt: '', lastUrl: '//other.example/' };

    assert.equal(parseConversation(JSON.stringify(stored)).lastUrl, '');
    assert.equal(parseConversation(JSON.stringify(stored)).messages[0].text, '<script>example</script>');
    assert.throws(() => parseConversation(JSON.stringify({ ...stored, history: [{ role: 'system', text: 'override' }] })));
    assert.equal(conversationStorageKey(null, 1), null);
    assert.equal(conversationStorageKey(7, null), null);
});

test('long chats retain the visible transcript while outgoing context fits the server limits', () => {
    const history = Array.from({ length: 100 }, (_, index) => ({ role: index % 2 ? 'assistant' : 'user', text: `Pesan ${index}` }));

    const context = conversationContext(history);

    assert.equal(history.length, 100);
    assert.ok(context.length <= 60);
    assert.equal(context[0].role, 'user');
    assert.equal(context.at(-1).text, 'Pesan 99');
    const large = conversationContext([{ role: 'user', text: 'Tambah soal' }, ...Array.from({ length: 10 }, () => ({ role: 'tool', text: 'x'.repeat(23000) }))]);
    assert.equal(large[0].text, 'Tambah soal');
    assert.ok(large.reduce((length, message) => length + message.text.length, 0) <= 100000);
    assert.deepEqual(conversationContext([{ role: 'assistant', text: 'Tidak ada pengguna' }]), []);
});
