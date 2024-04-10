<?php

//если в массиве только одно значения запись должна быть в таком виде 'role' => ['field']
return [
    TABLE_PREFIX . 'tasks' => [
        'fields'       => ['id', 'id_user', 'author', 'email', 'content', 'status', 'img'],
        'not_empty'    => ['id', 'author', 'email', 'content', 'status'],
        'html_allowed' => [],
        'nickname'     => ['author'],
        'email'        => ['email'],
        'range'        => [
            'author'  => ['3', '20'],
            'content' => ['5', '500'],
        ],
        'labels'       => [
            'author'  => '"Автор задачи"',
            'email'   => '"Email"',
            'content' => '"Текст задачи"'
        ],
        'pk'           => 'id'
    ],
    TABLE_PREFIX . 'views' => [
        'fields'       => [
            'id',
            'ip',
            'request_uri',
            'ip_info',
            'browser',
            'device',
            'device_version',
            'is_mobile',
            'is_tablet',
            'is_desktop',
            'is_robot',
            'created_at'
        ],
        'pk'           => 'id'
    ],
];
