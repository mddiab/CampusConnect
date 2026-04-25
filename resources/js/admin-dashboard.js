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
