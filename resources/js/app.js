import './../css/app.css';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Global helper for toast notifications
window.toast = function (message, type = 'info') {
    window.dispatchEvent(new CustomEvent('notify', {
        detail: { message, type }
    }));
};

Alpine.start();
