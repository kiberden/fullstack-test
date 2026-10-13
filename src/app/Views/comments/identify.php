<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="application/json" id="app-config"><?=
    json_encode([
        'csrfHash' => $csrfHash,
        'csrfCookie' => config('Security')->cookieName,
        'csrfHeader' => config('Security')->headerName,
    ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
?></script>
<script src="<?= esc(base_url('js/app.js')) ?>"></script>
<script src="<?= esc(base_url('js/csrf.js')) ?>"></script>
<script src="<?= esc(base_url('js/api.js')) ?>"></script>
<script src="<?= esc(base_url('js/dom.js')) ?>"></script>
<script src="<?= esc(base_url('js/identify.js')) ?>"></script>
<?= $this->endSection() ?>
