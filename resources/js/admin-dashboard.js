(() => {
    function ajaxFilter(formId, resultsId) {
        const form = document.getElementById(formId);
        const container = document.getElementById(resultsId);
        if (!form || !container) return;

        let debounceTimer;

        async function fetchResults() {
            const params = new URLSearchParams(new FormData(form));
            const url = form.action + '?' + params.toString();
            try {
                const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const fresh = doc.getElementById(resultsId);
                if (fresh) container.innerHTML = fresh.innerHTML;
            } catch (e) { /* network error — let user submit manually */ }
        }

        form.addEventListener('submit', (e) => { e.preventDefault(); fetchResults(); });

        form.querySelectorAll('select').forEach((el) => {
            el.addEventListener('change', () => fetchResults());
        });

        form.querySelectorAll('input[type="text"]').forEach((el) => {
            el.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetchResults(), 400);
            });
        });
    }

    ajaxFilter('admin-users-form', 'admin-users-results');
    ajaxFilter('admin-dept-form', 'admin-categories-results');
})();

// Staff filter AJAX
(() => {
    const filterForm = document.getElementById('staff-filter-form');
    const resultsContainer = document.getElementById('staff-results');

    if (filterForm && resultsContainer) {
        let debounceTimer;

        async function fetchResults() {
            const params = new URLSearchParams(new FormData(filterForm));
            const url = filterForm.action + '?' + params.toString();
            try {
                const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const fresh = doc.getElementById('staff-results');
                if (fresh) resultsContainer.innerHTML = fresh.innerHTML;
            } catch (e) { /* network error — let user submit manually */ }
        }

        filterForm.addEventListener('submit', (e) => { e.preventDefault(); fetchResults(); });

        filterForm.querySelectorAll('select').forEach((el) => {
            el.addEventListener('change', () => fetchResults());
        });

        filterForm.querySelectorAll('input[type="text"]').forEach((el) => {
            el.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetchResults(), 400);
            });
        });
    }
})();

const adminDashboardPage = document.getElementById('adminDashboardPage');
let lastFocusedElement = null;

    function syncModalState() {
        const hasActiveModal = document.querySelector('.modal.active') !== null;

        document.body.classList.toggle('modal-open', hasActiveModal);
        adminDashboardPage?.classList.toggle('modal-open', hasActiveModal);
    }

    function openModal(id) {
        const modal = document.getElementById(id);

        if (! modal) {
            return;
        }

        lastFocusedElement = document.activeElement instanceof HTMLElement
            ? document.activeElement
            : null;

        modal.classList.add('active');
        syncModalState();
        focusFirstModalControl(modal);
    }

    function closeModal(id) {
        closeActiveModal(document.getElementById(id));
    }

    window.openModal = openModal;
    window.closeModal = closeModal;

    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;

        if (! target) {
            return;
        }

        const openButton = target.closest('[data-modal-open]');

        if (openButton) {
            openModal(openButton.dataset.modalOpen);
            return;
        }

        const closeButton = target.closest('[data-modal-close]');

        if (closeButton) {
            closeModal(closeButton.dataset.modalClose);
        }
    });

    window.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;

        if (target?.classList.contains('modal')) {
            closeActiveModal(target);
        }
    });

    window.addEventListener('keydown', (event) => {
        const activeModal = document.querySelector('.modal.active');

        if (! activeModal) {
            return;
        }

        if (event.key === 'Escape') {
            closeActiveModal(activeModal);
            return;
        }

        if (event.key === 'Tab') {
            trapModalFocus(event, activeModal);
        }
    });

    function closeActiveModal(modal) {
        if (! modal) {
            return;
        }

        modal.classList.remove('active');
        syncModalState();

        if (lastFocusedElement?.isConnected) {
            lastFocusedElement.focus();
        }

        lastFocusedElement = null;
    }

    function focusFirstModalControl(modal) {
        window.requestAnimationFrame(() => {
            const focusable = modalFocusableElements(modal);

            if (focusable.length > 0) {
                focusable[0].focus();
                return;
            }

            modal.setAttribute('tabindex', '-1');
            modal.focus();
        });
    }

    function trapModalFocus(event, modal) {
        const focusable = modalFocusableElements(modal);

        if (focusable.length === 0) {
            event.preventDefault();
            modal.focus();
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
            return;
        }

        if (! event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    function modalFocusableElements(modal) {
        return Array.from(modal.querySelectorAll(
            'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])',
        )).filter((element) => element.offsetParent !== null);
    }
