<?php

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

class JobObject
{
    public function doSomething($arg)
    {
        sleep(1);
        // complex job
        return new JobObjectString;
    }
}

class JobObjectString
{
    private $attribute = 'nc';
    
    public function __toString()
    {
        return $this->attribute;
    }
}

class JobObjectSimple
{
    public function doSomething($arg)
    {
        $stdClass = new \stdClass();
        $stdClass->key = 'ok';
        return $stdClass;
    }
}