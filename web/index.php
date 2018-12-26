<?php

// comment out the following two lines when deployed to production
$debugDefault = getenv('YII_DEBUG');
if (false === $debugDefault) {
    $debugDefault = true;
}
$envDefault = getenv('YII_ENV');
if (false === $envDefault) {
    $envDefault = 'dev';
}
defined('YII_DEBUG') or define('YII_DEBUG', $debugDefault);
defined('YII_ENV') or define('YII_ENV', $envDefault);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
