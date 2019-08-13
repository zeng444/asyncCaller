<?php
define('ROOT_PATH', dirname(__DIR__).DIRECTORY_SEPARATOR);
include_once ROOT_PATH.'vendor/autoload.php';
include_once ROOT_PATH.'demo/Test.php';

try {
    $asyncModel = new Janfish\Phalcon\AsyncCaller\Server([
        'host' => 'beanstalkd',
        'tube' => 'test2',
        'workerNum' => 2,
        'reserveTimeout' => 2,
        'maxRequest' => 50000,
        'cron' => 100,
        'daemonize' => false,
        'pidFile' => __DIR__.'/.async_task.pid',
//        'logPath' => __DIR__.'/async.log',
    ]);
    $asyncModel->start();
} catch (Exception $e) {
    echo $e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL;
}
