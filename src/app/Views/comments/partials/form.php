<section class="card">
    <div class="card-body">
        <h2 class="h5 card-title">Добавить комментарий</h2>
        <form id="comment-form">
            <div class="form-group">
                <label for="comment-text">Текст</label>
                <textarea
                    class="form-control"
                    id="comment-text"
                    name="text"
                    rows="3"
                    required
                ></textarea>
                <div class="invalid-feedback" id="comment-text-error"></div>
            </div>
            <button type="submit" class="btn btn-success">Отправить</button>
        </form>
    </div>
</section>
