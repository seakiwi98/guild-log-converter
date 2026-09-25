(() => {
    'use strict';

    const trimText = value => value.replace(/^\s+|\s+$/g, '');

    const setupDetails = () => {
        const buttons = document.querySelectorAll('.js-detail');
        const hiddenDetails = document.getElementById('hidden-details');
        const modalElement = document.getElementById('statsModal');
        const modalBody = document.getElementById('statsModalBody');
        const modalTitle = document.getElementById('statsModalTitle');

        if (!buttons.length || !hiddenDetails || !modalElement || !modalBody || !modalTitle || !window.bootstrap) {
            return;
        }

        const modal = new window.bootstrap.Modal(modalElement);

        const openDetail = button => {
            const target = button.getAttribute('data-detail-target');
            const detail = hiddenDetails.querySelector(`[data-detail-id="${CSS.escape(target)}"]`);
            const title = trimText(button.textContent || 'Details');

            if (!detail) {
                return;
            }

            modalTitle.textContent = title;
            modalBody.innerHTML = detail.innerHTML;
            modal.show();
        };

        buttons.forEach(button => {
            button.addEventListener('click', () => openDetail(button));
        });
    };

    const setupCopyButtons = () => {
        const buttons = document.querySelectorAll('[data-copy-target]');
        const copiedText = window.uiText?.copied || 'Copied';
        const fallbackText = window.uiText?.selectAndCopy || 'Select and copy';

        buttons.forEach(button => {
            button.addEventListener('click', async () => {
                const targetId = button.getAttribute('data-copy-target');
                const input = document.getElementById(targetId);

                if (!input) {
                    return;
                }

                try {
                    await navigator.clipboard.writeText(input.value);
                    button.textContent = copiedText;
                } catch {
                    input.focus();
                    input.select();

                    try {
                        document.execCommand('copy');
                        button.textContent = copiedText;
                    } catch {
                        button.textContent = fallbackText;
                    }
                }
            });
        });
    };

    const formatLocalDateTime = date => new Intl.DateTimeFormat(undefined, {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    }).format(date).replace(',', '');

    const setupLocalTimes = () => {
        document.querySelectorAll('[data-local-time]').forEach(element => {
            const rawValue = element.getAttribute('datetime') || element.textContent;
            const date = new Date(rawValue);

            if (Number.isNaN(date.getTime())) {
                return;
            }

            element.textContent = formatLocalDateTime(date);
            element.title = rawValue;
        });
    };

    const getRowAnchor = card => {
        const tableBody = card.querySelector('[data-expandable-table]');

        if (!tableBody) {
            return null;
        }

        const visibleLimit = Number.parseInt(tableBody.dataset.initialVisible || '10', 10);

        return tableBody.querySelector(`tr:nth-child(${visibleLimit})`);
    };

    const preserveAnchorPosition = (anchor, callback) => {
        if (!anchor) {
            callback();
            return;
        }

        const beforeTop = anchor.getBoundingClientRect().top;

        callback();

        const afterTop = anchor.getBoundingClientRect().top;
        const delta = afterTop - beforeTop;

        if (delta !== 0) {
            window.scrollBy({ top: delta, left: 0, behavior: 'instant' });
        }
    };

    const setupExpandableTables = () => {
        document.querySelectorAll('[data-expand-table-button]').forEach(button => {
            button.addEventListener('click', () => {
                const card = button.closest('.parsed-events-card');

                if (!card) {
                    return;
                }

                const rows = card.querySelectorAll('.is-extra-row');
                const anchor = getRowAnchor(card);
                const isExpanded = button.getAttribute('aria-expanded') === 'true';
                const expandLabel = button.dataset.expandLabel || 'Show all events';
                const collapseLabel = button.dataset.collapseLabel || 'Show fewer events';

                preserveAnchorPosition(anchor, () => {
                    rows.forEach(row => {
                        row.hidden = isExpanded;
                    });

                    button.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
                    button.textContent = isExpanded ? expandLabel : collapseLabel;
                });
            });
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        setupDetails();
        setupCopyButtons();
        setupLocalTimes();
        setupExpandableTables();
    });
})();