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

    setupDetails();
    setupCopyButtons();
})();