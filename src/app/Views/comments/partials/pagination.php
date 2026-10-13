<?php if (($pagination['totalPages'] ?? 0) > 1): ?>
    <?php
    $sort = $sort ?? \App\Services\CommentService::DEFAULT_SORT;
    $dir  = $dir ?? \App\Services\CommentService::DEFAULT_DIR;
    ?>
    <nav aria-label="Пагинация комментариев" class="mb-4">
        <ul class="pagination justify-content-center mb-0">
            <?php foreach ($pagination['pages'] as $pageNum): ?>
                <?php if ($pageNum === null): ?>
                    <li class="page-item disabled" aria-hidden="true">
                        <span class="page-link">…</span>
                    </li>
                <?php elseif ((int) $pageNum === (int) $pagination['page']): ?>
                    <li class="page-item active" aria-current="page">
                        <span class="page-link"><?= (int) $pageNum ?></span>
                    </li>
                <?php else: ?>
                    <li class="page-item">
                        <a
                            class="page-link"
                            href="<?= esc(site_url('/?' . \App\Services\CommentService::buildListQuery((int) $pageNum, $sort, $dir))) ?>"
                        ><?= (int) $pageNum ?></a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </nav>
<?php endif; ?>
