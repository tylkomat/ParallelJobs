<?php

namespace ParallelJobsTest\System\Fork;

class JobLongString
{
    public function doSomething($arg)
    {
        sleep(2);
        // complex job
        return 'azertyuiopazertyuiopazertyuiopazertyuiop';
    }

    public function doOtherSomething($arg1, $arg2)
    {
        sleep(1);
        // bad job
        return 'azertyuiopazertyuiopazertyuiopazertyuiopazertyuiopazertyuiopazertyuiopazertyuiop';
    }
}
