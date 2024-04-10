<h3 class="text-center mt-4">Создание новой задачи</h3>
<?php if(!empty($errors)): ?>
    <div class="alert alert-danger" role="alert">
        <?php foreach($errors as $error): ?>
            <p><?=$error?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="formTask" class="container">
    <div class="row">
        <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="author">Автор задачи:</label>
                    <input
                            name="author"
                            type="text"
                            class="form-control"
                            id="author"
                            value="<?=htmlspecialchars($fields['author'] ?? '') ?>"
                    >
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input name="email" class="form-control" id="email" value="<?=htmlspecialchars($fields['email'] ?? '') ?>">
                </div>
                <div class="form-group" id="content-error-container">
                    <label for="content" data-cs-focusable>Текст задачи:</label>
                    <textarea rows="8" name="content" id="content" class="form-control"><?=sanitizeHTML($fields['content'] ?? '')?></textarea>
                </div>
            </div>
        <div class="col-12 col-md-6">
            <label for="file" class="custom-file-label col-8 ml-auto mr-auto is-invalid">Изображение:</label>
            <div class="upload-img mr-auto ml-auto" id="image"></div>
            <input type="file" name="file" id="file">
            <div class="help-block col-12 mt-3">Допустимый формат jpg, png, gif. Допустимый размер - не более 320х240 пикселей, размер файла не больше 5 Мб.</div>
        </div>
    </div>
    <div class="row justify-content-md-center mt-3 mb-3 mt-md-0">
        <button id="buttonPreview" type="button" data-toggle="modal" data-target="#exampleModalCenter" class="btn btn-secondary mr-md-3 col-8 col-md-4 offset-2 offset-md-0 mb-2 mb-md-0">
            Предпросмотр
        </button>
        <button class="btn btn-warning col-8 col-md-4 col-md-4 offset-2 offset-md-0" type="submit">Сохранить</button>
    </div>
</form>