<?php if ($comments === []): ?>
    <div class="alert alert-secondary mb-0" id="comments-empty">
        Пока нет комментариев.
    </div>
<?php else: ?>
    <?php foreach ($comments as $comment): ?>
        <?= view('comments/partials/item', ['comment' => $comment, 'user' => $user]) ?>
    <?php endforeach; ?>
<?php endif; ?>
