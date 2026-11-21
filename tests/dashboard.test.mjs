import test from 'node:test';
import assert from 'node:assert/strict';
import { dashboardCalendar, sidebarScroll } from '../resources/js/dashboard.js';

test('calendar includes leap day and complete Monday-first weeks', () => {
    const calendar = dashboardCalendar();
    calendar.displayedMonth = new Date(2028, 1, 1);
    const dates = calendar.days.filter((day) => day.currentMonth);
    assert.equal(dates.length, 29);
    assert.equal(dates.at(-1).date, 29);
    assert.equal(new Date(calendar.days[0].timestamp).getDay(), 1);
    assert.equal(calendar.days.length % 7, 0);
    assert.equal(new Set(calendar.days.map((day) => day.key)).size, calendar.days.length);
});

test('calendar navigation crosses year boundaries correctly', () => {
    const calendar = dashboardCalendar();
    calendar.displayedMonth = new Date(2026, 0, 1);
    calendar.changeMonth(-1);
    assert.equal(calendar.displayedMonth.getFullYear(), 2025);
    assert.equal(calendar.displayedMonth.getMonth(), 11);
    calendar.changeMonth(1);
    assert.equal(calendar.displayedMonth.getFullYear(), 2026);
    assert.equal(calendar.displayedMonth.getMonth(), 0);
});

test('selecting an adjacent-month day updates selection and the displayed month', () => {
    const calendar = dashboardCalendar();
    calendar.displayedMonth = new Date(2026, 10, 1);
    const day = calendar.days.find((item) => !item.currentMonth);
    calendar.selectDate(day);
    assert.equal(calendar.selectedKey, day.key);
    assert.equal(calendar.displayedMonth.getMonth(), new Date(day.timestamp).getMonth());
    assert.equal(calendar.selectedDate.getDate(), day.date);
});

function setupSidebarStorage(t) {
    const originalWindow = globalThis.window;
    const values = new Map();
    globalThis.window = {
        sessionStorage: {
            getItem: (key) => values.get(key) ?? null,
            setItem: (key, value) => values.set(key, value),
        },
    };
    t.after(() => { globalThis.window = originalWindow; });
    return values;
}

function mountSidebar(userId, scrollTop = 0, collapsed = false) {
    const sidebar = sidebarScroll(userId);
    sidebar.$el = { scrollTop };
    sidebar.$nextTick = (callback) => callback();
    sidebar.$watch = () => {};
    sidebar.sidebarCollapsed = collapsed;
    sidebar.init();
    return sidebar;
}

test('sidebar keeps its scroll position after navigating to another page', (t) => {
    setupSidebarStorage(t);
    const firstPage = mountSidebar('42');
    firstPage.$el.scrollTop = 875;
    firstPage.savePosition();

    const nextPage = mountSidebar('42');

    assert.equal(nextPage.$el.scrollTop, 875);
});

test('sidebar scroll positions stay separate for different signed-in users', (t) => {
    setupSidebarStorage(t);
    const firstUser = mountSidebar('42');
    firstUser.$el.scrollTop = 875;
    firstUser.savePosition();

    const secondUser = mountSidebar('43');

    assert.equal(secondUser.$el.scrollTop, 0);
});

test('sidebar ignores invalid stored scroll positions', (t) => {
    const values = setupSidebarStorage(t);
    const sidebar = mountSidebar('42');

    for (const position of ['invalid', '-100', 'Infinity']) {
        values.set(sidebar.storageKey, position);
        sidebar.init();
        assert.equal(sidebar.$el.scrollTop, 0);
    }
});

test('sidebar stays usable when browser storage is blocked', (t) => {
    setupSidebarStorage(t);
    t.mock.method(window.sessionStorage, 'getItem', () => { throw new Error('Storage blocked'); });
    t.mock.method(window.sessionStorage, 'setItem', () => { throw new Error('Storage blocked'); });

    const sidebar = mountSidebar('42');

    assert.doesNotThrow(() => sidebar.savePosition());
    assert.equal(sidebar.$el.scrollTop, 0);
});

test('collapsing the sidebar cannot overwrite its expanded scroll position', (t) => {
    setupSidebarStorage(t);
    const sidebar = mountSidebar('42');
    sidebar.$el.scrollTop = 875;
    sidebar.savePosition();

    sidebar.$el.scrollTop = 0;
    sidebar.setCollapsed(true);
    assert.equal(sidebar.$el.scrollTop, 875);
    sidebar.savePosition();
    sidebar.setCollapsed(false);

    assert.equal(sidebar.$el.scrollTop, 875);
});

test('expanded and collapsed sidebar positions restore independently across pages', (t) => {
    setupSidebarStorage(t);
    const sidebar = mountSidebar('42');
    sidebar.$el.scrollTop = 875;
    sidebar.savePosition();
    sidebar.setCollapsed(true);
    sidebar.$el.scrollTop = 120;
    sidebar.savePosition();

    const expandedPage = mountSidebar('42');
    const collapsedPage = mountSidebar('42', 0, true);

    assert.equal(expandedPage.$el.scrollTop, 875);
    assert.equal(collapsedPage.$el.scrollTop, 120);
    expandedPage.setCollapsed(true);
    assert.equal(expandedPage.$el.scrollTop, 120);
});

test('sidebar ignores intermediate scroll changes until its resize transition finishes', async (t) => {
    setupSidebarStorage(t);
    const sidebar = mountSidebar('42');
    sidebar.$el.scrollTop = 875;
    sidebar.savePosition();
    let finishTransition;
    const transition = new Promise((resolve) => { finishTransition = resolve; });
    sidebar.$el.closest = () => ({ getAnimations: () => [{ finished: transition }] });

    sidebar.setCollapsed(true);
    sidebar.$el.scrollTop = 0;
    sidebar.savePosition();
    finishTransition();
    await new Promise(setImmediate);

    assert.equal(sidebar.$el.scrollTop, 875);
    sidebar.setCollapsed(false);
    sidebar.$el.scrollTop = 600;
    sidebar.savePosition();
    await new Promise(setImmediate);
    assert.equal(sidebar.$el.scrollTop, 875);
});
