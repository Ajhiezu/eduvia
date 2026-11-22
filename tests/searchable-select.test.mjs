import test from 'node:test';
import assert from 'node:assert/strict';
import { searchableOptions, shouldSearchSelect } from '../resources/js/searchable-select.js';

function option(value, label, properties = {}) {
    const { group = null, ...attributes } = properties;
    return { value, label, hidden: false, closest: () => group, ...attributes };
}

function select(options, properties = {}) {
    return { options, dataset: {}, size: 0, multiple: false, ...properties };
}

test('search is enabled for long lists without counting empty placeholders', () => {
    const control = select([
        option('', 'Pilih kelas'),
        option('1', 'Kelas 1'), option('2', 'Kelas 2'), option('3', 'Kelas 3'),
        option('4', 'Kelas 4'), option('5', 'Kelas 5'),
    ]);

    assert.equal(shouldSearchSelect(control), false);
    control.options.push(option('6', 'Kelas 6'));
    assert.equal(shouldSearchSelect(control), true);
});

test('search matches multiple words regardless of case and order', () => {
    const control = select([
        option('1', 'Ahmad Fauzi — SMA 10 A'),
        option('2', 'Ahmad Rizki — SMP 7 B'),
        option('3', 'Siti Fauzi — SMA 10 B'),
    ]);

    assert.deepEqual(searchableOptions(control, '  SMA   AHMAD ').map((item) => item.value), ['1']);
    assert.deepEqual(searchableOptions(control, 'tidak ditemukan'), []);
    assert.equal(searchableOptions(control, '  ').length, 3);
});

test('group names are searchable and hidden options and groups stay hidden', () => {
    const control = select([
        option('1', 'Matematika', { group: { label: 'SMA' } }),
        option('2', 'Matematika', { group: { label: 'SMP' } }),
        option('3', 'Fisika', { hidden: true, group: { label: 'SMA' } }),
        option('4', 'Kimia', { group: { label: 'SMA', hidden: true } }),
    ]);

    assert.deepEqual(searchableOptions(control, 'sma').map((item) => item.value), ['1']);
});

test('filtering keeps selected and disabled options intact without changing form values', () => {
    const selected = option('101', 'Siswa Terpilih', { selected: true });
    const disabled = option('102', 'Siswa Nonaktif', { disabled: true });
    const control = select([selected, disabled]);

    assert.deepEqual(searchableOptions(control, 'Nonaktif'), [disabled]);
    assert.equal(selected.selected, true);
    assert.equal(selected.value, '101');
    assert.equal(disabled.disabled, true);
});

test('explicit preferences and native listboxes are respected', () => {
    const options = Array.from({ length: 7 }, (_, index) => option(String(index), `Kelas ${index}`));

    assert.equal(shouldSearchSelect(select(options, { dataset: { searchable: 'false' } })), false);
    assert.equal(shouldSearchSelect(select([], { dataset: { searchable: 'true' } })), true);
    assert.equal(shouldSearchSelect(select(options, { size: 7 })), false);
    assert.equal(shouldSearchSelect(select(options, { size: 7, multiple: true })), true);
    assert.equal(shouldSearchSelect(select([option('1', 'Tersembunyi', { hidden: true }), ...options.slice(0, 5)])), false);
});
