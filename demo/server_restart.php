<?php
include_once '../vendor/autoload.php';

try {
    $asyncModel = new Janfish\Phalcon\AsyncCaller\Server([
        'host' => 'beanstalkd2',
        'tube' => 'test2',
        'workerNum' => 2,
        'daemonize' => false,
        'pidFile' => __DIR__.'/.async_task.pid',
        'reserveTimeout' => 2,
    ]);
    var_dump($asyncModel->restart());
} catch (Exception $e) {
    echo $e->getTraceAsString();
}
