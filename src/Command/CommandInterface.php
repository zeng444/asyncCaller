<?php

namespace Janfish\Phalcon\AsyncCaller\Command;

/**
 * Author:Robert
 *
 * Interface CommandInterface
 * @package Janfish\Phalcon\AsyncCaller\Command
 */
interface CommandInterface
{

    const RELEASE_RESULT_STATUS = 'RELEASE';

    const BURY_RESULT_STATUS = 'BURY';

    const DELETE_RESULT_STATUS = 'DELETE';

    /**
     * 执行命令
     * Author:Robert
     *
     * @return bool
     */
    public function execute(): bool;

    /**
     * 执行结果数据
     * Author:Robert
     *
     * @return mixed
     */
    public function getResultData();


    /**
     * 重发延时
     * Author:Robert
     *
     * @return int
     */
    public function getRetryIntervalTime(): int;

    /**
     * 执行状态
     * Author:Robert
     *
     * @return string
     */
    public function getStatus(): string;

    /**
     * 执行的对象实例
     * Author:Robert
     *
     * @return mixed
     */
    public function getCalledInstance();


    /**
     * Author:Robert
     *
     * @return string
     */
    public function getError(): string;

    /**
     * Author:Robert
     *
     * @param string $error
     */
    public function setError(string $error): void;
}
