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
     * Server constructor.
     * @param array $options
     * @param string $commandType
     * @throws \Exception
     */
    public function __construct(array $options = [], $commandType = '\\Janfish\\Phalcon\\AsyncCaller\\Command\\ORMCommand')
    {
        if (!extension_loaded('swoole')) {
            throw new \Exception('sorry ,your haven\'t installed swoole extension yet.');
        }
        $this->_config = Config::getInstance($options);
        $this->job = new Job($this->_config, $commandType);
        $this->pool = new Pool($this->_config);
    }

    /**
     * Author:Robert
     *
     */
    public function tips(): void
    {
        echo PHP_EOL;
        echo '==============================================='.PHP_EOL;
        echo PHP_EOL;
        echo ' PHP Ver:'.PHP_VERSION.' Swoole Ver:'.SWOOLE_VERSION.PHP_EOL;
        echo PHP_EOL;
        echo ' Process:'.$this->_config->getWorkerNum().' Cron:'.$this->_config->getCron().' Max Request:'.$this->_config->getMaxRequest().PHP_EOL;
        echo PHP_EOL;
        if ($this->_config->getLogPath()) {
            echo ' Log Path:'.$this->_config->getLogPath().PHP_EOL;
        }
        echo PHP_EOL;
        echo '==============================================='.PHP_EOL;
        echo PHP_EOL;
    }


    /**
     * Author:Robert
     *
     * @param bool $tips
     * @return bool
     * @throws \Exception
     */
    public function start($tips = true): bool
    {
        if($tips){
            $this->tips();
        }
        $this->pool->on('workStart', array($this, 'todo'));
        if (!$this->pool->start()) {
            if($tips){
                echo "AsyncCaller is running,pid is {$this->pool->getPid()},startup failed.........".PHP_EOL.PHP_EOL;
            }
            return false;
        }
        return true;
    }

    /**
     * Author:Robert
     *
     * @return bool
     * @throws \Exception
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
