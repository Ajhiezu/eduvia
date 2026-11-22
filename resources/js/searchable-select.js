let nextSelectId = 0;

export function searchableOptions(select, query = '') {
    const terms = query.trim().toLocaleLowerCase('id').split(/\s+/);

    return Array.from(select.options).filter((option) => {
        const group = option.closest('optgroup');
        const text = `${group?.label ?? ''} ${option.label}`.toLocaleLowerCase('id');

        return !option.hidden && !group?.hidden && terms.every((term) => text.includes(term));
    });
}

export function shouldSearchSelect(select) {
    return select.dataset.searchable !== 'false'
        && (!select.size || select.size <= 1 || select.multiple)
        && (select.dataset.searchable === 'true'
            || searchableOptions(select).filter((option) => option.value !== '').length > 5);
}

export function initializeSearchableSelects(root = document) {
    const controls = new Map();
    let activeControl = null;

    function enhance(select) {
        const wrapper = document.createElement('div');
        wrapper.className = 'searchable-select';
        select.before(wrapper);
        wrapper.append(select);

        const button = document.createElement('button');
        button.type = 'button';
        button.id = `searchable-select-${++nextSelectId}`;
        button.setAttribute('aria-haspopup', 'listbox');
        button.setAttribute('aria-expanded', 'false');
        wrapper.append(button);

        const panel = document.createElement('div');
        panel.className = 'searchable-select-panel';
        panel.hidden = true;
        if (typeof panel.showPopover === 'function') {
            panel.setAttribute('popover', 'manual');
        }
        wrapper.append(panel);

        const search = document.createElement('input');
        search.type = 'search';
        search.className = 'searchable-select-search';
        search.placeholder = 'Ketik untuk mencari…';
        search.autocomplete = 'off';
        search.setAttribute('role', 'combobox');
        search.setAttribute('aria-autocomplete', 'list');
        search.setAttribute('aria-expanded', 'false');
        panel.append(search);

        const list = document.createElement('div');
        list.id = `${button.id}-options`;
        list.className = 'searchable-select-options';
        list.setAttribute('role', 'listbox');
        search.setAttribute('aria-controls', list.id);
        button.setAttribute('aria-controls', list.id);
        panel.append(list);

        const status = document.createElement('p');
        status.className = 'searchable-select-status';
        status.setAttribute('role', 'status');
        panel.append(status);

        let visibleOptions = [];
        let activeIndex = -1;
        let isOpen = false;
        let validationAttempted = false;
        const originalTabIndex = select.getAttribute('tabindex');
        const originalAriaHidden = select.getAttribute('aria-hidden');
        select.classList.add('searchable-select-native');
        select.tabIndex = -1;
        select.setAttribute('aria-hidden', 'true');

        function isDisabled(option) {
            return option.disabled || option.closest('optgroup')?.disabled;
        }

        function highlight(index) {
            activeIndex = index;
            Array.from(list.children).forEach((item, itemIndex) => {
                item.classList.toggle('is-active', itemIndex === index);
            });
            const item = list.children[index];
            if (item) {
                search.setAttribute('aria-activedescendant', item.id);
                item.scrollIntoView({ block: 'nearest' });
            } else {
                search.removeAttribute('aria-activedescendant');
            }
        }

        function render() {
            visibleOptions = searchableOptions(select, search.value);
            list.replaceChildren();
            for (const option of visibleOptions) {
                const item = document.createElement('div');
                item.id = `${button.id}-option-${option.index}`;
                item.className = 'searchable-select-option';
                item.setAttribute('role', 'option');
                item.setAttribute('aria-selected', String(option.selected));
                item.setAttribute('aria-disabled', String(Boolean(isDisabled(option))));
                const group = option.closest('optgroup');
                item.textContent = `${group ? `${group.label} — ` : ''}${option.label}`;
                item.addEventListener('pointerdown', (event) => event.preventDefault());
                item.addEventListener('click', () => choose(option));
                list.append(item);
            }
            status.textContent = visibleOptions.length
                ? `${visibleOptions.length} pilihan${select.multiple ? ' · Dapat memilih lebih dari satu' : ''}`
                : 'Tidak ada hasil yang cocok.';
            highlight(visibleOptions.findIndex((option) => option.selected && !isDisabled(option)));
        }

        function sync() {
            button.className = `${select.className.replace('searchable-select-native', '')} searchable-select-trigger`;
            button.disabled = select.matches(':disabled');
            button.hidden = select.hidden || select.style.display === 'none';
            const label = select.getAttribute('aria-label')
                || Array.from(select.labels ?? []).map((label) => label.textContent.trim()).join(' ')
                || select.name || 'Pilihan';
            button.setAttribute('aria-label', `${label}: ${Array.from(select.selectedOptions).map((option) => option.label).join(', ') || 'Pilih data'}`);
            search.setAttribute('aria-label', `Cari ${label}`);
            search.setAttribute('aria-required', String(select.required));
            button.setAttribute('aria-required', String(select.required));
            list.setAttribute('aria-label', label);
            list.setAttribute('aria-multiselectable', String(select.multiple));
            for (const attribute of ['aria-describedby', 'aria-invalid']) {
                const value = select.getAttribute(attribute);
                if (value !== null) {
                    button.setAttribute(attribute, value);
                } else {
                    button.removeAttribute(attribute);
                }
            }
            if (validationAttempted && !select.validity.valid) {
                button.setAttribute('aria-invalid', 'true');
            }
            button.textContent = Array.from(select.selectedOptions).map((option) => option.label).join(', ') || 'Pilih data';
            if (isOpen && (button.disabled || button.hidden)) {
                close();
            } else if (isOpen) {
                render();
            }
        }

        function position() {
            if (!isOpen) {
                return;
            }
            const rect = button.getBoundingClientRect();
            const viewportHeight = window.visualViewport?.height ?? window.innerHeight;
            const below = viewportHeight - rect.bottom - 8;
            const above = rect.top - 8;
            const opensAbove = below < 240 && above > below;
            const height = Math.min(340, Math.max(100, opensAbove ? above : below));
            panel.style.width = `${Math.min(rect.width, window.innerWidth - 16)}px`;
            panel.style.left = `${Math.max(8, Math.min(rect.left, window.innerWidth - rect.width - 8))}px`;
            panel.style.maxHeight = `${height}px`;
            panel.style.top = `${opensAbove ? Math.max(8, rect.top - panel.getBoundingClientRect().height - 4) : rect.bottom + 4}px`;
        }

        function close(restoreFocus = false) {
            if (!isOpen) {
                return;
            }
            isOpen = false;
            if (panel.hasAttribute('popover')) {
                panel.hidePopover();
            }
            panel.hidden = true;
            button.setAttribute('aria-expanded', 'false');
            search.setAttribute('aria-expanded', 'false');
            if (activeControl === control) {
                activeControl = null;
            }
            if (restoreFocus) {
                button.focus();
            }
        }

        function open() {
            sync();
            if (button.disabled || button.hidden) {
                return;
            }
            activeControl?.close();
            activeControl = control;
            isOpen = true;
            search.value = '';
            panel.hidden = false;
            if (panel.hasAttribute('popover')) {
                panel.showPopover();
            }
            button.setAttribute('aria-expanded', 'true');
            search.setAttribute('aria-expanded', 'true');
            render();
            position();
            search.focus({ preventScroll: true });
        }

        function choose(option) {
            if (select.matches(':disabled') || isDisabled(option)) {
                return;
            }
            if (select.multiple) {
                option.selected = !option.selected;
            } else {
                select.selectedIndex = option.index;
                close(true);
            }
            select.dispatchEvent(new Event('input', { bubbles: true }));
            select.dispatchEvent(new Event('change', { bubbles: true }));
            sync();
        }

        button.addEventListener('click', () => isOpen ? close(true) : open());
        button.addEventListener('keydown', (event) => {
            if (['ArrowDown', 'ArrowUp'].includes(event.key)) {
                event.preventDefault();
                open();
            }
        });
        search.addEventListener('input', () => {
            render();
            position();
        });
        search.addEventListener('keydown', (event) => {
            if (['ArrowDown', 'ArrowUp'].includes(event.key)) {
                event.preventDefault();
                const direction = event.key === 'ArrowDown' ? 1 : -1;
                let index = activeIndex < 0 && direction < 0 ? 0 : activeIndex;
                for (let step = 0; step < visibleOptions.length; step++) {
                    index = (index + direction + visibleOptions.length) % visibleOptions.length;
                    if (!isDisabled(visibleOptions[index])) {
                        highlight(index);
                        break;
                    }
                }
            } else if (event.key === 'Enter') {
                event.preventDefault();
                const option = visibleOptions[activeIndex] ?? visibleOptions.find((item) => !isDisabled(item));
                if (option) {
                    choose(option);
                }
            }
        });
        wrapper.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && isOpen) {
                event.preventDefault();
                event.stopPropagation();
                close(true);
            }
        });
        wrapper.addEventListener('focusout', () => {
            queueMicrotask(() => {
                if (!wrapper.contains(document.activeElement)) {
                    close();
                }
            });
        });
        const onChange = () => sync();
        const onFocus = () => button.focus();
        const onInvalid = (event) => {
            event.preventDefault();
            validationAttempted = true;
            button.focus();
            open();
            status.textContent = select.validationMessage;
        };
        select.addEventListener('change', onChange);
        select.addEventListener('focus', onFocus);
        select.addEventListener('invalid', onInvalid);

        const control = {
            wrapper, close, sync, position,
            destroy() {
                close();
                select.removeEventListener('change', onChange);
                select.removeEventListener('focus', onFocus);
                select.removeEventListener('invalid', onInvalid);
                select.classList.remove('searchable-select-native');
                if (originalTabIndex === null) {
                    select.removeAttribute('tabindex');
                } else {
                    select.setAttribute('tabindex', originalTabIndex);
                }
                if (originalAriaHidden === null) {
                    select.removeAttribute('aria-hidden');
                } else {
                    select.setAttribute('aria-hidden', originalAriaHidden);
                }
                if (wrapper.parentNode) {
                    wrapper.before(select);
                    wrapper.remove();
                }
                controls.delete(select);
            },
        };
        controls.set(select, control);
        sync();
    }

    function scan() {
        for (const [select, control] of controls) {
            if (!select.isConnected || !shouldSearchSelect(select)) {
                control.destroy();
            }
        }
        for (const select of root.querySelectorAll('select')) {
            if (!controls.has(select) && shouldSearchSelect(select)) {
                enhance(select);
            }
        }
    }

    scan();
    const observer = new MutationObserver((records) => {
        const changedSelects = new Set();
        let needsScan = false;
        for (const record of records) {
            const target = record.target.nodeType === 3 ? record.target.parentElement : record.target;
            const select = target.closest?.('select');
            if (select) {
                changedSelects.add(select);
                needsScan = true;
            }
            if (target.matches?.('fieldset') && record.attributeName === 'disabled') {
                target.querySelectorAll('select').forEach((element) => changedSelects.add(element));
                needsScan = true;
            }
            if (record.type === 'childList' && [...record.addedNodes, ...record.removedNodes]
                .some((node) => node.nodeType === 1 && (node.matches('select') || node.querySelector('select')))) {
                needsScan = true;
            }
        }
        if (needsScan) {
            scan();
            for (const select of changedSelects) {
                controls.get(select)?.sync();
            }
        }
    });
    observer.observe(root, {
        subtree: true, childList: true, characterData: true, attributes: true,
        attributeFilter: ['disabled', 'required', 'selected', 'label', 'hidden', 'multiple', 'size', 'data-searchable', 'aria-invalid', 'aria-describedby', 'style'],
    });
    root.addEventListener('pointerdown', (event) => {
        if (activeControl && !activeControl.wrapper.contains(event.target)) {
            activeControl.close();
        }
    });
    root.addEventListener('reset', () => {
        setTimeout(() => {
            activeControl?.close();
            controls.forEach((control) => control.sync());
        }, 0);
    });
    window.addEventListener('resize', () => activeControl?.position());
    window.visualViewport?.addEventListener('resize', () => activeControl?.position());
    window.visualViewport?.addEventListener('scroll', () => activeControl?.position());
    root.addEventListener('scroll', (event) => {
        if (activeControl && !activeControl.wrapper.contains(event.target)) {
            activeControl.position();
        }
    }, true);
}
