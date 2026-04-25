const storageKey = 'campusconnect:preserve-scroll';
const maxAgeMilliseconds = 15000;

restoreScrollPosition();

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (! (form instanceof HTMLFormElement) || ! form.hasAttribute('data-preserve-scroll')) {
        return;
    }

    persistScrollPosition(form.action || window.location.href);
});

document.addEventListener('click', (event) => {
    const target = event.target instanceof Element ? event.target : null;
    const link = target?.closest('a[data-preserve-scroll]');

    if (! link) {
        return;
    }

    persistScrollPosition(link.href);
});

document.addEventListener('change', (event) => {
    const target = event.target;

    if (! (target instanceof HTMLSelectElement) || ! target.hasAttribute('data-auto-submit')) {
        return;
    }

    const form = target.form;

    if (! form) {
        return;
    }

    persistScrollPosition(form.action || window.location.href);
    form.requestSubmit();
});

function persistScrollPosition(url) {
    const targetUrl = new URL(url, window.location.href);

    sessionStorage.setItem(storageKey, JSON.stringify({
        path: targetUrl.pathname,
        y: window.scrollY,
        createdAt: Date.now(),
    }));
}

function restoreScrollPosition() {
    const saved = readSavedPosition();

    if (! saved) {
        return;
    }

    sessionStorage.removeItem(storageKey);

    if (saved.path !== window.location.pathname || Date.now() - saved.createdAt > maxAgeMilliseconds) {
        return;
    }

    window.requestAnimationFrame(() => {
        window.requestAnimationFrame(() => {
            window.scrollTo({
                top: saved.y,
                left: 0,
                behavior: 'auto',
            });
        });
    });
}

function readSavedPosition() {
    try {
        const raw = sessionStorage.getItem(storageKey);

        if (! raw) {
            return null;
        }

        const parsed = JSON.parse(raw);

        if (typeof parsed?.path !== 'string'
            || typeof parsed?.y !== 'number'
            || typeof parsed?.createdAt !== 'number') {
            return null;
        }

        return parsed;
    } catch (error) {
        return null;
    }
}
