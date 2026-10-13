<?php
$currentUserId = (int) ($user['id'] ?? 0);
?>
<div class="card mb-3 comment-item" data-id="<?= (int) $comment['id'] ?>">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h3 class="h6 mb-1"><?= esc($comment['email']) ?></h3>
                <p class="mb-2 comment-text"><?= esc($comment['text']) ?></p>
                <small class="text-muted">
                    <?= esc($comment['date_create']) ?>
                </small>
            </div>
            <?php if ($currentUserId > 0 && (int) $comment['user_id'] === $currentUserId): ?>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger btn-delete-comment"
                    data-id="<?= (int) $comment['id'] ?>"
                >
                    Удалить
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
