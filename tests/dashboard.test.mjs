import test from 'node:test';
import assert from 'node:assert/strict';
import { dashboardCalendar } from '../resources/js/dashboard.js';

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
