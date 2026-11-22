import test from 'node:test';
import assert from 'node:assert/strict';
import { buildSearchUrl, createLiveSearch } from '../resources/js/live-search.js';

function page(html = '<main>Hasil terbaru</main>', properties = {}) {
    return {
        ok: true,
        headers: new Headers({ 'content-type': 'text/html; charset=UTF-8' }),
        text: async () => html,
        ...properties,
    };
}

function deferred() {
    let resolve;
    const promise = new Promise((finish) => { resolve = finish; });
    return { promise, resolve };
}

test('typing a search preserves filters, encodes the query and resets pagination', () => {
    const url = buildSearchUrl('/students?view=list&page=9', [
        ['search', 'Siti & Ahmad'], ['status', 'active'], ['classroom_id', '12'], ['grade_level_id', '3'], ['page', '9'],
    ], 'https://eduvia.test/students?page=9');

    assert.equal(url.pathname, '/students');
    assert.equal(url.searchParams.get('view'), 'list');
    assert.equal(url.searchParams.get('search'), 'Siti & Ahmad');
    assert.equal(url.searchParams.get('status'), 'active');
    assert.equal(url.searchParams.get('classroom_id'), '12');
    assert.equal(url.searchParams.get('grade_level_id'), '3');
    assert.equal(url.searchParams.has('page'), false);
    assert.match(url.href, /search=Siti\+%26\+Ahmad/);
});

test('clearing the query and unchecking a filter removes old values without losing repeated selections', () => {
    const url = buildSearchUrl('', [
        ['search', ''], ['category[]', 'A'], ['category[]', 'B'], ['is_active', '0'],
    ], 'https://eduvia.test/website/messages?search=old&unread_only=1&messages_page=5', ['search', 'unread_only', 'category[]', 'is_active']);

    assert.equal(url.searchParams.get('search'), '');
    assert.equal(url.searchParams.has('unread_only'), false);
    assert.equal(url.searchParams.has('messages_page'), false);
    assert.deepEqual(url.searchParams.getAll('category[]'), ['A', 'B']);
    assert.equal(url.searchParams.get('is_active'), '0');
});

test('changing the search retains the page-size filter while resetting the current page', () => {
    const url = buildSearchUrl('/website/messages?per_page=50&page=4', [
        ['search', 'pendaftaran'], ['per_page', '25'], ['sort', 'oldest'],
    ], 'https://eduvia.test/website/messages?page=4');

    assert.equal(url.searchParams.get('per_page'), '25');
    assert.equal(url.searchParams.get('sort'), 'oldest');
    assert.equal(url.searchParams.has('page'), false);
});

test('searches are debounced so continuous typing sends only the latest query', async (t) => {
    t.mock.timers.enable({ apis: ['setTimeout'] });
    const requests = [];
    const rendered = [];
    const search = createLiveSearch({
        fetchPage: async (url, options) => {
            requests.push({ url, options });
            return page();
        },
        render: (...args) => rendered.push(args),
    });

    search.schedule('https://eduvia.test/students?search=Si');
    t.mock.timers.tick(200);
    search.schedule('https://eduvia.test/students?search=Siti');
    t.mock.timers.tick(299);
    assert.equal(requests.length, 0);
    t.mock.timers.tick(1);
    await new Promise((resolve) => queueMicrotask(() => queueMicrotask(resolve)));

    assert.equal(requests.length, 1);
    assert.equal(requests[0].url, 'https://eduvia.test/students?search=Siti');
    assert.equal(requests[0].options.method, 'GET');
    assert.equal(requests[0].options.credentials, 'same-origin');
    assert.equal(requests[0].options.headers.Accept, 'text/html');
    assert.equal(rendered.length, 1);
});

test('a slow previous response cannot overwrite a newer result even when abort is ignored', async () => {
    const first = deferred();
    const signals = [];
    const rendered = [];
    const search = createLiveSearch({
        fetchPage: (url, options) => {
            signals.push(options.signal);
            return url.includes('search=old') ? first.promise : Promise.resolve(page('new results'));
        },
        render: (html) => rendered.push(html),
    });

    const oldRequest = search.request('https://eduvia.test/students?search=old');
    await search.request('https://eduvia.test/students?search=new');
    first.resolve(page('old results'));
    await oldRequest;

    assert.equal(signals[0].aborted, true);
    assert.deepEqual(rendered, ['new results']);
});

test('typing another query invalidates a running request before the debounce expires', async (t) => {
    t.mock.timers.enable({ apis: ['setTimeout'] });
    const first = deferred();
    const rendered = [];
    const search = createLiveSearch({ fetchPage: () => first.promise, render: (html) => rendered.push(html) });

    const pending = search.request('https://eduvia.test/students?search=old');
    search.schedule('https://eduvia.test/students?search=typed');
    first.resolve(page('obsolete results'));
    await pending;

    assert.deepEqual(rendered, []);
    search.cancel();
});

test('cancelling composition or leaving the page prevents a scheduled search', (t) => {
    t.mock.timers.enable({ apis: ['setTimeout'] });
    let requests = 0;
    const search = createLiveSearch({ fetchPage: async () => { requests++; return page(); }, render: () => {} });

    search.schedule('https://eduvia.test/students?search=unfinished');
    search.cancel();
    t.mock.timers.tick(1000);

    assert.equal(requests, 0);
});

test('server errors keep the existing table and allow retrying the same query', async () => {
    const states = [];
    const rendered = [];
    let attempts = 0;
    const search = createLiveSearch({
        fetchPage: async () => ++attempts === 1 ? page('server error', { ok: false, status: 500 }) : page('recovered'),
        render: (html) => rendered.push(html),
        report: (state) => states.push(state),
    });

    assert.equal(await search.request('https://eduvia.test/students?search=Siti'), false);
    assert.deepEqual(rendered, []);
    assert.equal(states.at(-1), 'error');
    assert.equal(await search.request('https://eduvia.test/students?search=Siti'), true);
    assert.deepEqual(rendered, ['recovered']);
    assert.equal(states.at(-1), 'idle');
});

test('non-HTML responses and missing content leave the current page intact', async () => {
    const states = [];
    let renders = 0;
    const search = createLiveSearch({
        fetchPage: async () => page('{}', { headers: new Headers({ 'content-type': 'application/json' }) }),
        render: () => { renders++; },
        report: (state) => states.push(state),
    });

    await search.request('https://eduvia.test/students?search=Siti');

    assert.equal(renders, 0);
    assert.equal(states.at(-1), 'error');
    const missing = createLiveSearch({ fetchPage: async () => page(), render: () => { throw new Error('Missing main'); }, report: (state) => states.push(state) });
    assert.equal(await missing.request('https://eduvia.test/students'), false);
    assert.equal(states.at(-1), 'error');
});

test('an expired session redirects to login instead of inserting the login form into the table', async () => {
    const destinations = [];
    let renders = 0;
    const search = createLiveSearch({
        fetchPage: async () => page('login', { redirected: true, url: 'https://eduvia.test/login' }),
        render: () => { renders++; },
        navigate: (url) => destinations.push(url),
    });

    await search.request('https://eduvia.test/students?search=Siti');

    assert.equal(renders, 0);
    assert.deepEqual(destinations, ['https://eduvia.test/login']);
});

test('pagination and back-navigation options reach the renderer unchanged', async () => {
    const rendered = [];
    const search = createLiveSearch({ fetchPage: async () => page(), render: (html, url, options) => rendered.push({ url, options }) });

    await search.request('https://eduvia.test/students?search=Siti&page=2', { history: 'push' });
    await search.request('https://eduvia.test/students?search=Siti', { history: 'none' });

    assert.deepEqual(rendered, [
        { url: 'https://eduvia.test/students?search=Siti&page=2', options: { history: 'push' } },
        { url: 'https://eduvia.test/students?search=Siti', options: { history: 'none' } },
    ]);
});
