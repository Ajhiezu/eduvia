import './../css/app.css';
import Alpine from 'alpinejs';
import { dashboardCalendar, dashboardNavigationSearch } from './dashboard';
import { childSwitcher, initializeFormFeedback, toastNotifications } from './ui';

window.Alpine = Alpine;

// Global helper for toast notifications
window.toast = function (message, type = 'info') {
    window.dispatchEvent(new CustomEvent('notify', {
        detail: { message, type }
    }));
};

Alpine.data('dashboardCalendar', dashboardCalendar);
Alpine.data('dashboardNavigationSearch', dashboardNavigationSearch);
Alpine.data('toastNotifications', toastNotifications);
Alpine.data('childSwitcher', childSwitcher);

Alpine.start();
initializeFormFeedback();
