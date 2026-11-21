import test from 'node:test';
import assert from 'node:assert/strict';
import { describeAgentForm, applyAgentFields, isSafeAgentField, aiAgent } from '../resources/js/ai-agent.js';

function control(name, value = '', extras = {}) {
    return { name, value, tagName: 'INPUT', type: 'text', disabled: false, readOnly: false, events: [],
        getClientRects: () => [1], closest: () => null, getAttribute: () => null,
        dispatchEvent(event) { this.events.push(event.type); }, ...extras };
}

test('sensitive nested names are blocked while question options are allowed', () => {
    for (const name of ['school_id', 'user[role]', 'credentials.password', '_token', 'student_id']) assert.equal(isSafeAgentField(name), false);
    assert.equal(isSafeAgentField('options[0][text]'), true);
});

test('form context contains labels and choices without current values or private controls', () => {
    const form = { elements: [control('question', 'private existing question', { tagName: 'TEXTAREA', labels: [{ textContent: 'Pertanyaan' }] }), control('password', 'secret', { type: 'password' }), control('school_id', '1'), control('hidden', 'secret', { type: 'hidden' }), control('disabled', '', { disabled: true }), control('invisible', '', { getClientRects: () => [] }), control('type', 'essay', { tagName: 'SELECT', options: [{ value: 'essay', textContent: 'Esai', disabled: false }] })] };
    assert.deepEqual(describeAgentForm(form), [{ name: 'question', label: 'Pertanyaan', type: 'textarea' }, { name: 'type', label: 'type', type: 'select', options: [{ value: 'essay', label: 'Esai' }] }]);
    assert.equal(JSON.stringify(describeAgentForm(form)).includes('private existing question'), false);
});

test('draft applies input and change events, can undo, and never submits', () => {
    const question = control('question', 'Original', { tagName: 'TEXTAREA' });
    const form = { elements: [question], submit() { assert.fail('AI must never submit'); } };
    const undo = applyAgentFields(form, [{ name: 'question', value: 'New draft' }]);
    assert.equal(question.value, 'New draft');
    assert.deepEqual(question.events, ['input', 'change']);
    applyAgentFields(form, undo);
    assert.equal(question.value, 'Original');
});

test('unknown choices, fields and protected names cannot be applied', () => {
    const type = control('type', 'essay', { tagName: 'SELECT', options: [{ value: 'essay', disabled: false }] });
    const role = control('role', 'teacher');
    assert.deepEqual(applyAgentFields({ elements: [type, role] }, [{ name: 'type', value: 'sql' }, { name: 'role', value: 'super_admin' }, { name: 'unknown', value: 'x' }]), []);
    assert.equal(type.value, 'essay');
    assert.equal(role.value, 'teacher');
});

test('radio selection and undo restore an initially empty answer', () => {
    const a = control('correct_answer', 'A', { type: 'radio', checked: false });
    const b = control('correct_answer', 'B', { type: 'radio', checked: false });
    const form = { elements: [a, b] };
    const undo = applyAgentFields(form, [{ name: 'correct_answer', value: 'B' }]);
    assert.equal(b.checked, true);
    assert.equal(a.checked, false);
    applyAgentFields(form, undo);
    assert.equal(b.checked, false);
    assert.equal(a.checked, false);
});

test('reapplying a draft does not destroy original undo values', () => {
    const field = control('question', 'Original');
    const agent = aiAgent('/generate', '/status');
    agent.resultForm = { isConnected: true, elements: [field] };
    agent.result = { fields: [{ name: 'question', value: 'Draft' }] };
    agent.applyDraft();
    agent.applyDraft();
    agent.undoDraft();
    assert.equal(field.value, 'Original');
});
