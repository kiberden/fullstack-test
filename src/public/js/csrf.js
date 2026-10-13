(function (global) {
    'use strict';

    const App = global.App || {};
    const config = App.config || {};

    function getCookie(name) {
        const escaped = name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1');
        const match = document.cookie.match(new RegExp('(?:^|; )' + escaped + '=([^;]*)'));

        return match ? decodeURIComponent(match[1]) : null;
    }

    let csrfHash = typeof config.csrfHash === 'string' ? config.csrfHash : '';

    App.csrf = {
        getCookie,

        header() {
            const cookieName = config.csrfCookie || 'csrf_cookie_name';

            return getCookie(cookieName) || csrfHash;
        },

        refresh(xhrOrPayload) {
            let payload = xhrOrPayload;

            if (xhrOrPayload && xhrOrPayload.responseJSON) {
                payload = xhrOrPayload.responseJSON;
            }

            if (payload && typeof payload.csrfHash === 'string' && payload.csrfHash !== '') {
                csrfHash = payload.csrfHash;
                return;
            }

            const cookieName = config.csrfCookie || 'csrf_cookie_name';
            const fromCookie = getCookie(cookieName);

            if (fromCookie) {
                csrfHash = fromCookie;
            }
        },
    };

    global.App = App;
})(window);
