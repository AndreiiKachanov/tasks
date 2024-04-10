<div class="tasks card">
    <div class="card-header">
        <p><span>Имя:</span> <?= htmlspecialchars($item['author'] ?? '')?></p>
        <p><span>Email:</span> <?= htmlspecialchars($item['email'] ?? '')?></p>
        <p><span>Статус:</span> <?= $item['status']?></p>
    </div>
    <div class="card-body text-secondary row flex-column flex-md-row">
        <div class="img-block col-md-6 col-lg-5 d-flex justify-content-center justify-content-center">
            <img
                class="img-thumbnail"
                src="<?= IMG_DIR . (is_null($item['img']) ? 'no-image.png' : $item['img']) ?>"
                alt="<?=htmlspecialchars($item['author'] ?? '')?>"
            >
        </div>
        <div class="card-text col-md-6 col-lg-7">
            <?= sanitizeHTML($item['content']) ?>
        </div>
    </div>
</div>
