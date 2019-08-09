<?php

namespace Janfish\Phalcon\AsyncCaller\Command;

use Janfish\Phalcon\AsyncCaller\ModelInterface;

/**
 * Author:Robert
 *
 * Class ORM
 * @package Janfish\Phalcon\AsyncCaller\Command
 */
class ORMCommand implements CommandInterface
{

    /**
     * Author:Robert
     *
     * @var
     */
    protected $error = '';

    /**
     * Author:Robert
     *
     * @var
     */
    protected $resultData;

    /**
     * Author:Robert
     *
     * @var
     */
    protected $status = '';

    /**
     * Author:Robert
     *
     * @var int
     */
    protected $retryIntervalTime = 0;

    /**
     * Author:Robert
     *
     * @var
     */
    protected $calledInstance;

    /**
     * Author:Robert
     *
     * @var
     */
    protected $commands;


    /**
     * ORMCommand constructor.
     * @param array $commands
     */
    public function __construct(array $commands)
    {
        $this->commands = $commands;
    }

    /**
     * Author:Robert
     *
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * Author:Robert
     *
     * @param string $error
     */
    public function setError(string $error): void
    {
        $this->error = $error;
    }

    /**
     * Author:Robert
     *
     * @return int
     */
    public function getRetryIntervalTime(): int
    {
        return $this->retryIntervalTime;
    }


    /**
     * Author:Robert
     *
     * @return bool
     */
    public function execute(): bool
    {
        if (!isset($this->commands['model']) || !isset($this->commands['method']) || !isset($this->commands['modelParams']) || !isset($this->commands['methodParams'])) {
            $this->status = CommandInterface::BURY_RESULT_STATUS;
            $this->setError('参数不完整，无法解析当前当前调用');
            return false;
        }
        if (!$this->commands['model'] || !$this->commands['method']) {
            $this->status = CommandInterface::BURY_RESULT_STATUS;
            $this->setError('参数为空，无法解析当前当前调用');
            return false;
        }
        if (!$this->commands['methodParams'] || !is_array($this->commands['methodParams'])) {
            $this->commands['methodParams'] = [];
        }
        if (class_exists($this->commands['model']) === false) {
            $this->status = CommandInterface::BURY_RESULT_STATUS;
            $this->setError('Model not exist');
            return false;
        }
        if ($this->commands['methodParams']) {
            $model = call_user_func_array($this->commands['model'].'::findFirst', [$this->commands['methodParams']]);
        } else {
            $model = new $this->commands['model']();
        }
        if (!$model) {
            $this->status = CommandInterface::BURY_RESULT_STATUS;
            $this->setError('Model not exist');
            return false;
        }
        if (!$model instanceof ModelInterface) {
            $this->status = CommandInterface::BURY_RESULT_STATUS;
            $this->setError('Illegal model,your instance must implement from \Janfish\Phalcon\AsyncCaller\ModelInterface');
            return false;
        }
        if (method_exists($model, $this->commands['method']) === false) {
            $this->status = CommandInterface::BURY_RESULT_STATUS;
            $this->setError('Method not exist');
            return false;
        }
        $this->calledInstance = $model;
        $this->resultData = call_user_func_array([
            $this->calledInstance,
            $this->commands['method'],
        ], $this->commands['methodParams']);
        if (!$this->resultData) {
            if (isset($this->commands['retryIntervalTime']) && $this->commands['retryIntervalTime'] > 0) {
                if (isset($this->commands['retryStopAt']) && time() > strtotime($this->commands['retryStopAt'])) {
                    $this->setError($this->calledInstance->getMessage().' - 重新发布的任务结束，达到最大重试时间点'.$this->commands['retryStopAt']);
                    $this->status = CommandInterface::DELETE_RESULT_STATUS;
                    return false;
                }
                $this->setError($this->calledInstance->getMessage().' - 重新发布，延时'.$this->commands['retryIntervalTime'].'秒后重新执行');
                $this->retryIntervalTime = intval($this->commands['retryIntervalTime']);
                $this->status = CommandInterface::RELEASE_RESULT_STATUS;
                //            $queueService->release($job, time(), $this->commands['retryIntervalTime']);
                return false;
            } else {
                $this->setError($this->calledInstance->getMessage());
                $this->status = CommandInterface::BURY_RESULT_STATUS;
                return false;
            }
        }
        $this->status = CommandInterface::DELETE_RESULT_STATUS;
        return true;
    }


    /**
     * Author:Robert
     *
     * @return mixed
     */
    public function getCalledInstance()
    {
        return $this->calledInstance;
    }


    /**
     * Author:Robert
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Author:Robert
     *
     * @return mixed
     */
    public function getResultData()
    {
        return $this->resultData;
    }
}
