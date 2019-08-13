<?php
define('ROOT_PATH', dirname(__DIR__).DIRECTORY_SEPARATOR);
include_once ROOT_PATH.'vendor/autoload.php';
include_once ROOT_PATH.'demo/Test.php';

use Janfish\Phalcon\AsyncCaller\Client as AsyncCallerClient;

$asyncModel = new AsyncCallerClient([
    'host' => 'beanstalkd2',
    'tube' => 'test2',
]);
$result = $asyncModel->asyncCall([
    'model' => '\Janfish\Phalcon\AsyncCaller\Test',
    'method' => 'test',
    'forceSync' => false,
    'modelParams' => 1,
        'delay' => 2,
    'retryTimeTable' => [time() + 10, time() + 20, time() + 30],
    //    'retryIntervalTime' => '5',
//        'retryStopAt' => date('Y-m-d H:i:s', time() + 20),
]);

if (!$result) {
    echo $asyncModel->getErrorMsg().PHP_EOL;
}

print_r($result);
