<section class="mb-4" id="comments-sort">
    <form method="get" action="<?= esc(site_url('/')) ?>" class="form-row align-items-end" id="comments-sort-form">
        <div class="form-group col-md-5 mb-2 mb-md-0">
            <label for="comments-sort-field">Сортировка</label>
            <select class="form-control" id="comments-sort-field" name="sort">
                <option value="id" <?= $sort === 'id' ? 'selected' : '' ?>>ID</option>
                <option value="date_create" <?= $sort === 'date_create' ? 'selected' : '' ?>>Дата добавления</option>
            </select>
        </div>
        <div class="form-group col-md-5 mb-2 mb-md-0">
            <label for="comments-sort-dir">Направление</label>
            <select class="form-control" id="comments-sort-dir" name="dir">
                <option value="asc" <?= $dir === 'asc' ? 'selected' : '' ?>>По возрастанию</option>
                <option value="desc" <?= $dir === 'desc' ? 'selected' : '' ?>>По убыванию</option>
            </select>
        </div>
        <input type="hidden" name="page" value="1">
    </form>
</section>
