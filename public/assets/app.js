(function () {
    'use strict';

    function setupDetails() {
        var buttons = document.querySelectorAll('.js-detail');
        var hiddenDetails = document.getElementById('hidden-details');
        var modalElement = document.getElementById('statsModal');
        var modalBody = document.getElementById('statsModalBody');
        var modalTitle = document.getElementById('statsModalTitle');

        if (!buttons.length || !hiddenDetails || !modalElement || !modalBody || !modalTitle || !window.bootstrap) {
            return;
        }

        var modal = new window.bootstrap.Modal(modalElement);

        function trimText(value) {
            return value.replace(/^\s+|\s+$/g, '');
        }

        function openDetail(button) {
            var target = button.getAttribute('data-detail-target');
            var detail = hiddenDetails.querySelector('[data-detail-id="' + target + '"]');
            var title = trimText(button.textContent || 'Details');

            if (!detail) {
                return;
            }

            modalTitle.textContent = title;
            modalBody.innerHTML = detail.innerHTML;
            modal.show();
        }

        for (var i = 0; i < buttons.length; i++) {
            buttons[i].onclick = function () {
                openDetail(this);
            };
        }
    }

    function setupCopyButtons() {
        var buttons = document.querySelectorAll('[data-copy-target]');
        var copiedText = window.uiText && window.uiText.copied ? window.uiText.copied : 'Copied!';
        var fallbackText = window.uiText && window.uiText.selectAndCopy ? window.uiText.selectAndCopy : 'Select and copy';

        for (var i = 0; i < buttons.length; i++) {
            buttons[i].onclick = function () {
                var targetId = this.getAttribute('data-copy-target');
                var input = document.getElementById(targetId);

                if (!input) {
                    return;
                }

                input.focus();
                input.select();

                try {
                    document.execCommand('copy');
                    this.textContent = copiedText;
                } catch (error) {
                    this.textContent = fallbackText;
                }
            };
        }
    }

    function padNumber(value) {
        return value < 10 ? '0' + value : String(value);
    }

    function formatLocalDateTime(date) {
        return [
            padNumber(date.getDate()),
            padNumber(date.getMonth() + 1),
            date.getFullYear()
        ].join('.') + ' ' + [
            padNumber(date.getHours()),
            padNumber(date.getMinutes()),
            padNumber(date.getSeconds())
        ].join(':');
    }

    function setupLocalTimes() {
        var elements = document.querySelectorAll('[data-local-time]');

        for (var i = 0; i < elements.length; i++) {
            var rawValue = elements[i].getAttribute('datetime') || elements[i].textContent;
            var date = new Date(rawValue);

            if (isNaN(date.getTime())) {
                continue;
            }

            elements[i].textContent = formatLocalDateTime(date);
            elements[i].setAttribute('title', rawValue);
        }
    }

    function getRowAnchor(card) {
        var tableBody = card.querySelector('[data-expandable-table]');

        if (!tableBody) {
            return null;
        }

        var visibleLimit = parseInt(tableBody.getAttribute('data-initial-visible'), 10);

        if (!visibleLimit || visibleLimit < 1) {
            visibleLimit = 10;
        }

        return tableBody.querySelector('tr:nth-child(' + visibleLimit + ')');
    }

    function preserveAnchorPosition(anchor, callback) {
        if (!anchor) {
            callback();
            return;
        }

        var beforeTop = anchor.getBoundingClientRect().top;

        callback();

        var afterTop = anchor.getBoundingClientRect().top;
        var delta = afterTop - beforeTop;

        if (delta !== 0) {
            window.scrollBy(0, delta);
        }
    }

    function setupExpandableTables() {
        var buttons = document.querySelectorAll('[data-expand-table-button]');

        for (var i = 0; i < buttons.length; i++) {
            buttons[i].onclick = function () {
                var button = this;
                var card = button.closest('.parsed-events-card');

                if (!card) {
                    return;
                }

                var rows = card.querySelectorAll('.is-extra-row');
                var anchor = getRowAnchor(card);
                var isExpanded = button.getAttribute('aria-expanded') === 'true';
                var expandLabel = button.getAttribute('data-expand-label') || 'Show all events';
                var collapseLabel = button.getAttribute('data-collapse-label') || 'Show fewer events';

                preserveAnchorPosition(anchor, function () {
                    for (var rowIndex = 0; rowIndex < rows.length; rowIndex++) {
                        rows[rowIndex].hidden = isExpanded;
                    }

                    button.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
                    button.textContent = isExpanded ? expandLabel : collapseLabel;
                });
            };
        }
    }

    setupDetails();
    setupCopyButtons();
    setupLocalTimes();
    setupExpandableTables();
})();