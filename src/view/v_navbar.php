<!--
	Шаблон вывода пагинации
	
	$count - общее количество записей
	$on_page - количество записей на странице
	$page_num - номер текущей страницы
	$url_self - url адрес от корня без номера с страницы. Например, /pages/all или /articles/editor/
-->
<?php $sorting = is_null($sorting) ? '' : '?sort=' . $sorting['field'] . '&order=' . $sorting['order']; ?>
<?php extract($params); ?>

<?php if($max_page > 1):?>
    <ul class="pagination justify-content-center">

        <?php if($page_num <= 1): ?>
            <li class='page-item disabled'><span class='page-link'>Начало</span></li>
            <li class='page-item disabled'><span class='page-link'>Пред.</span></li>
        <?php else: ?>
            <li class='page-item'><a class='page-link' href="<?= $url_self . $sorting ?>">Начало</a></li>
            <li class='page-item'><a class='page-link' href="<?= $url_self . ($page_num - 1) . $sorting ?>">Пред.</a></li>
        <?php endif; ?>

        <?php for ($i = $left; $i <= $right; $i++): ?>
            <?php if ($i < 1 || $i > $max_page) {
                continue;
            } ?>
            <?php if ($i === $page_num): ?>
                <li class='page-item active'><span class='page-link'><?= $i ?></span></li>
            <?php else: ?>
                <li class='page-item'>
                    <a class='page-link' href="<?= $url_self . $i . $sorting ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if($page_num * $on_page >= $count): ?>
        <li class='page-item disabled'><span class='page-link'>След.</span></li>
        <li class='page-item disabled'><span class='page-link'>Конец</span></li>
        <?php else: ?>
        <li class='page-item'><a class='page-link' href="<?= $url_self . ($page_num + 1) . $sorting ?>">След.</a></li>
        <li class='page-item'><a class='page-link' href="<?= $url_self . $max_page . $sorting ?>">Конец</a></li>
        <?php endif; ?>
    </ul>

<?php endif; ?>