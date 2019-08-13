<?php

namespace Janfish\Phalcon\AsyncCaller\Process;

use Janfish\Phalcon\AsyncCaller\Command\CommandInterface;
use Janfish\Phalcon\AsyncCaller\Config;
use Janfish\Phalcon\AsyncCaller\Logger;
use Pheanstalk\Exception;
use Pheanstalk\Pheanstalk;

/**
 * Author:Robert
 *
 * Class Server
 * @property \Janfish\Phalcon\AsyncCaller\Logger $_logger
 * @package Janfish\Phalcon\AsyncCaller
 */
class Job
{

    /**
     * Author:Robert
     *
     * @var
     */
    private $_config;


    /**
     * Author:Robert
     *
     * @var
     */
    private $connection;

    /**
     * Author:Robert
     *
     * @var
     */
    private $commandType;

    /**
     * Author:Robert
     *
     * @var
     */
    private $_logger;


    const RECONNECTION_COUNT = 2;


    /**
     * Job constructor.
     * @param Config $config
     * @param $commandType
     */
    public function __construct(Config $config, $commandType)
    {
        $this->_config = $config;
        $this->commandType = $commandType;
        $this->_logger = new Logger($config);
    }


    /**
     * Author:Robert
     *
     * @return Pheanstalk
     */
    public function getConnection(): Pheanstalk
    {
        try {
            $this->connection = Pheanstalk::create($this->_config->getHost(), $this->_config->getPort());
            $this->connection->watch($this->_config->getQueueTube());
            return $this->connection;
        } catch (\Exception $e) {
            $this->_logger->debug('connection queue server error , reconnection after 1 second');
            sleep(self::RECONNECTION_COUNT);
            return $this->getConnection();
        }
    }

    /**
     * Author:Robert
     *
     * @return bool
     * @throws Exception\DeadlineSoonException
     */
    public function start(): bool
    {
        $queueService = $this->getConnection();
        $job = $queueService->reserveWithTimeout($this->_config->reserveTimeout);
        if (!$job) {
            return true;
        }
        $data = $job->getData();
        try {
            if (!$data || !$data = unserialize($data)) {
                $this->_logger->debug('参数为空无法解析当前调用');
                $queueService->bury($job);
                return false;
            }
            $com = $this->commandType;
            if (!class_exists($com)) {
                throw new \Exception('无法调用的方法');
            }
            $command = new $com($data);
            if (!$command instanceof CommandInterface) {
                throw new \Exception('无法调用的方法');
            }
            $command->execute();
            //统一处理
            switch ($command->getStatus()) {
                case CommandInterface::BURY_RESULT_STATUS:
                    $this->_logger->debug(CommandInterface::BURY_RESULT_STATUS.' '.$command->getError().' - '.json_encode($data));
                    $queueService->bury($job);
                    break;
                case CommandInterface::RELEASE_RESULT_STATUS:
                    $this->_logger->debug(CommandInterface::RELEASE_RESULT_STATUS.' '.$command->getError().' - '.json_encode($data));
                    $queueService->release($job, time(), $command->getRetryIntervalTime());
                    break;
                case CommandInterface::DELETE_RESULT_STATUS:
                    $this->_logger->debug(CommandInterface::DELETE_RESULT_STATUS.' '.json_encode($command->getResultData()).' - '.json_encode($data));
                    $queueService->delete($job);
                    break;
            }
            return true;
        } catch (\Exception $e) {
            $this->_logger->debug(CommandInterface::RELEASE_RESULT_STATUS."Exception ".$e->getTraceAsString().' - '.json_encode($data));
            $queueService->release($job, time(), 10);
            return false;
        }
    }
}
