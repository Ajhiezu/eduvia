import test from 'node:test';
import assert from 'node:assert/strict';
import { navigationRequest, resolveNavigation } from '../resources/js/ai-agent-navigation.js';
import { aiAgentChat } from '../resources/js/ai-agent-actions.js';
import { conversationStorageKey, parseConversation } from '../resources/js/ai-agent-conversation.js';

const currentUrl = 'https://eduvia.test/dashboard';
const menu = [
    { label: 'Dashboard', url: '/dashboard' },
    { label: 'Data Siswa', url: '/students' },
    { label: 'Tagihan Siswa', url: '/finance/invoices' },
    { label: 'Data Master Kelas', url: '/classrooms' },
    { label: 'Materi Belajar', url: '/learning-materials' },
    { label: 'Mata Pelajaran', url: '/subjects' },
    { label: 'Bank Soal', url: '/cbt/questions' },
    { label: 'Tugas & Penilaian', url: '/assignments' },
    { label: 'Guru & Staf (PTK)', url: '/staff' },
    { label: 'Jadwal Mengajar', url: '/teacher/schedule' },
    { label: 'Jadwal Pelajaran', url: '/schedules' },
];

const creationPages = [
    { label: 'Tambah Materi pembelajaran', url: '/learning-materials/create' },
    { label: 'Tambah Guru dan staf', url: '/staff/create' },
    { label: 'Tambah Data siswa', url: '/students/create' },
    { label: 'Tambah Bank soal CBT', url: '/cbt/questions/create' },
];

test('add, create, buat and tambah open the permitted creation page from any module', () => {
    for (const text of [
        'buka halaman add materi', 'buka halaman tambah materi', 'buka halaman create materi',
        'buka halaman buat materi pembelajaran', 'buka formulir tambah materi', 'open page create material',
    ]) {
        const result = resolveNavigation(text, [...menu, ...creationPages], currentUrl);
        assert.equal(result.matches.length, 1, text);
        assert.equal(result.matches[0].url, 'https://eduvia.test/learning-materials/create', text);
    }
});

test('creation links do not make an ordinary menu request ambiguous', () => {
    const result = resolveNavigation('buka halaman guru', [...menu, ...creationPages], currentUrl);

    assert.deepEqual(result.matches, [{ label: 'Guru & Staf (PTK)', url: 'https://eduvia.test/staff' }]);
});

test('an unavailable creation page is never guessed from a list URL', () => {
    assert.deepEqual(resolveNavigation('buka halaman add materi', menu, currentUrl).matches, []);
});

test('an explicit page command resolves the application menu including common Indonesian aliases', () => {
    for (const [text, path] of [
        ['buka halaman siswa', '/students'],
        ['Tolong bukakan menu peserta didik sekarang ya!', '/students'],
        ['buka halaman manajemen siswa', '/students'],
        ['buka halaman rombel', '/classrooms'],
        ['buka halaman materi pembelajaran', '/learning-materials'],
        ['buka halaman tugas pembelajaran', '/assignments'],
        ['buka halaman staff', '/staff'],
        ['buka halaman mapel', '/subjects'],
        ['tampilkan halaman Bank Soal', '/cbt/questions'],
        ['arahkan saya ke halaman SPP', '/finance/invoices'],
        ['buka beranda', '/dashboard'],
    ]) {
        const result = resolveNavigation(text, menu, currentUrl);
        assert.equal(result.matches.length, 1, text);
        assert.equal(result.matches[0].url, `https://eduvia.test${path}`, text);
    }
});

test('ambiguous page names provide choices instead of opening an arbitrary page', () => {
    const result = resolveNavigation('buka halaman jadwal', menu, currentUrl);

    assert.deepEqual(result.matches.map((page) => page.label), ['Jadwal Mengajar', 'Jadwal Pelajaran']);
});

test('pages that are absent from the current user menu cannot be opened', () => {
    const teacherMenu = menu.filter((page) => !['Data Siswa', 'Tagihan Siswa'].includes(page.label));

    assert.deepEqual(resolveNavigation('buka halaman siswa', teacherMenu, currentUrl).matches, []);
});

test('navigation never accepts an external URL, script, fragment or URL with embedded credentials', () => {
    const unsafe = [
        { label: 'Data Siswa', url: 'https://other.example/students' },
        { label: 'Data Siswa', url: '//other.example/students' },
        { label: 'Data Siswa', url: 'javascript:alert(1)' },
        { label: 'Data Siswa', url: '#students' },
        { label: 'Data Siswa', url: 'https://someone:password@eduvia.test/students' },
    ];

    assert.deepEqual(resolveNavigation('buka halaman siswa', unsafe, currentUrl).matches, []);
});

test('different labels pointing to the same permitted page remain searchable without duplicate results', () => {
    const payments = [
        { label: 'Riwayat Pembayaran', url: '/finance/payments' },
        { label: 'Verifikasi Pembayaran', url: '/finance/payments' },
    ];

    assert.equal(resolveNavigation('buka halaman verifikasi pembayaran', payments, currentUrl).matches.length, 1);
    assert.equal(resolveNavigation('buka halaman pembayaran', payments, currentUrl).matches.length, 1);
});

test('data queries, negation and commands with further operations remain with the operational agent', () => {
    for (const text of [
        'Cari siswa Ahmad', 'Tampilkan data siswa', 'Jangan buka halaman siswa',
        'Buka halaman siswa lalu cari Ahmad', 'Buka halaman bank soal dan tambahkan soal baru',
    ]) {
        assert.equal(navigationRequest(text), null, text);
    }
});

function browser(t, entries = menu, extraPages = []) {
    const previousWindow = globalThis.window;
    const previousDocument = globalThis.document;
    const values = new Map();
    const destinations = [];
    let savedBeforeNavigation = null;
    globalThis.window = {
        location: {
            href: currentUrl, pathname: '/dashboard', origin: 'https://eduvia.test',
            assign(url) {
                savedBeforeNavigation = values.get(conversationStorageKey(7, 1));
                destinations.push(url);
            },
        },
        localStorage: { getItem: (key) => values.get(key) ?? null, setItem: (key, value) => values.set(key, value), removeItem: (key) => values.delete(key) },
    };
    globalThis.document = {
        querySelectorAll: () => entries.map((page) => ({ textContent: page.label, href: new URL(page.url, currentUrl).href, getAttribute: () => page.url })),
        querySelector: () => ({ content: 'csrf-test' }),
    };
    t.after(() => { globalThis.window = previousWindow; globalThis.document = previousDocument; });
    const chat = aiAgentChat('/run', '/status', 7, 1, extraPages);
    chat.$nextTick = () => {};
    chat.restoreConversation(1);
    chat.status = { enabled: true, configured: true, school_id: 1 };
    return { chat, destinations, saved: () => parseConversation(savedBeforeNavigation) };
}

test('sending a page command navigates immediately and saves both messages before leaving', async (t) => {
    const { chat, destinations, saved } = browser(t);
    const requests = t.mock.method(globalThis, 'fetch', async () => { throw new Error('Should not call Gemini'); });
    chat.prompt = 'Buka halaman siswa';

    await chat.send();

    assert.deepEqual(destinations, ['https://eduvia.test/students']);
    assert.equal(requests.mock.callCount(), 0);
    assert.equal(saved().messages[0].text, 'Buka halaman siswa');
    assert.equal(saved().messages[1].text, 'Membuka halaman Data Siswa.');
    assert.equal(saved().history.length, 2);
    assert.equal(saved().prompt, '');
});

test('the chat opens add materi using server-provided creation links while retaining the transcript', async (t) => {
    const { chat, destinations, saved } = browser(t, menu, creationPages);
    const requests = t.mock.method(globalThis, 'fetch', async () => { throw new Error('Should not call Gemini'); });
    chat.prompt = 'buka halaman add materi';

    await chat.send();

    assert.deepEqual(destinations, ['https://eduvia.test/learning-materials/create']);
    assert.equal(requests.mock.callCount(), 0);
    assert.equal(saved().messages[0].text, 'buka halaman add materi');
    assert.equal(saved().messages[1].text, 'Membuka halaman Tambah Materi pembelajaran.');
});

test('ambiguous and unavailable page requests stay on the current page with an explanation', async (t) => {
    const { chat, destinations } = browser(t);
    chat.prompt = 'Buka halaman jadwal';
    await chat.send();

    assert.deepEqual(destinations, []);
    assert.match(chat.messages.at(-1).text, /Jadwal Mengajar, Jadwal Pelajaran/);
    chat.prompt = 'Buka halaman rahasia';
    await chat.send();
    assert.deepEqual(destinations, []);
    assert.match(chat.messages.at(-1).text, /tidak ditemukan pada menu akun/);
});
