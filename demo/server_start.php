<?php
include_once '../vendor/autoload.php';

try {
    $asyncModel = new Janfish\Phalcon\AsyncCaller\Server([
        'host' => 'beanstalkd2',
        'tube' => 'test2',
        'workerNum' => 2,
        'reserveTimeout' => 2,
        'maxRequest' => 112111111112,
        'cron' => 1,
        'daemonize' => false,
        'pidFile' => __DIR__.'/.async_task.pid',
//        'logPath' => __DIR__.'/async.log',
    ]);
    $asyncModel->start();
} catch (Exception $e) {
    echo $e->getTraceAsString();
}
