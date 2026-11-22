const searchFields = 'input[name="search"], input[name="q"], input[name="keyword"], input[name="query"], input[type="search"][name]';
const isPageParameter = (name) => /(^|_)page$/.test(name) && !/(^|_)per_page$/.test(name);

export function buildSearchUrl(action, entries, currentUrl, fieldNames = []) {
    const url = new URL(action || currentUrl, currentUrl);
    const values = Array.from(entries).filter(([, value]) => typeof value === 'string');

    for (const name of new Set([...fieldNames, ...values.map(([name]) => name)])) {
        url.searchParams.delete(name);
    }
    for (const name of Array.from(url.searchParams.keys())) {
        if (isPageParameter(name)) {
            url.searchParams.delete(name);
        }
    }
    for (const [name, value] of values) {
        if (!isPageParameter(name)) {
            url.searchParams.append(name, value);
        }
    }
    url.hash = '';

    return url;
}

export function createLiveSearch({ render, report = () => {}, navigate = () => {}, fetchPage = (...args) => fetch(...args), delay = 300 }) {
    let timer = null;
    let controller = null;
    let revision = 0;

    function cancel() {
        revision++;
        clearTimeout(timer);
        controller?.abort();
        report('idle');
    }

    async function request(url, options = {}) {
        cancel();
        const requestRevision = revision;
        const requestController = new AbortController();
        controller = requestController;
        report('loading');

        try {
            const response = await fetchPage(url.toString(), {
                method: 'GET',
                credentials: 'same-origin',
                headers: { Accept: 'text/html' },
                signal: requestController.signal,
                cache: 'no-store',
            });
            if (requestRevision !== revision) {
                return false;
            }
            if (response.redirected && response.url) {
                const destination = new URL(response.url, url);
                const requested = new URL(url);
                if (destination.origin !== requested.origin || destination.pathname !== requested.pathname) {
                    navigate(destination.href);
                    report('idle');
                    return false;
                }
            }
            if (!response.ok || !response.headers.get('content-type')?.includes('text/html')) {
                throw new Error('Search response unavailable');
            }
            const html = await response.text();
            if (requestRevision !== revision) {
                return false;
            }
            render(html, url, options);
            report('idle');
            return true;
        } catch (error) {
            if (requestRevision === revision && error.name !== 'AbortError') {
                report('error');
            }
            return false;
        }
    }

    return {
        request,
        cancel,
        schedule(url, options = {}) {
            cancel();
            timer = setTimeout(() => request(url, options), delay);
        },
    };
}

export function initializeLiveSearch(root = document) {
    const main = root.querySelector('main');
    if (!main) {
        return;
    }

    const pagePath = window.location.pathname;
    let feedback = null;

    function searchForms(scope = main) {
        return Array.from(scope.querySelectorAll('form')).filter((form) => {
            const action = new URL(form.getAttribute('action') || window.location.href, window.location.href);
            return form.method.toLowerCase() === 'get'
                && form.dataset.liveSearch !== 'false'
                && action.origin === window.location.origin
                && action.pathname === pagePath
                && form.querySelector(searchFields);
        });
    }

    function enhanceForms() {
        for (const form of searchForms()) {
            form.dataset.liveSearch = 'true';
            for (const input of form.querySelectorAll(searchFields)) {
                input.setAttribute('enterkeyhint', 'search');
                if (!input.labels?.length && !input.hasAttribute('aria-label') && !input.hasAttribute('aria-labelledby')) {
                    input.setAttribute('aria-label', input.placeholder || 'Cari data');
                }
            }
            for (const control of form.elements) {
                if (/^\s*this\.form\.(?:submit|requestSubmit)\(\)\s*;?\s*$/.test(control.getAttribute('onchange') ?? '')) {
                    control.removeAttribute('onchange');
                }
            }
        }
    }

    function eligibleForm(element) {
        const form = element?.form;
        return form && main.contains(form) && searchForms().includes(form) ? form : null;
    }

    function formUrl(form, submitter = null) {
        const entries = new FormData(form);
        if (submitter?.name) {
            entries.append(submitter.name, submitter.value);
        }
        return buildSearchUrl(
            submitter?.getAttribute('formaction') || form.action,
            entries,
            window.location.href,
            Array.from(form.elements, (control) => control.name).filter(Boolean),
        );
    }

    function report(state) {
        main.setAttribute('aria-busy', String(state === 'loading'));
        if (!feedback && state !== 'idle') {
            feedback = document.createElement('div');
            feedback.className = 'live-search-feedback';
            feedback.setAttribute('role', 'status');
            feedback.setAttribute('aria-live', 'polite');
            document.body.append(feedback);
        }
        if (feedback) {
            feedback.hidden = state === 'idle';
            feedback.dataset.state = state;
            feedback.textContent = state === 'error'
                ? 'Pencarian belum berhasil. Periksa koneksi, lalu ketik kembali atau tekan Enter untuk mencoba lagi.'
                : 'Memuat hasil pencarian…';
        }
    }

    const search = createLiveSearch({
        report,
        navigate: (url) => window.location.assign(url),
        render(html, url, options) {
            const parsed = new DOMParser().parseFromString(html, 'text/html');
            const nextMain = main.id ? parsed.getElementById(main.id) : parsed.querySelector('main');
            if (!nextMain || !searchForms(nextMain).length) {
                throw new Error('Search content missing');
            }

            const active = document.activeElement;
            const form = eligibleForm(active);
            const focus = form && active.matches(searchFields) ? {
                formIndex: searchForms().indexOf(form),
                name: active.name,
                value: active.value,
                start: active.selectionStart,
                end: active.selectionEnd,
                direction: active.selectionDirection,
            } : null;
            const scroll = { left: window.scrollX, top: window.scrollY };

            // Keep unrelated forms (for example, parent-code generation) and their drafts intact.
            const currentForms = Array.from(main.querySelectorAll('form')).filter((item) => item.method.toLowerCase() !== 'get');
            const nextForms = Array.from(nextMain.querySelectorAll('form')).filter((item) => item.method.toLowerCase() !== 'get');
            for (const current of currentForms) {
                if (current.closest('table') || current.querySelector('[name="_method"]')) {
                    continue;
                }
                const replacement = nextForms.find((item) => item.action === current.action
                    && item.method === current.method && !item.closest('table'));
                if (replacement) {
                    for (const control of current.elements) {
                        if (control.name && !['hidden', 'submit', 'button', 'file'].includes(control.type)) {
                            const target = Array.from(replacement.elements).find((item) => item.name === control.name);
                            if (target) {
                                target.value = control.value;
                                if (['checkbox', 'radio'].includes(control.type)) {
                                    target.checked = control.checked;
                                }
                            }
                        }
                    }
                }
            }

            main.replaceChildren(...nextMain.childNodes);
            enhanceForms();
            if (parsed.title) {
                document.title = parsed.title;
            }
            if (options.history !== 'none') {
                const method = options.history === 'push' ? 'pushState' : 'replaceState';
                window.history[method](window.history.state, '', url.toString());
            }
            if (focus) {
                const input = Array.from(searchForms()[focus.formIndex]?.querySelectorAll(searchFields) ?? [])
                    .find((item) => item.name === focus.name);
                if (input) {
                    input.value = focus.value;
                    input.focus({ preventScroll: true });
                    if (focus.start !== null) {
                        input.setSelectionRange(focus.start, focus.end, focus.direction);
                    }
                }
            }
            window.scrollTo(scroll);
        },
    });

    root.addEventListener('input', (event) => {
        const form = eligibleForm(event.target);
        if (!form || !event.target.matches(searchFields)) {
            return;
        }
        if (event.isComposing) {
            search.cancel();
            return;
        }
        search.schedule(formUrl(form));
    });
    root.addEventListener('compositionstart', (event) => {
        if (eligibleForm(event.target) && event.target.matches(searchFields)) {
            search.cancel();
        }
    });
    root.addEventListener('compositionend', (event) => {
        const form = eligibleForm(event.target);
        if (form && event.target.matches(searchFields)) {
            search.schedule(formUrl(form));
        }
    });
    root.addEventListener('change', (event) => {
        const form = eligibleForm(event.target);
        if (form && !event.target.matches(searchFields)) {
            search.request(formUrl(form));
        }
    });
    root.addEventListener('submit', (event) => {
        const form = event.target;
        if (!searchForms().includes(form) || event.defaultPrevented
            || (event.submitter?.formTarget && event.submitter.formTarget !== '_self')) {
            return;
        }
        const url = formUrl(form, event.submitter);
        if (url.origin !== window.location.origin || url.pathname !== pagePath) {
            return;
        }
        event.preventDefault();
        search.request(url);
    });
    root.addEventListener('reset', (event) => {
        if (searchForms().includes(event.target)) {
            setTimeout(() => search.request(formUrl(event.target)), 0);
        }
    });
    root.addEventListener('click', (event) => {
        const link = event.target.closest?.('a[href]');
        if (!link || !main.contains(link) || !searchForms().length || event.defaultPrevented
            || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey
            || link.hasAttribute('download') || (link.target && link.target !== '_self')) {
            return;
        }
        let url = new URL(link.href, window.location.href);
        if (url.origin !== window.location.origin || url.pathname !== pagePath || url.hash) {
            return;
        }
        const pages = Array.from(url.searchParams).filter(([name]) => isPageParameter(name));
        if (!pages.length && !/\b(reset|hapus filter|bersihkan filter|atur ulang)\b/i.test(link.textContent)) {
            return;
        }
        if (pages.length) {
            url = formUrl(searchForms()[0]);
            for (const [name, value] of pages) {
                url.searchParams.set(name, value);
            }
        }
        event.preventDefault();
        search.request(url, { history: 'push' });
    });
    window.addEventListener('popstate', () => {
        if (window.location.pathname === pagePath && searchForms().length) {
            search.request(new URL(window.location.href), { history: 'none' });
        }
    });
    window.addEventListener('pagehide', () => search.cancel());

    enhanceForms();
    const observer = new MutationObserver((records) => {
        if (records.some((record) => Array.from(record.addedNodes)
            .some((node) => node.nodeType === 1 && (node.matches('form') || node.querySelector(searchFields))))) {
            enhanceForms();
        }
    });
    observer.observe(main, { childList: true, subtree: true });
}
