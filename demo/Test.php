<?php

namespace Janfish\Phalcon\AsyncCaller;

class Test implements ModelInterface
{
    public $error = '';

    public function test()
    {
        $this->error = 'failed info from Test';
        return false;
    }

    public function getMessage(): string
    {
        return $this->error;
    }
}
