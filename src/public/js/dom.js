(function (global) {
    'use strict';

    const App = global.App || {};

    function createCloseButton() {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'close';
        button.setAttribute('data-dismiss', 'alert');
        button.setAttribute('aria-label', 'Close');

        const icon = document.createElement('span');
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = '\u00d7';
        button.appendChild(icon);

        return button;
    }

    App.dom = {
        setHtml(element, html) {
            if (!element) {
                return;
            }

            element.innerHTML = html == null ? '' : String(html);
        },

        showAlert(container, type, message) {
            if (!container) {
                return;
            }

            container.replaceChildren();

            const alert = document.createElement('div');
            alert.className = 'alert alert-' + type + ' alert-dismissible fade show';
            alert.setAttribute('role', 'alert');
            alert.appendChild(document.createTextNode(message == null ? '' : String(message)));
            alert.appendChild(createCloseButton());

            container.appendChild(alert);
        },
    };

    global.App = App;
})(window);
