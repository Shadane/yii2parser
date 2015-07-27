<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii'],
    'controllerNamespace' => 'app\commands',
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'flushInterval' => 1,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'logVars' => [],
                    'categories' => ['parseInfo'],
                    'logFile' => '@app/runtime/logs/parse.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20,
                    'exportInterval' => 1,
                    'prefix' => function ($message) {
                        return "[message]";
                    }
                ]
            ],
        ],
        'db' => $db,
    ],
    'params' => $params,
];
