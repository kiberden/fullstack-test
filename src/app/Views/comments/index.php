<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="h3 mb-4"><?= esc($title) ?></h1>

            <?= $this->include('comments/partials/auth_info') ?>

            <div id="alerts"></div>

            <?= $this->include('comments/partials/list') ?>

            <?= $this->include('comments/partials/form') ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function ($) {
    'use strict';

    var csrfHash = <?= json_encode($csrfHash) ?>;
    var currentUserId = <?= (int) $user['id'] ?>;

    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    function csrfHeader() {
        return getCookie('csrf_cookie_name') || csrfHash;
    }

    function showAlert(type, message) {
        $('#alerts').html(
            '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
            $('<div>').text(message).html() +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span></button></div>'
        );
    }

    function renderComment(comment) {
        $('#comments-empty').remove();

        var canDelete = parseInt(comment.user_id, 10) === currentUserId;
        var deleteButton = canDelete
            ? '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-comment">Удалить</button>'
            : '';

        var $card = $(
            '<div class="card mb-3 comment-item">' +
            '<div class="card-body">' +
            '<div class="d-flex justify-content-between align-items-start">' +
            '<div>' +
            '<h3 class="h6 mb-1"></h3>' +
            '<p class="mb-2 comment-text"></p>' +
            '<small class="text-muted"></small>' +
            '</div>' +
            deleteButton +
            '</div></div></div>'
        );

        $card.attr('data-id', comment.id);
        $card.find('h3').text(comment.email);
        $card.find('.comment-text').text(comment.text);
        $card.find('small').text(comment.date_create);
        $card.find('.btn-delete-comment').attr('data-id', comment.id);

        $('#comments-list').append($card);
    }

    $('#comment-form').on('submit', function (e) {
        e.preventDefault();

        $('#comment-text').removeClass('is-invalid');
        $('#comment-text-error').text('');

        $.ajax({
            url: '/comments',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': csrfHeader() },
            data: JSON.stringify({ text: $('#comment-text').val() })
        }).done(function (response) {
            if (!response.success || !response.comment) {
                return;
            }

            renderComment(response.comment);
            $('#comment-text').val('');
            showAlert('success', 'Комментарий добавлен');
        }).fail(function (xhr) {
            if (xhr.status === 401) {
                window.location.reload();
                return;
            }

            var errors = (xhr.responseJSON && xhr.responseJSON.errors) || {};
            if (errors.text) {
                $('#comment-text').addClass('is-invalid');
                $('#comment-text-error').text(errors.text);
            } else {
                showAlert('danger', 'Не удалось добавить комментарий');
            }
        });
    });

    $('#comments-list').on('click', '.btn-delete-comment', function () {
        var id = $(this).data('id');
        var $item = $(this).closest('.comment-item');

        $.ajax({
            url: '/comments/' + id,
            method: 'DELETE',
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': csrfHeader() }
        }).done(function (response) {
            if (!response.success) {
                return;
            }

            $item.remove();

            if ($('#comments-list .comment-item').length === 0) {
                $('#comments-list').html(
                    '<div class="alert alert-secondary mb-0" id="comments-empty">Пока нет комментариев.</div>'
                );
            }

            showAlert('success', 'Комментарий удалён');
        }).fail(function () {
            showAlert('danger', 'Не удалось удалить комментарий');
        });
    });
})(jQuery);
</script>
</body>
</html>
