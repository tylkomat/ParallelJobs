<?php

namespace ZFPLTest\System\Fork;

class Job
{
    public function doSomething($arg)
    {
        sleep(2);
        // complex job
        return 'ok';
    }

    public function doOtherSomething($arg1, $arg2)
    {
        sleep(1);
        // bad job
        return 'ko';
    }
}