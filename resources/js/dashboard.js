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
