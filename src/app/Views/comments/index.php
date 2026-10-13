<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="application/json" id="app-config"><?=
    json_encode([
        'csrfHash' => $csrfHash,
        'csrfCookie' => config('Security')->cookieName,
        'csrfHeader' => config('Security')->headerName,
        'page' => (int) $pagination['page'],
        'sort' => $sort,
        'dir' => $dir,
    ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
?></script>
<script src="<?= esc(base_url('js/app.js')) ?>"></script>
<script src="<?= esc(base_url('js/csrf.js')) ?>"></script>
<script src="<?= esc(base_url('js/api.js')) ?>"></script>
<script src="<?= esc(base_url('js/dom.js')) ?>"></script>
<script src="<?= esc(base_url('js/comments.js')) ?>"></script>
<?= $this->endSection() ?>
