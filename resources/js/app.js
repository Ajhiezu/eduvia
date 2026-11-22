import './../css/app.css';
import Alpine from 'alpinejs';
import { dashboardCalendar, dashboardNavigationSearch, sidebarScroll } from './dashboard';
import { childSwitcher, initializeFormFeedback, toastNotifications } from './ui';
import { aiAgentSettingsForm } from './ai-agent';
import { aiAgentChat } from './ai-agent-actions';
import { initializeSearchableSelects } from './searchable-select';
import { initializeLiveSearch } from './live-search';
import { initializeSessionHistoryGuard } from './session-history';

window.Alpine = Alpine;

// Global helper for toast notifications
window.toast = function (message, type = 'info') {
    window.dispatchEvent(new CustomEvent('notify', {
        detail: { message, type }
    }));
};

Alpine.data('dashboardCalendar', dashboardCalendar);
Alpine.data('dashboardNavigationSearch', dashboardNavigationSearch);
Alpine.data('sidebarScroll', sidebarScroll);
Alpine.data('toastNotifications', toastNotifications);
Alpine.data('childSwitcher', childSwitcher);
Alpine.data('aiAgentChat', aiAgentChat);
Alpine.data('aiAgentSettingsForm', aiAgentSettingsForm);

initializeSessionHistoryGuard();
Alpine.start();
initializeFormFeedback();
initializeSearchableSelects();
initializeLiveSearch();
