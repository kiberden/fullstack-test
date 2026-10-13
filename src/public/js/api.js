(function (global, $) {
    'use strict';

    const App = global.App || {};
    const config = App.config || {};

    App.api = {
        /**
         * Обёртка jQuery.ajax: CSRF-заголовок и обновление токена.
         *
         * @param {JQueryAjaxSettings} options
         * @returns {JQuery.jqXHR}
         */
        request(options) {
            const settings = Object.assign({}, options);
            const method = String(settings.method || settings.type || 'GET').toUpperCase();
            const headers = Object.assign({}, settings.headers || {});
            const needsCsrf = settings.csrf !== false && !['GET', 'HEAD', 'OPTIONS'].includes(method);

            if (needsCsrf) {
                const headerName = config.csrfHeader || 'X-CSRF-TOKEN';
                headers[headerName] = App.csrf.header();
            }

            settings.method = method;
            settings.headers = headers;

            return $.ajax(settings)
                .done(function (response) {
                    App.csrf.refresh(response);
                })
                .fail(function (xhr) {
                    App.csrf.refresh(xhr);
                });
        },
    };

    global.App = App;
})(window, jQuery);
