<?php if (!empty($item)): ?>
    <section class="tasks">
        <article class="task">
            <div class="container mt-5">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <p>
                                    <strong>Имя:</strong> <?=htmlspecialchars($item['author'] ?? '') ?>
                                </p>
                                <p>
                                    <strong>Email:</strong> <?=htmlspecialchars($item['email'] ?? '') ?>
                                </p>
                                <p>
                                    <strong>Статус:</strong> <?= $item['status']?>
                                </p>
                            </div>
                            <div class="card-body text-secondary row flex-column flex-md-row">
                                <div class="img-block col-md-6 col-lg-5 d-flex justify-content-center justify-content-center">
                                    <?php if (isset($item['img'])): ?>
                                        <img class="img_prev img-fluid" src="<?=IMG_DIR_PREV . $item['img']?>" alt="">
                                    <?php else: ?>
                                        <img class="img_prev img-fluid" src="<?=IMG_DIR . 'no-image.png'?>" alt="">
                                    <?php endif; ?>
                                </div>
                                <div class="card-text col-md-6 col-lg-7">
                                    <?=sanitizeHTML($item['content'])?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    </section>
<?php else: ?>
	<h3>Форма пуста</h3>
<?php endif; ?>


