export function conversationStorageKey(userId, schoolId) {
    if (![userId, schoolId].every((value) => /^[1-9][0-9]*$/.test(String(value)))) {
        return null;
    }

    return `eduvia:ai-agent:conversation:${userId}:${schoolId}`;
}

export function parseConversation(raw) {
    if (raw === null) {
        return null;
    }
    const data = JSON.parse(raw);
    const validMessages = (messages, roles) => Array.isArray(messages)
        && messages.every((message) => message && roles.includes(message.role) && typeof message.text === 'string');
    if (!data || data.version !== 1
        || !validMessages(data.messages, ['user', 'assistant', 'action'])
        || !validMessages(data.history, ['user', 'assistant', 'tool'])
        || typeof data.prompt !== 'string') {
        throw new Error('Invalid conversation');
    }

    return {
        messages: data.messages.map(({ role, text, ok }) => ({ role, text, ...(role === 'action' ? { ok: ok === true } : {}) })),
        history: data.history.map(({ role, text }) => ({ role, text })),
        prompt: data.prompt,
        changed: data.changed === true,
        lastUrl: typeof data.lastUrl === 'string' && data.lastUrl.startsWith('/')
            && !data.lastUrl.startsWith('//') && !data.lastUrl.includes('\\') ? data.lastUrl : '',
        interrupted: data.interrupted === true,
    };
}

export function conversationContext(history) {
    const latestUserIndex = history.findLastIndex((message) => message.role === 'user');
    if (latestUserIndex === -1) {
        return [];
    }
    const messages = history.map(({ role, text }) => ({ role, text: text.slice(0, 24000) }));
    const indexes = [latestUserIndex];
    let length = messages[latestUserIndex].text.length;

    for (let index = messages.length - 1; index >= 0; index--) {
        if (index === latestUserIndex) {
            continue;
        }
        if (indexes.length === 60 || length + messages[index].text.length > 100000) {
            break;
        }
        indexes.push(index);
        length += messages[index].text.length;
    }

    const context = indexes.sort((first, second) => first - second).map((index) => messages[index]);
    return context.slice(context.findIndex((message) => message.role === 'user'));
}
