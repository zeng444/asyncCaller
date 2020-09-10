<?php

namespace Janfish\Phalcon\AsyncCaller\Command;


/**
 * Class AnyCommand
 * @package Janfish\Phalcon\AsyncCaller\Command
 */
class AnyCommand implements CommandInterface
{
    private $_error;
    private $_status;
    private $_resultData;
    private $_commands;

    public function __construct(array $commands)
    {
        $this->_commands = $commands;
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

    public function getStatus(): string
    {
        return $this->_status;
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


    public function execute(): bool
    {

        return true;
    }


}