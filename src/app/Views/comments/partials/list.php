<section class="mb-4">
    <h2 class="h5 mb-3">Список комментариев</h2>
    <div id="comments-list">
        <?php if ($comments === []): ?>
            <div class="alert alert-secondary mb-0" id="comments-empty">
                Пока нет комментариев.
            </div>
        <?php else: ?>
            <?php foreach ($comments as $comment): ?>
                <?= $this->include('comments/partials/item', ['comment' => $comment, 'user' => $user]) ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
