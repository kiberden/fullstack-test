(function (global, $) {
    'use strict';

    const App = global.App;
    const config = App.config || {};

    let currentPage = parseInt(config.page, 10) || 1;
    let currentSort = config.sort || 'id';
    let currentDir = config.dir || 'desc';

    const alertsEl = document.getElementById('alerts');
    const $list = $('#comments-list');
    const $pagination = $('#comments-pagination');
    const $sortForm = $('#comments-sort-form');

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

    function applyHtml(html) {
        if (!html) {
            return;
        }

        App.dom.setHtml($list.get(0), html.list);
        App.dom.setHtml($pagination.get(0), html.pagination);
    }

    function reloadComments(page) {
        return App.api.request({
            url: '/comments',
            method: 'GET',
            dataType: 'json',
            data: {
                page: page,
                sort: currentSort,
                dir: currentDir,
            },
        }).done(function (response) {
            if (!response.success) {
                return;
            }

            currentPage = (response.pagination && response.pagination.page) || page;

            if (response.sorting) {
                currentSort = response.sorting.sort || currentSort;
                currentDir = response.sorting.dir || currentDir;
            }

            applyHtml(response.html);
            syncUrl(currentPage, currentSort, currentDir);
        }).fail(function () {
            App.dom.showAlert(alertsEl, 'danger', 'Не удалось обновить список комментариев');
        });
    }

    $('#comment-form').on('submit', function (e) {
        e.preventDefault();

        const $text = $('#comment-text');
        $text.removeClass('is-invalid');
        $('#comment-text-error').text('');

        App.api.request({
            url: '/comments',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ text: $text.val() }),
        }).done(function (response) {
            if (!response.success || !response.comment) {
                return;
            }

            $text.val('');
            App.dom.showAlert(alertsEl, 'success', 'Комментарий добавлен');
            reloadComments(1);
        }).fail(function (xhr) {
            if (xhr.status === 401) {
                window.location.reload();
                return;
            }

            const errors = (xhr.responseJSON && xhr.responseJSON.errors) || {};

            if (errors.text) {
                $text.addClass('is-invalid');
                $('#comment-text-error').text(errors.text);
                return;
            }

            App.dom.showAlert(alertsEl, 'danger', 'Не удалось добавить комментарий');
        });
    });

    $list.on('click', '.btn-delete-comment', function () {
        const id = $(this).data('id');

        App.api.request({
            url: '/comments/' + id,
            method: 'DELETE',
            dataType: 'json',
        }).done(function (response) {
            if (!response.success) {
                return;
            }

            App.dom.showAlert(alertsEl, 'success', 'Комментарий удалён');
            reloadComments(currentPage);
        }).fail(function (xhr) {
            if (xhr.status === 401) {
                window.location.reload();
                return;
            }

            if (xhr.status === 403) {
                const errors = (xhr.responseJSON && xhr.responseJSON.errors) || {};
                App.dom.showAlert(
                    alertsEl,
                    'danger',
                    errors.forbidden || 'Можно удалить только свой комментарий'
                );
                return;
            }

            App.dom.showAlert(alertsEl, 'danger', 'Не удалось удалить комментарий');
        });
    });

    $pagination.on('click', 'a.page-link', function (e) {
        e.preventDefault();

        const href = $(this).attr('href') || '';
        const params = new URLSearchParams(href.split('?')[1] || '');
        const page = parseInt(params.get('page'), 10) || 1;

        reloadComments(page);
    });

    $sortForm.on('change', 'select', function () {
        currentSort = String($('#comments-sort-field').val() || currentSort);
        currentDir = String($('#comments-sort-dir').val() || currentDir);
        reloadComments(1);
    });

    $sortForm.on('submit', function (e) {
        e.preventDefault();
        currentSort = String($('#comments-sort-field').val() || currentSort);
        currentDir = String($('#comments-sort-dir').val() || currentDir);
        reloadComments(1);
    });
})(window, jQuery);
