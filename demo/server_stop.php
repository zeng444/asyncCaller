<?php
include_once '../vendor/autoload.php';

try {
    $asyncModel = new Janfish\Phalcon\AsyncCaller\Server(['pidFile' => __DIR__.'/.async_task.pid']);
    var_dump($asyncModel->stop());
} catch (Exception $e) {
    echo $e->getTraceAsString();
}
