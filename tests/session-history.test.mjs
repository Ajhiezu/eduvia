import test from 'node:test';
import assert from 'node:assert/strict';
import { initializeSessionHistoryGuard } from '../resources/js/session-history.js';

function page(authenticated = true) {
    const listeners = new Map();
    let reloads = 0;
    const root = {
        querySelector: () => authenticated ? {} : null,
        documentElement: { style: { visibility: '' }, inert: false },
    };
    const browser = {
        addEventListener: (name, listener) => listeners.set(name, listener),
        location: { reload: () => { reloads++; } },
    };
    initializeSessionHistoryGuard(root, browser);
    return { root, listeners, reloads: () => reloads };
}

test('restoring a protected page with Back conceals it and requests a fresh server authentication check', () => {
    const protectedPage = page();

    protectedPage.listeners.get('pageshow')({ persisted: true });

    assert.equal(protectedPage.root.documentElement.style.visibility, 'hidden');
    assert.equal(protectedPage.root.documentElement.inert, true);
    assert.equal(protectedPage.reloads(), 1);
});

test('protected content is concealed before the browser saves a history snapshot', () => {
    const protectedPage = page();

    protectedPage.listeners.get('pagehide')({ persisted: true });

    assert.equal(protectedPage.root.documentElement.style.visibility, 'hidden');
    assert.equal(protectedPage.root.documentElement.inert, true);
    assert.equal(protectedPage.reloads(), 0);
});

test('a normal protected page load remains visible without a reload loop', () => {
    const protectedPage = page();

    protectedPage.listeners.get('pageshow')({ persisted: false });
    protectedPage.listeners.get('pagehide')({ persisted: false });

    assert.equal(protectedPage.root.documentElement.style.visibility, '');
    assert.equal(protectedPage.root.documentElement.inert, false);
    assert.equal(protectedPage.reloads(), 0);
});

test('login and public pages retain normal browser navigation', () => {
    const publicPage = page(false);

    assert.equal(publicPage.listeners.size, 0);
    assert.equal(publicPage.root.documentElement.style.visibility, '');
    assert.equal(publicPage.reloads(), 0);
});
