<?php

namespace ZFPJTest\System\Fork;

class JobObject
{
    public function doSomething($arg)
    {
        sleep(1);
        // complex job
        return new JobObjectString;
    }
}

class JobInvalidObject
{
    public function doSomething($arg)
    {
        sleep(1);
        // complex job
        return new JobObjectInvalidString;
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

class JobObjectInvalidString
{
    protected $attribut = 1;
}