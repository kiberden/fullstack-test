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

            <?= $this->include('comments/partials/sort') ?>

            <?= $this->include('comments/partials/list') ?>

            <div id="comments-pagination">
                <?= $this->include('comments/partials/pagination') ?>
            </div>

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
    var currentPage = <?= (int) $pagination['page'] ?>;
    var currentSort = <?= json_encode($sort) ?>;
    var currentDir = <?= json_encode($dir) ?>;

    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    function csrfHeader() {
        return getCookie('csrf_cookie_name') || csrfHash;
    }

    function refreshCsrf(xhrOrPayload) {
        var payload = xhrOrPayload;

        if (xhrOrPayload && xhrOrPayload.responseJSON) {
            payload = xhrOrPayload.responseJSON;
        }

        if (payload && typeof payload.csrfHash === 'string' && payload.csrfHash !== '') {
            csrfHash = payload.csrfHash;
        } else {
            var fromCookie = getCookie('csrf_cookie_name');
            if (fromCookie) {
                csrfHash = fromCookie;
            }
        }
    }

    function showAlert(type, message) {
        $('#alerts').html(
            '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
            $('<div>').text(message).html() +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span></button></div>'
        );
    }

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function buildListQuery(page, sort, dir) {
        return 'page=' + encodeURIComponent(page) +
            '&sort=' + encodeURIComponent(sort) +
            '&dir=' + encodeURIComponent(dir);
    }

    function listUrl(page, sort, dir) {
        return '/?' + buildListQuery(page, sort, dir);
    }

    function syncUrl(page, sort, dir) {
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, '', listUrl(page, sort, dir));
        }
    }

    function renderComment(comment) {
        var canDelete = parseInt(comment.user_id, 10) === currentUserId;
        var deleteButton = canDelete
            ? '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-comment" data-id="' +
              escapeHtml(comment.id) + '">Удалить</button>'
            : '';

        return (
            '<div class="card mb-3 comment-item" data-id="' + escapeHtml(comment.id) + '">' +
            '<div class="card-body">' +
            '<div class="d-flex justify-content-between align-items-start">' +
            '<div>' +
            '<h3 class="h6 mb-1">' + escapeHtml(comment.email) + '</h3>' +
            '<p class="mb-2 comment-text">' + escapeHtml(comment.text) + '</p>' +
            '<small class="text-muted">' + escapeHtml(comment.date_create) + '</small>' +
            '</div>' +
            deleteButton +
            '</div></div></div>'
        );
    }

    function renderComments(comments) {
        var $list = $('#comments-list');

        if (!comments || comments.length === 0) {
            $list.html(
                '<div class="alert alert-secondary mb-0" id="comments-empty">Пока нет комментариев.</div>'
            );
            return;
        }

        $list.html(comments.map(renderComment).join(''));
    }

    function renderPagination(pagination) {
        var $wrap = $('#comments-pagination');

        if (!pagination || pagination.totalPages <= 1) {
            $wrap.empty();
            return;
        }

        var items = (pagination.pages || []).map(function (pageNum) {
            if (pageNum === null) {
                return (
                    '<li class="page-item disabled" aria-hidden="true">' +
                    '<span class="page-link">…</span></li>'
                );
            }

            if (parseInt(pageNum, 10) === parseInt(pagination.page, 10)) {
                return (
                    '<li class="page-item active" aria-current="page">' +
                    '<span class="page-link">' + pageNum + '</span></li>'
                );
            }

            return (
                '<li class="page-item">' +
                '<a class="page-link" href="' + listUrl(pageNum, currentSort, currentDir) + '">' +
                pageNum + '</a></li>'
            );
        }).join('');

        $wrap.html(
            '<nav aria-label="Пагинация комментариев" class="mb-4">' +
            '<ul class="pagination justify-content-center mb-0">' + items + '</ul></nav>'
        );
    }

    function reloadComments(page) {
        return $.ajax({
            url: '/comments',
            method: 'GET',
            dataType: 'json',
            data: {
                page: page,
                sort: currentSort,
                dir: currentDir
            }
        }).done(function (response) {
            if (!response.success) {
                return;
            }

            currentPage = (response.pagination && response.pagination.page) || page;

            if (response.sorting) {
                currentSort = response.sorting.sort || currentSort;
                currentDir = response.sorting.dir || currentDir;
            }

            renderComments(response.comments || []);
            renderPagination(response.pagination);
            syncUrl(currentPage, currentSort, currentDir);
        }).fail(function () {
            showAlert('danger', 'Не удалось обновить список комментариев');
        });
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
            refreshCsrf(response);

            if (!response.success || !response.comment) {
                return;
            }

            $('#comment-text').val('');
            showAlert('success', 'Комментарий добавлен');
            reloadComments(1);
        }).fail(function (xhr) {
            refreshCsrf(xhr);

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

        $.ajax({
            url: '/comments/' + id,
            method: 'DELETE',
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': csrfHeader() }
        }).done(function (response) {
            refreshCsrf(response);

            if (!response.success) {
                return;
            }

            showAlert('success', 'Комментарий удалён');
            reloadComments(currentPage);
        }).fail(function (xhr) {
            refreshCsrf(xhr);
            showAlert('danger', 'Не удалось удалить комментарий');
        });
    });
})(jQuery);
</script>
</body>
</html>
