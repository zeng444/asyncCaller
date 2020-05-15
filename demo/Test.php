<?php

namespace Janfish\Phalcon\AsyncCaller;
use \Janfish\Phalcon\AsyncCaller\Command\ORMModelInterface as ModelInterface;

/**
 * Author:Robert
 *
 * Class Test
 * @package Janfish\Phalcon\AsyncCaller
 */
class Test implements ModelInterface
{
    public $error = '';

    /**
     * Author:Robert
     *
     * @return bool
     * @throws \Exception
     */
    public function test()
    {
        $this->error = 'failed info from Test';
        return true;
    }

    public function getMessage(): string
    {
        return $this->error;
    }
}
