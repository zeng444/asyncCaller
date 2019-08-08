<?php

namespace Janfish\Phalcon\AsyncCaller;

use Janfish\Phalcon\AsyncCaller\Process\Job;
use Janfish\Phalcon\AsyncCaller\Process\Pool as Pool;

//use Janfish\Phalcon\AsyncCaller\Process\Pool;

/**
 * Author:Robert
 *
 * @property  \Janfish\Phalcon\AsyncCaller\Process\Pool $pool
 * @property  \Janfish\Phalcon\AsyncCaller\Process\Job $job
 * @property  \Janfish\Phalcon\AsyncCaller\Config $_config
 * Class Server
 * @package Janfish\Phalcon\AsyncCaller
 */
class Server
{


    /**
     * Author:Robert
     *
     * @var Config
     */
    private $_config;


    /**
     * Author:Robert
     *
     * @var
     */
    protected $pool;


    /**
     * Author:Robert
     *
     * @var Job
     */
    protected $job;

    /**
     * Author:Robert
     *
     * @var
     */
    protected $daemonize;


    /**
     * Server constructor.
     * @param array $options
     * @param string $commandType
     */
    public function __construct(array $options = [], $commandType = '\\Janfish\\Phalcon\\AsyncCaller\\Command\\ORMCommand')
    {
        $this->_config = Config::getInstance($options);
        $this->job = new Job($this->_config, $commandType);
        $this->pool = new Pool($this->_config);
    }

    /**
     * Author:Robert
     *
     * @return bool
     */
    public function start(): bool
    {
        $this->pool->on('workStart', array($this, 'todo'));
        return $this->pool->start();
    }

    /**
     * Author:Robert
     *
     * @return bool
     */
    public function restart(): bool
    {
        if (!$this->stop()) {
            return false;
        }
        sleep(1);
        return $this->start();
    }

    /**
     * Author:Robert
     *
     * @return bool
     */
    public function stop(): bool
    {
        return $this->pool->stop();
    }

    /**
     * Author:Robert
     *
     * @param \Swoole\Process $worker
     * @throws \Pheanstalk\Exception\DeadlineSoonException
     */
    public function todo(\Swoole\Process $worker)
    {
        $this->job->start();
    }


}
