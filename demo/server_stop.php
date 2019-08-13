<?php
define('ROOT_PATH', dirname(__DIR__).DIRECTORY_SEPARATOR);
include_once ROOT_PATH.'vendor/autoload.php';

try {
    $asyncModel = new Janfish\Phalcon\AsyncCaller\Server([
        'pidFile' => __DIR__.'/.async_task.pid'
    ]);
    var_dump($asyncModel->stop());
} catch (Exception $e) {
    echo $e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL;
}
