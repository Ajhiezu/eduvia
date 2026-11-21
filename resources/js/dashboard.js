export function dashboardCalendar() {
    return {
        displayedMonth: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
        selectedDate: new Date(),
        dateKey(date) {
            return [date.getFullYear(), date.getMonth() + 1, date.getDate()].join('-');
        },
        get monthLabel() {
            return new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(this.displayedMonth);
        },
        get selectedKey() {
            return this.dateKey(this.selectedDate);
        },
        get selectedLabel() {
            return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(this.selectedDate);
        },
        get days() {
            const year = this.displayedMonth.getFullYear();
            const month = this.displayedMonth.getMonth();
            const offset = (new Date(year, month, 1).getDay() + 6) % 7;
            const cellCount = Math.ceil((offset + new Date(year, month + 1, 0).getDate()) / 7) * 7;
            const todayKey = this.dateKey(new Date());
            return Array.from({ length: cellCount }, (_, index) => {
                const date = new Date(year, month, index - offset + 1);
                return {
                    key: this.dateKey(date), date: date.getDate(), timestamp: date.getTime(),
                    currentMonth: date.getMonth() === month, today: this.dateKey(date) === todayKey,
                    label: new Intl.DateTimeFormat('id-ID', { dateStyle: 'full' }).format(date),
                };
            });
        },
        changeMonth(offset) {
            this.displayedMonth = new Date(this.displayedMonth.getFullYear(), this.displayedMonth.getMonth() + offset, 1);
        },
        selectDate(day) {
            this.selectedDate = new Date(day.timestamp);
            this.displayedMonth = new Date(this.selectedDate.getFullYear(), this.selectedDate.getMonth(), 1);
        },
        goToday() {
            this.selectedDate = new Date();
            this.displayedMonth = new Date(this.selectedDate.getFullYear(), this.selectedDate.getMonth(), 1);
        },
    };
}

export function sidebarScroll(userId) {
    return {
        storageKey: `eduvia:sidebar-scroll:${userId}`,
        mode: 'expanded',
        positions: { expanded: 0, collapsed: null },
        restoring: false,
        restorationId: 0,
        init() {
            for (const mode of ['expanded', 'collapsed']) {
                try {
                    const key = mode === 'expanded' ? this.storageKey : `${this.storageKey}:collapsed`;
                    const storedPosition = window.sessionStorage.getItem(key);
                    const position = Number(storedPosition);

                    if (storedPosition !== null && Number.isFinite(position) && position >= 0) {
                        this.positions[mode] = position;
                    }
                } catch {
                    // Navigation remains available when browser storage is blocked.
                }
            }

            this.setCollapsed(Boolean(this.sidebarCollapsed));
            this.$watch('sidebarCollapsed', (collapsed) => this.setCollapsed(collapsed));
        },
        setCollapsed(collapsed) {
            const nextMode = collapsed ? 'collapsed' : 'expanded';

            if (this.positions[nextMode] === null) {
                this.positions[nextMode] = this.positions[this.mode];
            }

            this.mode = nextMode;
            this.restoring = true;
            const restorationId = ++this.restorationId;
            this.$nextTick(() => {
                const restore = () => {
                    if (restorationId !== this.restorationId) {
                        return;
                    }

                    this.$el.scrollTop = this.positions[this.mode];
                    this.restoring = false;
                };
                const animations = this.$el.closest?.('.app-sidebar')?.getAnimations() ?? [];

                if (animations.length === 0) {
                    restore();
                    return;
                }

                Promise.allSettled(animations.map((animation) => animation.finished)).then(restore);
            });
        },
        savePosition() {
            if (this.restoring) {
                return;
            }

            this.positions[this.mode] = this.$el.scrollTop;

            try {
                const key = this.mode === 'expanded' ? this.storageKey : `${this.storageKey}:collapsed`;
                window.sessionStorage.setItem(key, String(this.positions[this.mode]));
            } catch {
                // Navigation remains available when browser storage is blocked.
            }
        },
    };
}

export function dashboardNavigationSearch() {
    return {
        query: '',
        open: false,
        items: [],
        init() {
            this.$nextTick(() => {
                this.items = Array.from(document.querySelectorAll('.metis-nav-item'))
                    .map((link) => ({ label: link.textContent.trim(), url: link.href }))
                    .filter((item, index, all) => all.findIndex((other) => other.url === item.url) === index);
            });
        },
        get matches() {
            const query = this.query.trim().toLocaleLowerCase('id-ID');
            return query ? this.items.filter((item) => item.label.toLocaleLowerCase('id-ID').includes(query)).slice(0, 7) : [];
        },
        goFirst() {
            if (this.matches.length) {
                window.location.assign(this.matches[0].url);
            }
        },
    };
}
