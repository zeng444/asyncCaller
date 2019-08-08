<?php
include_once '../vendor/autoload.php';
include_once 'Test.php';

use Janfish\Phalcon\AsyncCaller\Client as AsyncCallerClient;

$asyncModel = new AsyncCallerClient([
    'host' => 'beanstalkd2',
    'tube' => 'test2',
]);
$result = $asyncModel->asyncCall([
    'model' => '\Janfish\Phalcon\AsyncCaller\Test',
    'method' => 'test',
    //    'retryIntervalTime' => '5',
    //    'retryStopAt' => date('Y-m-d H:i:s', time() + 20),
]);

if (!$result) {
    echo $asyncModel->getErrorMsg().PHP_EOL;
}

print_r($result);
