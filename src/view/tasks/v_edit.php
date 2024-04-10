
<h3 class="text-center mt-4">Редактирование задачи</h3>
<?php if(!empty($errors)): ?>
    <div class="alert alert-danger" role="alert">
        <?php foreach($errors as $error): ?>
            <p><?=$error?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST" id="formTask" class="container mt-4">
    <div class="row">
        <div class="col-12 col-md-6">
            <div class="form-group">
                <label for="status">Статус задачи:</label>
                <select class="form-control" name="status" id="status">
                    <?php foreach($status as $key => $st): ?>
                        <option
                                value="<?=$st?>"
                                <?=($st === $fields['status']) ? 'selected' : ''?>
                        >
                            <?=$st?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mt-5">
                <label for="author">Автор задачи:</label>
                <input
                        name="author"
                        type="text"
                        class="form-control"
                        id="author"
                        value="<?=htmlspecialchars($fields['author'] ?? '') ?>"
                >
            </div>
            <div class="form-group mt-4">
                <label for="email">Email:</label>
                <input name="email" type="email" class="form-control" id="email" value="<?=htmlspecialchars($fields['email'] ?? '') ?>">
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="form-group" id="content-error-container">
                <label for="content" data-cs-focusable>Текст задачи:</label>
                <textarea name="content" id="content" class="form-control"><?=sanitizeHTML($fields['content'] ?? '') ?></textarea>
            </div>
        </div>
    </div>
    <div class="row">
        <input class="btn btn-warning col-8 offset-2 col-md-3 offset-md-0 ml-md-auto mr-md-5" type="submit" value="Сохранить" name="save">
        <input class="btn btn-danger col-8 offset-2 col-md-3 mt-3 mt-md-0 mb-3 mb-md-0 offset-md-0  mr-md-auto" type="submit" value="Удалить" name="delete" onClick="return confirm('Вы действительно хотите удалить?')">
    </div>
    <input type="hidden" name="redirect" value="<?= $returnBack ?>">
</form>