import test from 'node:test';
import assert from 'node:assert/strict';
import { studentSearchRequest } from '../resources/js/ai-agent-search.js';
import { AgentWorkspace, aiAgentChat, recordIdFromPath } from '../resources/js/ai-agent-actions.js';
import { conversationStorageKey, parseConversation } from '../resources/js/ai-agent-conversation.js';

test('student-name requests extract the requested name without changing its spelling', () => {
    for (const text of [
        'cari siswa yang namanya naruto uzumaki', 'carikan siswa bernama naruto uzumaki',
        'tolong cari peserta didik dengan nama naruto uzumaki', 'cari siswa naruto uzumaki',
        'cari siswa nama: naruto uzumaki', 'cari murid "naruto uzumaki"',
    ]) {
        assert.deepEqual(studentSearchRequest(text), { resource: 'students', query: 'naruto uzumaki' }, text);
    }
});

test('complex filters and writing commands remain with the operational agent', () => {
    for (const text of [
        'Cari siswa aktif', 'Cari siswa di kelas VII-A', 'Cari siswa Naruto lalu hapus',
        'Cari siswa Naruto dan ubah namanya', 'Jangan cari siswa Naruto', 'Buat ringkasan materi',
    ]) {
        assert.equal(studentSearchRequest(text), null, text);
    }
    assert.equal(studentSearchRequest(`cari siswa ${'a'.repeat(201)}`), null);
});

function browser(t, allowed = true) {
    const previousWindow = globalThis.window;
    const previousDocument = globalThis.document;
    const values = new Map();
    globalThis.window = {
        location: { href: 'https://eduvia.test/dashboard', origin: 'https://eduvia.test', pathname: '/dashboard' },
        localStorage: { getItem: (key) => values.get(key) ?? null, setItem: (key, value) => values.set(key, value), removeItem: (key) => values.delete(key) },
    };
    globalThis.document = { querySelector: () => ({ content: 'csrf-test' }), querySelectorAll: () => [] };
    t.after(() => { globalThis.window = previousWindow; globalThis.document = previousDocument; });
    const modules = allowed ? { students: { label: 'Data siswa', routes: { index: '/students', edit: '/students/__ID__/edit', show: '/students/__ID__' } } } : {};
    const chat = aiAgentChat('/run', '/status', 7, 1, [], modules);
    chat.$nextTick = () => {};
    chat.restoreConversation(1);
    chat.status = { enabled: true, configured: true, school_id: 1 };
    return { chat, values, module: modules.students };
}

test('a student profile link identifies a record even when the list has no edit links', (t) => {
    browser(t);

    assert.equal(recordIdFromPath('/students/__ID__', '/students/17'), 17);
    assert.equal(recordIdFromPath('/students/__ID__/edit', '/students/17'), null);
    assert.equal(recordIdFromPath('/students/__ID__', '/students/create'), null);
    assert.equal(recordIdFromPath('/students/__ID__', 'https://other.example/students/17'), null);
});

test('student browsing reads actual profile rows and sends the school context with the search', async (t) => {
    const { module } = browser(t);
    const originalParser = globalThis.DOMParser;
    const row = { querySelectorAll: () => [
        { textContent: '1' }, { textContent: 'Naruto Uzumaki' }, { textContent: 'NIS 1001' }, { textContent: 'VII-A' },
    ] };
    const link = { getAttribute: () => '/students/17', closest: () => row };
    globalThis.DOMParser = class {
        parseFromString() { return { querySelectorAll: (selector) => selector === 'main a[href]' ? [link] : [] }; }
    };
    t.after(() => { globalThis.DOMParser = originalParser; });
    const fetchMock = t.mock.method(globalThis, 'fetch', async () => ({
        ok: true, status: 200, url: 'https://eduvia.test/students?search=naruto+uzumaki', text: async () => '<main>Student list</main>',
    }));
    const workspace = new AgentWorkspace(1);

    const result = await workspace.browse(module, { name: 'browse', resource: 'students', query: 'naruto uzumaki', page: 1 });

    assert.deepEqual(result.records, [{ id: 17, summary: '1 | Naruto Uzumaki | NIS 1001 | VII-A' }]);
    const [url, options] = fetchMock.mock.calls[0].arguments;
    assert.equal(url.searchParams.get('search'), 'naruto uzumaki');
    assert.equal(options.headers['X-School-ID'], '1');
    assert.equal(options.credentials, 'same-origin');
});

test('name searches read school data and persist results without calling Gemini', async (t) => {
    const { chat, values } = browser(t);
    const browse = t.mock.method(AgentWorkspace.prototype, 'browse', async () => ({
        records: [{ id: 17, summary: 'Naruto Uzumaki | VII-A' }], url: '/students?search=naruto+uzumaki',
    }));
    const provider = t.mock.method(globalThis, 'fetch', async () => { throw new Error('Gemini unavailable'); });
    chat.prompt = 'cari siswa yang namanya naruto uzumaki';

    await chat.send();

    assert.equal(browse.mock.callCount(), 1);
    assert.equal(browse.mock.calls[0].arguments[1].query, 'naruto uzumaki');
    assert.equal(provider.mock.callCount(), 0);
    assert.match(chat.messages.at(-1).text, /Naruto Uzumaki/);
    assert.equal(chat.lastUrl, '/students?search=naruto+uzumaki');
    assert.equal(chat.changed, false);
    assert.equal(parseConversation(values.get(conversationStorageKey(7, 1))).messages.at(-1).text, chat.messages.at(-1).text);
});

test('accounts without access to student management do not issue a search', async (t) => {
    const { chat } = browser(t, false);
    const browse = t.mock.method(AgentWorkspace.prototype, 'browse', async () => { throw new Error('Forbidden'); });
    chat.prompt = 'cari siswa Naruto Uzumaki';

    await chat.send();

    assert.equal(browse.mock.callCount(), 0);
    assert.match(chat.messages.at(-1).text, /tidak tersedia untuk akun/);
    assert.equal(chat.lastUrl, '');
});

test('empty student results are reported honestly', async (t) => {
    const { chat } = browser(t);
    t.mock.method(AgentWorkspace.prototype, 'browse', async () => ({ records: [], url: '/students?search=naruto' }));
    chat.prompt = 'cari siswa Naruto';

    await chat.send();

    assert.match(chat.messages.at(-1).text, /Tidak ditemukan siswa/);
    assert.equal(chat.error, '');
    assert.equal(chat.busy, false);
});

test('a failed internal search does not claim the student exists or retain a stale result link', async (t) => {
    const { chat } = browser(t);
    t.mock.method(AgentWorkspace.prototype, 'browse', async () => { throw new Error('Periksa sesi dan hak akses.'); });
    chat.lastUrl = '/students?search=old';
    chat.prompt = 'cari siswa Naruto';

    await chat.send();

    assert.equal(chat.error, 'Periksa sesi dan hak akses.');
    assert.equal(chat.lastUrl, '');
    assert.equal(chat.busy, false);
    assert.equal(chat.messages.filter((message) => message.role === 'assistant').length, 0);
});
