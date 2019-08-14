# 消息中间件


## 特性

- 支持多种类型的异步调用，使用消息队列服务保证消费事务一致性
- 支持多进程、安全关闭、安全重启、后台进程化运行
- 支持内存回收，进程完成指定任务数后，自动注销重建
- 消息队列服务器断线自动延时重连
- 支持自定义任务模版


## 安装

```
composer require janfish/async-caller
```

## 服务端

- 启动服务

```php
$asyncModel = new Janfish\Phalcon\AsyncCall\Server([
    'host' => 'beanstalkd2',
    'port' => '11300',
    'tube' => 'test_tube',
    'workerNum' => 2,
    'reserveTimeout' => 2,
    'maxRequest' => 2000,
    'cron' => 200,
    'daemonize' => false,
    'pidFile' => __DIR__.'/.async_task.pid',
    'logPath' => __DIR__.'/async.log',
]);
$asyncModel->start();
```

| 参数          | 默认| 说明|
|-----------------|---------------------|----------|
|  host           | 127.0.0.1           | Beanstalkd服务地址  |
|  port           | 11300               | Beanstalkd服务端口  |
|  tube           | async_call_test     | 消费队列名，多个填写数组，单个填写字符串 |
|  workerNum      | 1                   | 进程数 |
|  reserveTimeout | 2                   | 读取队列超时时间，单位秒 |
|  maxRequest     | 1000                | 最大完成任务数，当一个进程达到最大任务数，将平滑重启 |
|  cron           | 200                 | 定时任务周期，默认200毫秒   |
|  daemonize      | false               | 是否已后台进程方式运行 |
|  pidFile        | /tmp/async_task_%d.pid | 运行时pid记录的地址 |
|  logPath        |                     | 日志文件地址，设置后将写日志 |

- 重启服务

```php
$asyncModel = new Janfish\Phalcon\AsyncCall\Server([
    'pidFile' => __DIR__.'/.async_task.pid',
]);
$asyncModel->restart();
```
- 关闭服务

```php
$asyncModel = new Janfish\Phalcon\AsyncCall\Server([
    'pidFile' => __DIR__.'/.async_task.pid',
]);
$asyncModel->stop();
```

- linux命令关闭

> 主进程pid直接kill，子进程会检查自己任务执行完后自行退出

```bash
kill -9 $pid
```

## docker 下运行

- docker下运行无法平滑重启和关闭的问题，原因是非进程模式下运行asyncCaller是使用容器内PID=1进程的，docker下默认PID=1进程不处理发起的sigterm信息的
- 解决方案是docker挂起命令以下script脚本，然后此脚本会运行在PID=1下，asyncCaller运行在其他PID，就可以正常接收信号了

```bash
#!/bin/bash
FOLDER=/data/asyncCallerHub/demo
CMD=${FOLDER}/server_start.php
LOG_FILE=${FOLDER}/async.log
php -f ${CMD}
tail -f ${LOG_FILE}
```

然后是平滑重启

```
docker exec -d  swoole-cli php /data/asyncCallerHub/demo/server_restart.php
```

## 开启多套服务

- 只需要定义不同的pid即可

```php
$asyncModel = new Janfish\Phalcon\AsyncCall\Server([
    'host' => 'beanstalkd2',
    'port' => '11300',
    'tube' => 'test_tube1',
    'pidFile' => __DIR__.'/.async_task.pid',
],'\\Core\\Mailler');
$asyncModel->start();
```

```php
$asyncModel = new Janfish\Phalcon\AsyncCall\Server([
    'host' => 'beanstalkd2',
    'port' => '11300',
    'tube' => 'test_tube2',
    'pidFile' => __DIR__.'/.async_task_2.pid',
],'\\Core\\Mailler');
$asyncModel->start();

```

## 客户端

```
$asyncModel = new \Janfish\Phalcon\AsyncCall\Client([
    'host' => 'beanstalkd',
    'port' => '11300',
    'tube' => 'test_tube',
]);
$result = $asyncModel->asyncCall([
   'model' => 'Order',
   'modelParams' => $insuranceQuotation->id,
   'method' => 'cancel',
   'delay' => 10,
   'retryTimeTable' =>  [time() + 10, time() + 120, time() + 3600]
   'retryIntervalTime' => 600,
   'retryStopAt' => date('Y-m-d H:i:s', strtotime('+2 hours')),
   'forceSync' => false,
]);
```

| 参数 | 类型      |必填    |  说明 | 
|------|----------|------|------|
|model | string   | 是 |  调用的ORM模型对象名称|
|modelParams     |  mixed  | 否 | 调用的ORM的条件参数，空字符串，criteria条件，或数字主键ID |
|method|  string   | 是 |  调用ORM对象的方法|
|methodParams|   array| 否| 调用的方法参数|
|delay| int  |  否| 延时执行，单位秒|
|retryTimeTable   | int   | 否 | 按指定时刻表重复运行任务，当此参数填写时，禁止使用retryIntervalTime和retryStopAt参数，单位秒|
|retryIntervalTime| int   | 否 | 当结果为false延时执行的间隔时间，单位秒|
|retryStopAt| datetime  | 否| 延时执行的停止执行时间 Y-m-d h:i:s |
|forceSync| bool  | 否| 强制同步执行,默认false |

## 订制命令解析

- 继承 \Janfish\Phalcon\AsyncCall\Command，并实现其中的方法，定义如何解析和执行收到的消息

```
<?php

namespace Janfish\Phalcon\AsyncCall\Command;

/**
 * Author:Robert
 *
 * Interface CommandInterface
 * @package Janfish\Phalcon\AsyncCall\Command
 */
interface CommandInterface
{

    const RELEASE_RESULT_STATUS = 'RELEASE';

    const BURY_RESULT_STATUS = 'BURY';

    const DELETE_RESULT_STATUS = 'DELETE';

    /**
     * 执行命令
     * Author:Robert
     *
     * @return bool
     */
    public function execute(): bool;

    /**
     * 执行结果数据
     * Author:Robert
     *
     * @return mixed
     */
    public function getResultData();


    /**
     * 重发延时
     * Author:Robert
     *
     * @return int
     */
    public function getRetryIntervalTime(): int;

    /**
     * 执行状态
     * Author:Robert
     *
     * @return string
     */
    public function getStatus(): string;

    /**
     * 执行的对象实例
     * Author:Robert
     *
     * @return mixed
     */
    public function getCalledInstance();


    /**
     * Author:Robert
     *
     * @return string
     */
    public function getError(): string;

    /**
     * Author:Robert
     *
     * @param string $error
     */
    public function setError(string $error): void;
}

```

## 配置服务器使其执行订制的命令

- 服务端：

> 将自定义的解析对象指定给服务器

```php
$asyncModel = new Janfish\Phalcon\AsyncCall\Server([
    'host' => 'beanstalkd2',
    'port' => '11300',
    'tube' => 'test_tube',
    'workerNum' => 2,
    'reserveTimeout' => 2,
    'maxRequest' => 112111111112,
    'cron' => 200,
    'daemonize' => false,
    'pidFile' => __DIR__.'/.async_task.pid',
    'logPath' => __DIR__.'/async.log',
],'\\Core\\Mailler');
$asyncModel->start();
```

- 客户端：将自己定义的消息向队列服务器对用的tube中push即可

