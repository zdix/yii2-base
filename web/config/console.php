<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'controllerMap' => [
            [
                'account' => 'app\controllers\UserController',
            ],
        ],
        'cache' => [
            'class' => 'smartwork\base\component\RedisCache;',
        ],
        'db' => $db,
    ],
    'params' => $params,
];
