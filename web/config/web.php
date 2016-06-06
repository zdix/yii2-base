<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'defaultRoute' => '/site/index',
    'controllerMap' => [
        'site' => 'dix\base\web\controller\SiteController',
        'test' => 'dix\base\web\controller\TestController',
    ],
    'components' => [
        'session' => [
            'class' => 'dix\base\component\RedisSession',
        ],
        'cache' => [
            'class' => 'dix\base\component\RedisCache',
        ],
        'urlManager' => [
            'rules' => [
                // '<controller:.+>/<id:\d+>' => '<controller>/view',
                // '<controller:.+>/<action:.+>/<id:\d+>' => '<controller>/<action>',
                // '<controller:.+>/<action:.+>' => '<controller>/<action>',
            ],
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],        
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'db' => require(__DIR__ . '/db.php'),
    ],
    'params' => $params,
];

if (YII_ENV_DEV)
{
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}

return $config;
