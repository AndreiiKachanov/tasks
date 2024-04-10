 <?php if (!empty($items)): ?>
    <?php $items = deserializeIpInfo($items); ?>
        <table class="bottom">
            <thead>
            <tr>
                <th scope="col">Дата входа</th>
                <th scope="col">Робот</th>
                <th scope="col">Ip адрес</th>
                <th scope="col">Uri</th>
                <th scope="col">Город</th>
                <th scope="col">Девайс</th>
                <th scope="col">Версия девайса</th>
                <th scope="col">Платформа</th>
                <th scope="col">Браузер</th>
                <th scope="col">Мобильник</th>
                <th scope="col">Планшет</th>
                <th scope="col">Десктоп</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td data-label="Дата входа"> <?= date('d.m.Y H:i:s', strtotime($item['created_at'])) ?></td>
                    <td data-label="Робот"><?= $item['is_robot'] ? '&#x2705;' : '&nbsp;'; ?></td>
                    <td data-label="Ip адрес">
                        <?= filter_var($item['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 'ipv6' : $item['ip']; ?>
                    </td>
                    <td data-label="Url">
                        <?= $item['request_uri'] ?>
                    </td>
                    <td data-label="Город">

                        <?php if(isset($item['ip_info']['flag'])): ?>
                            <img style="width: 15px" src="<?= $item['ip_info']['flag']; ?>" alt="Flag" class="flag-icon">
                        <?php endif; ?>

                        <?= is_null($item['ip_info']) ? '&nbsp;' : ($item['ip_info']['city'] ?? '&nbsp;'); ?>
                    </td>
                    <td data-label="Девайс">
                        <?= is_null($item['device']) ? '&nbsp;' : $item['device']; ?>
                    </td>
                    <td data-label="Версия девайса">
                        <?= is_null($item['device_version']) ? '&nbsp;' : $item['device_version']; ?>
                    </td>
                    <td data-label="Платформа">
                        <?= is_null($item['platform']) ? '&nbsp;' : $item['platform']; ?>
                    </td>
                    <td data-label="Браузер">
                        <?= is_null($item['browser']) ? '&nbsp;' : $item['browser']; ?>
                    </td>
                    <td data-label="Мобильник"><?= $item['is_mobile'] ? '&#x2705;' : '&nbsp;'; ?></td>
                    <td data-label="Планшет"><?= $item['is_tablet'] ? '&#x2705;' : '&nbsp;'; ?></td>
                    <td data-label="Десктоп"><?= $item['is_desktop'] ? '&#x2705;' : '&nbsp;'; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
<?php else: ?>
    <div class="text-center">
        <h3>Просмотров нет!</h3>
    </div>
<?php endif; ?>

<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <?=$navbar ?>
    </ul>
</nav>

