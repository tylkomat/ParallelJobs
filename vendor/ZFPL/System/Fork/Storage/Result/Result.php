<?php

namespace ZFPL\System\Fork\Storage\Result;

class Result implements ResultInterface
{
    /**
     * uid
     * @var int
     */
    protected $uid;
    
    /**
     * pid
     * @var int
     */
    protected $pid;
    
    /**
     * result
     * @var string
     */
    protected $result;
    
    /**
     * Get result
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }
    
    /**
     * Set result
     * @param string
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }
    
    /**
     * Get uid
     * @return string
     */
    public function getUid()
    {
        return $this->pid;
    }
    
    /**
     * Set result
     * @param string
     */
    public function setUid($pid)
    {
        $this->pid = $pid;
        return $this;
    }
    
    /**
     * Get pid
     * @return string
     */
    public function getPid()
    {
        return $this->uid;
    }
    
    /**
     * Set result
     * @param string
     */
    public function setPid($uid)
    {
        $this->uid = $uid;
        return $this;
    }
}