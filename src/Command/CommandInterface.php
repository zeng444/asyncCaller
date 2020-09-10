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

    /**
     * 任务需要重新发布
     */
    const RELEASE_RESULT_STATUS = 'RELEASE';

    /**
     * 任务需要被BUEY
     */
    const BURY_RESULT_STATUS = 'BURY';

    /**
     * 任务需要删除
     */
    const DELETE_RESULT_STATUS = 'DELETE';

    /**
     * 任务已删除无需处理
     */
    const DELETED_RESULT_STATUS = 'DELETED';

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
//    public function getRetryIntervalTime(): int;

    /**
     * 执行状态
     * Author:Robert
     *
     * @return string
     */
//    public function getStatus(): string;

    /**
     * 执行的对象实例
     * Author:Robert
     *
     * @return mixed
     */
//    public function getCalledInstance();


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
