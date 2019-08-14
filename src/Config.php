<?php

namespace Janfish\Phalcon\AsyncCaller;

class Config
{

    /**
     * 中间件管道名称
     * Author:Robert
     *
     * @var
     */
    public $queueTube = 'async_call_test';

    /**
     * Author:Robert
     *
     * @var
     */
    public $logPath = '';

    /**
     * Author:Robert
     *
     * @var bool
     */
    public $daemonize = false;

    /**
     * Author:Robert
     *
     * @var mixed|string
     */
    public $host = '127.0.0.1';


    /**
     * Author:Robert
     *
     * @var mixed|string
     */
    public $port = 11300;

    /**
     * Author:Robert
     *
     * @var mixed|string
     */
    public $workerNum = 1;

    /**
     * Author:Robert
     *
     * @var mixed|string
     */
    public $pidFile = '';

    /**
     * Author:Robert
     *
     * @var int
     */
    public $reserveTimeout = 2;

    /**
     * Author:Robert
     *
     * @var int
     */
    public $maxRequest = 1000;

    /**
     * Author:Robert
     *
     * @var int
     */
    public $cron = 200;

    /**
     * Middleware constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (isset($options['tube'])) {
            $this->queueTube = $options['tube'];
        }
        if (isset($options['cron'])) {
            $this->cron = $options['cron'];
        }

        if (isset($options['logPath'])) {
            $this->logPath = $options['logPath'];
        }
        if (isset($options['host'])) {
            $this->host = $options['host'];
        }
        if (isset($options['port'])) {
            $this->port = $options['port'];
        }
        if (isset($options['workerNum'])) {
            $this->workerNum = $options['workerNum'];
        }
        if (isset($options['pidFile'])) {
            $this->pidFile = $options['pidFile'];
        }
        if (isset($options['daemonize'])) {
            $this->daemonize = $options['daemonize'];
        }
        if (isset($options['maxRequest'])) {
            $this->maxRequest = $options['maxRequest'];
        }
        if (isset($options['reserveTimeout'])) {
            $this->reserveTimeout = $options['reserveTimeout'];
        }
    }

    /**
     * Author:Robert
     *
     * @return array
     */
    public function getQueueTube(): array
    {
        if (!$this->queueTube) {
            $this->queueTube = uniqid();
        }
        if (!is_array($this->queueTube)) {
            $this->queueTube = [$this->queueTube];
        }
        foreach ($this->queueTube as &$queueTube) {
            $queueTube = crc32($queueTube);
        }
        return $this->queueTube;

    }

    /**
     * Author:Robert
     *
     * @param $options
     * @return Config
     */
    public static function getInstance($options): Config
    {
        return new self($options);
    }

    /**
     * Author:Robert
     *
     * @return int
     */
    public function getWorkerNum(): int
    {
        return $this->workerNum;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getPidFile(): string
    {
        return $this->pidFile;
    }

    public function getLogPath(): string
    {
        return $this->logPath;
    }


    public function getDeamonize(): bool
    {
        return $this->daemonize;
    }

    public function getReserveTimeout(): int
    {
        return $this->reserveTimeout;
    }

    public function getMaxRequest(): int
    {
        return $this->maxRequest;
    }

    public function getCron(): int
    {
        return $this->cron;
    }
}
