<?php

$params_extra = require(__DIR__ . '/params_extra.php');

$base = [
    'version' => '0.0.1',

    'redis-param' => [
        'scheme' => 'tcp',
        'host'   => 'redis',
        'port'   => 6379,
        'password' => null,
        'read_write_timeout' => 0,
        'database' => 0,
    ],

    'redis-option' => [
        'prefix' => '',
    ],
];

return array_merge($base, $params_extra);
