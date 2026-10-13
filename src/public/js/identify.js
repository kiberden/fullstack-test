(function (global, $) {
    'use strict';

    const App = global.App;
    const alertEl = document.getElementById('identify-alert');

    $('#identify-modal').modal({
        backdrop: 'static',
        keyboard: false,
        show: true,
    });

    $('#identify-form').on('submit', function (e) {
        e.preventDefault();

        const $email = $('#identify-email');
        $email.removeClass('is-invalid');
        $('#identify-email-error').text('');

        if (alertEl) {
            alertEl.replaceChildren();
        }

        App.api.request({
            url: '/auth/identify',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ email: $email.val() }),
        }).done(function (response) {
            if (!response.success) {
                return;
            }

            window.location.reload();
        }).fail(function (xhr) {
            // После POST CI регенерирует CSRF; при 403 страница со старым hash — перезагрузка.
            if (xhr.status === 403) {
                window.location.reload();
                return;
            }

            const errors = (xhr.responseJSON && xhr.responseJSON.errors) || {};

            if (errors.email) {
                $email.addClass('is-invalid');
                $('#identify-email-error').text(errors.email);
                return;
            }

            App.dom.showAlert(alertEl, 'danger', 'Не удалось сохранить email');
        });
    });
})(window, jQuery);
