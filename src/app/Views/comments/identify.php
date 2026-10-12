<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div
    class="modal fade"
    id="identify-modal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="identify-modal-title"
    aria-modal="true"
>
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="identify-modal-title">Представьтесь</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    Укажите email, чтобы перейти к списку комментариев.
                    Без email продолжить нельзя.
                </p>
                <form id="identify-form">
                    <div class="form-group mb-0">
                        <label for="identify-email">Email</label>
                        <input
                            type="email"
                            class="form-control"
                            id="identify-email"
                            name="email"
                            required
                            autocomplete="email"
                            placeholder="name@example.com"
                        >
                        <div class="invalid-feedback" id="identify-email-error"></div>
                    </div>
                </form>
                <div id="identify-alert" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="submit" form="identify-form" class="btn btn-primary btn-block">
                    Продолжить
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function ($) {
    'use strict';

    var csrfHash = <?= json_encode($csrfHash) ?>;

    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    function csrfHeader() {
        return getCookie('csrf_cookie_name') || csrfHash;
    }

    $('#identify-modal').modal({
        backdrop: 'static',
        keyboard: false,
        show: true
    });

    $('#identify-form').on('submit', function (e) {
        e.preventDefault();

        $('#identify-email').removeClass('is-invalid');
        $('#identify-email-error').text('');
        $('#identify-alert').empty();

        $.ajax({
            url: '/auth/identify',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': csrfHeader() },
            data: JSON.stringify({ email: $('#identify-email').val() })
        }).done(function (response) {
            if (!response.success) {
                return;
            }

            window.location.reload();
        }).fail(function (xhr) {
            // После любого POST CI регенерирует CSRF-токен; при ошибке страница
            // остаётся со старым hash — повторный submit даст 403. Обновляем страницу.
            if (xhr.status === 403) {
                window.location.reload();
                return;
            }

            var errors = (xhr.responseJSON && xhr.responseJSON.errors) || {};

            if (errors.email) {
                $('#identify-email').addClass('is-invalid');
                $('#identify-email-error').text(errors.email);
                return;
            }

            $('#identify-alert').html(
                '<div class="alert alert-danger mb-0">Не удалось сохранить email</div>'
            );
        });
    });
})(jQuery);
</script>
</body>
</html>
