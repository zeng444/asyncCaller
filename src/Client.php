<?php

namespace Janfish\Phalcon\AsyncCaller;

use Janfish\Phalcon\AsyncCaller\Command\ORMCommand;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Contract\PheanstalkInterface;

class Client
{

    /**
     * Author:Robert
     *
     */
    private $_config;

    /**
     * Author:Robert
     *
     * @var
     */
    private $_connection;

    /**
     * Author:Robert
     *
     * @var
     */
    public $errorMsg;

    /**
     * Middleware constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->_config = Config::getInstance($options);
    }

    /**
     * Author:Robert
     *
     * @return mixed
     */
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    /**
     * Author:Robert
     *
     * @return Pheanstalk
     */
    public function getConnection(): Pheanstalk
    {
        if (!$this->_connection) {
            $this->_connection = Pheanstalk::create($this->_config->host, $this->_config->port);
        }
        return $this->_connection;
    }

    /**
     * Author:Robert
     *
     * @param string $date
     * @return bool
     */
    public static function isCorrectDatetimeFormat(string $date): bool
    {
        if (!preg_match("/^[\d]{4}\-[\d]{2}\-[\d]{2}\s([\d]{2}:[\d]{2}:[\d]{2})?$/", $date)) {
            return false;
        }
        return true;
    }

    /**
     * 参数式调用
     * Author:Robert
     *
     * @param array $params
     * @return bool
     */
    public function asyncCall(array $params)
    {
        if (!isset($params['model'])) {
            $params['model'] = '';
        }
        if (!isset($params['ttr'])) {
            $params['ttr'] = 60;
        }
        $params['modelParams'] = $params['modelParams'] ?? '';
        $params['method'] = $params['method'] ?? '';
        if (!isset($params['methodParams']) || !$params['methodParams']) {
            $params['methodParams'] = [];
        }
        if (!isset($params['delay']) || !$params['delay']) {
            $params['delay'] = 0;
        }
        if (!isset($params['retryIntervalTime']) || !$params['retryIntervalTime']) {
            $params['retryIntervalTime'] = 0;
        }
        if (!isset($params['retryStopAt']) || !$params['retryStopAt']) {
            $params['retryStopAt'] = '';
        }
        if (!isset($params['forceSync']) || !$params['forceSync']) {
            $params['forceSync'] = false;
        }
        if (!$params['model'] || !class_exists($params['model'])) {
            $this->errorMsg = '不存在的模型对象'.$params['model'];
            return false;
        }
        if (!$params['model'] || !method_exists($params['model'], $params['method'])) {
            $this->errorMsg = '不存在的模型方法'.!$params['model'].":".$params['method'];
            return false;
        }
        $retryIntervalTime = intval($params['retryIntervalTime']);
        if ($retryIntervalTime && !$params['retryStopAt']) {
            $this->errorMsg = '当设置重试后，必须设置重试结束时间'.$params['model'].":".$params['method'];
            return false;
        }
        if ($params['retryStopAt'] && !self::isCorrectDatetimeFormat($params['retryStopAt'])) {
            $this->errorMsg = '时间格式错误:'.$params['retryStopAt'];
            return false;
        }
        if ($params['forceSync'] === true) {
            $cmd = new ORMCommand($params);
            if (!$cmd->execute()) {
                $this->errorMsg = $cmd->getError();
            }
            return $cmd->getResultData();
        }
        $queueService = $this->getConnection();
        $queueService->useTube($this->_config->getQueueTube());
        $data = [
            'model' => $params['model'],
            'method' => $params['method'],
            'modelParams' => $params['modelParams'],
            'methodParams' => $params['methodParams'],
            'priority' => time(),
            'retryIntervalTime' => $retryIntervalTime,
            'retryStopAt' => $params['retryStopAt'],
        ];
        $queueService->put(serialize($data), $data['priority'] ?? PheanstalkInterface::DEFAULT_PRIORITY, intval($params['delay']), intval($params['ttr']));
        return true;
    }
}
