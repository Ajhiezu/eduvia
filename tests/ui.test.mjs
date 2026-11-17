import test from 'node:test';
import assert from 'node:assert/strict';
import { childSwitcher, toastNotifications } from '../resources/js/ui.js';

function setupBrowser(t) {
    const originalWindow = globalThis.window;
    const originalDocument = globalThis.document;
    const destinations = [];
    globalThis.window = {
        location: {
            href: 'http://eduvia.test/parent/grades',
            assign: (url) => destinations.push(url),
        },
    };
    globalThis.document = { querySelector: () => ({ content: 'test-csrf-token' }) };
    t.after(() => {
        globalThis.window = originalWindow;
        globalThis.document = originalDocument;
    });
    return destinations;
}

test('switching children uses the existing endpoint and returns to the current menu', async (t) => {
    const destinations = setupBrowser(t);
    const request = t.mock.method(globalThis, 'fetch', async () => ({ ok: true, url: 'http://eduvia.test/parent/dashboard' }));
    const switcher = childSwitcher();

    await switcher.selectChild('/parent/switch-child/42', '42', '/parent/grades');

    assert.deepEqual(destinations, ['/parent/grades']);
    assert.equal(request.mock.calls[0].arguments[0], '/parent/switch-child/42');
    assert.equal(request.mock.calls[0].arguments[1].method, 'POST');
    assert.equal(request.mock.calls[0].arguments[1].headers['X-CSRF-TOKEN'], 'test-csrf-token');
});

test('failed child switches remain on the current page and allow a retry', async (t) => {
    const destinations = setupBrowser(t);
    t.mock.method(globalThis, 'fetch', async () => ({ ok: false, status: 419 }));
    const switcher = childSwitcher();

    await switcher.selectChild('/parent/switch-child/42', '42', '/parent/grades');

    assert.deepEqual(destinations, []);
    assert.equal(switcher.busyChildId, null);
    assert.match(switcher.errorMessage, /coba kembali/);
});

test('expired sessions return parents to the login screen', async (t) => {
    const destinations = setupBrowser(t);
    t.mock.method(globalThis, 'fetch', async () => ({ ok: true, url: 'http://eduvia.test/login' }));
    const switcher = childSwitcher();

    await switcher.selectChild('/parent/switch-child/42', '42', '/parent/grades');

    assert.deepEqual(destinations, ['http://eduvia.test/login']);
});

test('another child cannot be selected while the previous switch is pending', async (t) => {
    setupBrowser(t);
    let finishRequest;
    const request = t.mock.method(globalThis, 'fetch', () => new Promise((resolve) => { finishRequest = resolve; }));
    const switcher = childSwitcher();
    const pending = switcher.selectChild('/parent/switch-child/42', '42', '/parent/grades');

    await switcher.selectChild('/parent/switch-child/43', '43', '/parent/grades');
    assert.equal(request.mock.callCount(), 1);
    finishRequest({ ok: true, url: 'http://eduvia.test/parent/dashboard' });
    await pending;
});

test('error notifications remain visible until dismissed', (t) => {
    t.mock.timers.enable({ apis: ['setTimeout'] });
    const notifications = toastNotifications();
    notifications.add('Berkas gagal disimpan.', 'error');

    t.mock.timers.tick(60000);
    assert.equal(notifications.toasts.length, 1);
    assert.equal(notifications.toasts[0].type, 'danger');
    notifications.remove(notifications.toasts[0].id);
    assert.equal(notifications.toasts.length, 0);
});

test('notification timers pause while a message is being read', (t) => {
    t.mock.timers.enable({ apis: ['setTimeout'] });
    const notifications = toastNotifications();
    notifications.add('Perubahan tersimpan.', 'success');
    const toast = notifications.toasts[0];
    notifications.pause(toast.id);

    t.mock.timers.tick(10000);
    assert.equal(notifications.toasts.length, 1);
    notifications.resume(toast);
    t.mock.timers.tick(7000);
    assert.equal(notifications.toasts.length, 0);
});

test('notification overflow keeps the newest messages and releases old timers', (t) => {
    t.mock.timers.enable({ apis: ['setTimeout'] });
    const notifications = toastNotifications();
    for (let number = 1; number <= 6; number++) {
        notifications.add(`Pesan ${number}`, 'info');
    }

    assert.equal(notifications.toasts.length, 5);
    assert.equal(notifications.toasts[0].message, 'Pesan 2');
    assert.equal(notifications.timers.size, 5);
    notifications.destroy();
    assert.equal(notifications.timers.size, 0);
});
