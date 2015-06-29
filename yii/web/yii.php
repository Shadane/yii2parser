#!/usr/bin/env php
<?php
/**
 * Yii console bootstrap file.
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);

// fcgi не имеет констант STDIN и STDOUT, они определяются по умолчанию
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

// регистрация загрузчика классов Composer
require(__DIR__ . '/../vendor/autoload.php');

// подключение файла класса Yii
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

// загрузка конфигурации приложения
$config = require(__DIR__ . '/../config/console.php');

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);