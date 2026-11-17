<section class="dashboard-calendar" x-data="dashboardCalendar" aria-label="Kalender">
    <div class="dashboard-section-heading">
        <h2 x-text="monthLabel" aria-live="polite">Kalender</h2>
        <div class="flex gap-1">
            <button type="button" @click="changeMonth(-1)" class="dashboard-icon-button" aria-label="Bulan sebelumnya">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="m14 7-5 5 5 5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <button type="button" @click="changeMonth(1)" class="dashboard-icon-button" aria-label="Bulan berikutnya">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="m10 7 5 5-5 5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </div>
    <div class="dashboard-calendar-grid" role="group" aria-label="Tanggal dalam bulan">
        @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)
            <span class="dashboard-calendar-weekday">{{ $day }}</span>
        @endforeach
        <template x-for="day in days" :key="day.key">
            <button type="button"
                @click="selectDate(day)"
                :class="{ 'is-muted': !day.currentMonth, 'is-today': day.today, 'is-selected': day.key === selectedKey }"
                :aria-label="day.label" :aria-current="day.today ? 'date' : null"
                :aria-pressed="day.key === selectedKey"
                x-text="day.date"
            ></button>
        </template>
    </div>
    <div class="dashboard-calendar-footer">
        <span x-text="selectedLabel" aria-live="polite"></span>
        <button type="button" @click="goToday()">Hari ini</button>
    </div>
    <noscript><p class="text-sm text-zinc-500">Aktifkan JavaScript untuk menggunakan kalender.</p></noscript>
</section>
