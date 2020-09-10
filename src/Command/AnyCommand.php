<?php

namespace Janfish\Phalcon\AsyncCaller\Command;


/**
 * Class AnyCommand
 * @package Janfish\Phalcon\AsyncCaller\Command
 */
class AnyCommand implements CommandInterface
{
    private $_error;
    private $_resultData;
    private $_commands;

    public function __construct(array $commands)
    {
        $this->_commands = $commands;
        if (!isset($this->_commands['model']) || !isset($this->_commands['method']) || !isset($this->_commands['modelParams']) || !isset($this->_commands['methodParams'])) {
            $this->setError('参数不完整，无法解析当前当前调用');
            return false;
        }
        if (!$this->_commands['methodParams'] || !is_array($this->_commands['methodParams'])) {
            $this->_commands['methodParams'] = [];
        }
        if (!$this->_commands['model'] || !$this->_commands['method']) {
            $this->setError('参数为空，无法解析当前当前调用');
            return false;
        }
    }

    /**
     * Author:Robert
     *
     * @return string
     */
    public function getError(): string
    {
        return $this->_error;
    }

    /**
     * Author:Robert
     *
     * @param string $error
     */
    public function setError(string $error): void
    {
        $this->_error = $error;
    }

    /**
     * Author:Robert
     *
     * @return mixed
     */
    public function getResultData()
    {
        return $this->_resultData;
    }

    /**
     * @return bool
     */
    public function execute(): bool
    {
        $model = new $this->_commands['model']();
        if (!$model instanceof CommandInterface) {
            $this->setError('Illegal model,your instance must implement from \Janfish\Phalcon\AsyncCaller\Command\ORMModelInterface');
            return false;
        }
        if (method_exists($model, $this->_commands['method']) === false) {
            $this->setError('Method not exist');
            return false;
        }
        $this->_resultData = call_user_func_array([
            $model,
            $this->_commands['method'],
        ], $this->_commands['methodParams']);
        if (!$this->_resultData) {
            $this->setError('called '.$this->_commands['method'].' return false');
            return false;
        }
        return true;
    }


}