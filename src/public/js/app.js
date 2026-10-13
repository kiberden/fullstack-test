(function (global) {
    'use strict';

    const App = global.App || {};
    const configEl = document.getElementById('app-config');

    App.config = configEl ? JSON.parse(configEl.textContent) : {};

    global.App = App;
})(window);
