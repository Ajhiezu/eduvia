export function initializeSessionHistoryGuard(root = document, browser = window) {
    if (!root.querySelector('[data-authenticated-page]')) {
        return;
    }

    const concealPage = () => {
        root.documentElement.style.visibility = 'hidden';
        root.documentElement.inert = true;
    };

    browser.addEventListener('pagehide', (event) => {
        if (event.persisted) {
            concealPage();
        }
    });

    browser.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            concealPage();
            browser.location.reload();
        }
    });
}
