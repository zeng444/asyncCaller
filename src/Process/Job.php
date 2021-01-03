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

    const RECONNECTION_COUNT = 2;
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
                $this->_logger->debug('Command params is not null');
                $queueService->bury($job);
                return false;
            }
            $com = $this->commandType;
            if (!class_exists($com)) {
                $queueService->bury($job);
                throw new \Exception('Command class is not exist');
            }
            $command = new $com($data);
            if (!$command instanceof CommandInterface) {
                $queueService->bury($job);
                throw new \Exception('Command method is not exist');
            }
            if ($data['noRetry'] === true) {
                $queueService->delete($job);
                $this->_logger->debug(CommandInterface::DELETED_RESULT_STATUS.' '.json_encode($command->getResultData()).' - '.json_encode($data));
            }
            $result = $command->execute();
            if ($data['noRetry'] === false) {
                if ($result) {
                    $this->_logger->debug(CommandInterface::DELETE_RESULT_STATUS.' '.json_encode($command->getResultData()).' - '.json_encode($data));
                    $queueService->delete($job);
                    return true;
                }
                if (isset($data['retryIntervalTime']) && $data['retryIntervalTime'] > 0) {
                    if (isset($data['retryStopAt']) && time() > strtotime($data['retryStopAt'])) {
                        $error = $command->getError().' - 重新发布的任务结束，达到最大重试时间点'.$data['retryStopAt'];
                        $this->_logger->debug(CommandInterface::DELETE_RESULT_STATUS.' '.$error.' - '.json_encode($data));
                        $queueService->delete($job);
                    } else {
                        $error = $command->getError().' - 重新发布，延时'.$data['retryIntervalTime'].'秒后重新执行';
                        $this->_logger->debug(CommandInterface::RELEASE_RESULT_STATUS.' '.$error.' - '.json_encode($data));
                        $queueService->release($job, time(), intval($data['retryIntervalTime']));
                    }
                } else {
                    $this->_logger->debug(CommandInterface::BURY_RESULT_STATUS.' '.$command->getError().' - '.json_encode($data));
                    $queueService->bury($job);
                }
            }
            return true;
        } catch (\Exception $e) {
            if ($data['noRetry'] === false && $data['errorRetry'] !== false) {
                $this->_logger->debug(CommandInterface::RELEASE_RESULT_STATUS."Exception ".$e->getTraceAsString().' - '.json_encode($data));
                $queueService->release($job, time(), 10);
            } else {
                $this->_logger->debug(CommandInterface::BURY_RESULT_STATUS."Exception ".$e->getTraceAsString().' - '.json_encode($data));
                $queueService->bury($job);
            }
            return false;
        }
    }

    /**
     * Author:Robert
     *
     * @return Pheanstalk
     */
    public function getConnection(): Pheanstalk
    {

        $this->connection = Pheanstalk::create($this->_config->getHost(), $this->_config->getPort());
        $tubes = $this->_config->getQueueTube();
        foreach ($tubes as $tube) {
            $this->connection->watch($tube);
        }
        if ($tubes) {
            $this->connection->ignore('default');
        }
        return $this->connection;

        //            $this->_logger->debug('connection queue server error , reconnection after '.self::RECONNECTION_COUNT.' second');
        //            sleep(self::RECONNECTION_COUNT);
        //            return $this->getConnection();

    }
}
