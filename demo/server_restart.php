<?php
define('ROOT_PATH', dirname(__DIR__).DIRECTORY_SEPARATOR);
include_once ROOT_PATH.'vendor/autoload.php';

try {
    $asyncModel = new Janfish\Phalcon\AsyncCaller\Server([
        'host' => 'beanstalkd2',
        'tube' => 'test2',
        'workerNum' => 2,
        'daemonize' => true,
        'pidFile' => __DIR__.'/.async_task.pid',
//        'pidFile' => __DIR__.'/.async_task.pid',
        'reserveTimeout' => 2,
    ]);
    $asyncModel->restart();
} catch (Exception $e) {
    echo $e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL;
}
