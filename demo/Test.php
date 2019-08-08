<?php

namespace Janfish\Phalcon\AsyncCaller;

class Test implements ModelInterface
{
    public function test()
    {

        return false;
    }

    public function getMessage(): string
    {
        return 'failed info from Test';
    }
}
