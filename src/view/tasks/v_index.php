<div class="tasks">
    <?php if (!empty($items)): ?>
        <?php if (!is_null($sorting)): ?>
            <div class="row sort-row mb-5">
                <div class="col-2 offset-2">
                    <?= generateSortLink($navParams, 'author', 'Имя', $sorting['field'], $sorting['order']) ?>
                </div>
                <div class="col-3">
                    <?= generateSortLink($navParams, 'email', 'E-mail', $sorting['field'], $sorting['order']) ?>
                </div>
                <div class="col-3">
                    <?= generateSortLink($navParams, 'status', 'Статус', $sorting['field'], $sorting['order']) ?>
                </div>
                <div class="col-2">
                    Действия
                </div>
            </div>
        <?php endif; ?>
        <?php foreach ($items as $item): ?>
            <div class="row first-row">
                <div class="col-2">
                    <a href="task/<?= $item['id']?>">
                        <img src="<?= IMG_DIR . (is_null($item['img']) ? 'no-image.png' : $item['img']) ?>" alt="" class="img-thumbnail">
                    </a>
                </div>
                <div class="col-2">
                    <?=htmlspecialchars($item['author'])?>
                </div>
                <div class="col-3">
                    <?=htmlspecialchars($item['email'])?>
                </div>
                <div class="col-3">
                    <?= $item['status']?>
                </div>
                <div class="col-2">
                    <a class="btn btn-secondary btn-sm btn-block" href="task/<?= $item['id']?>">Просмотреть</a>
                    <?php if ($user !== null) :?>
                        <a class="btn btn-primary btn-sm btn-block mt-2" href="edit/<?= $item['id']?>">Изменить</a>
                    <?php endif ?>
                </div>
            </div>
        <?php endforeach ?>
    <?php else: ?>
        <div class="text-center">
            <h3>Список пуст. Создайте первую задачу!</h3>
        </div>
    <?php endif; ?>
</div>

<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <?= $navbar ?>
    </ul>
</nav>



