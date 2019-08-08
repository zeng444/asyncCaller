<?php

namespace Janfish\Phalcon\AsyncCaller\Process;

use Janfish\Phalcon\AsyncCaller\Config;
use Janfish\Phalcon\AsyncCaller\Logger;
use Swoole\Exception;

/**
 * Author:Robert
 *
 * Class Pool
 * @property \Janfish\Phalcon\AsyncCaller\Config $config
 * @property \Swoole\Process\Pool $pool
 * @property \Janfish\Phalcon\AsyncCaller\Logger $_logger
 * @package Janfish\Phalcon\Process
 */
class Pool
{


    /**
     * Author:Robert
     *
     * @var int|mixed
     */
    private $_pidFile;
    /**
     *
     * @var
     */
    public $process = [];

    /**
     *
     * @var
     */
    public $event = [];

    /**
     *
     * @var
     */
    private $_pid;

    /**
     * Author:Robert
     *
     * @var Logger
     */
    private $_logger;


    function __construct(Config $config)
    {
        $this->config = $config;
        $this->_pidFile = $config->getPidFile() ?: $this->genPidFile();
        $this->_logger = new Logger($config);
    }


    /**
     * Author:Robert
     *
     * @return string
     */
    private function genPidFile(): string
    {
        return 'tmp/async_task_'.crc32(uniqid(true)).'.pid';
    }

    /**
     * Author:Robert
     *
     */
    public function start()
    {
        if ($this->config->getDeamon()) {
            \Swoole\Process::daemon();
        }
        $this->setPid(posix_getpid());
        for ($index = 0; $index < $this->config->getWorkerNum(); $index++) {
            $this->createWorker($index);
        }
        $this->monitorWorker();
        return true;
    }

    /**
     * Author:Robert
     *
     */
    public function monitorWorker(): void
    {
        while (true) {
            while ($ret = \Swoole\Process::wait()) {
                $index = array_search($ret['pid'], $this->process);
                $this->_logger->debug("Sub process exited, Sub process  [{$ret['pid']}] begin restart");
                $this->createWorker($index);
            }
        }
    }

    /**
     * Author:Robert
     *
     * @param int $index
     */
    public function createWorker(int $index)
    {
        $processInfo = new \Swoole\Process([$this, 'workerStartEvent']);
        $this->process[$index] = $processInfo->start();
    }


    /**
     * Author:Robert
     *
     * @param string $event
     * @param $callback
     */
    public function on(string $event, $callback)
    {
        $this->event[$event] = $callback;
    }

    /**
     * Author:Robert
     *
     * @param \Swoole\Process $worker
     * @throws \Exception
     */
    public function workerStartEvent(\Swoole\Process $worker)
    {
        $callback = $this->event['workStart'] ?? '';
        if (!$callback || !is_callable($callback)) {
            throw new Exception('Event work Start is not exist');
        }
        $loopTime = 0;
        \Swoole\Timer::tick($this->config->getCron(), function () use ($worker, &$loopTime, $callback) {
            $loopTime++;
            if ($loopTime > $this->config->maxRequest || !$this->isRunning()) {
                $this->_logger->debug("Master process exited, Sub process [{$worker->pid}] also quit");
                $worker->exit();
            }
            $this->_logger->debug('Work '.$worker->pid.' cron job is start');
            $callback($worker);
            $this->_logger->debug('Work '.$worker->pid.' cron job is end');
        });
    }

    /**
     * Author:Robert
     *
     * @return int
     */
    public function getPid(): int
    {
        if ($this->_pid) {
            return $this->_pid;
        }
        if (!is_readable($this->_pidFile)) {
            return 0;
        }
        return file_get_contents($this->_pidFile);
    }

    /**
     * Author:Robert
     *
     * @param int $pid
     * @return bool
     */
    public function setPid(int $pid): bool
    {
        $this->_pid = $pid;
        if (!file_put_contents($this->_pidFile, $pid)) {
            return false;
        }
        return true;
    }


    /**
     * Author:Robert
     *
     * @return mixed
     */
    public function stop(): bool
    {
        if (!$this->isRunning()) {
            return false;
        }
        $pid = $this->getPid();
        @unlink($this->_pidFile);
        return \Swoole\Process::kill($pid, SIGTERM);
    }


    /**
     * Author:Robert
     *
     * @return int
     */
    public function isRunning(): int
    {
        $pid = $this->getPid();
        if (!$pid) {
            return 0;
        }
        if (!\Swoole\Process::kill($pid, 0)) {
            return 0;
        }
        return $pid;
    }

}
