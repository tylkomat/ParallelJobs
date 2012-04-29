<?php

namespace ZFPJ\System\Fork\Storage;

class Segment implements StorageInterface
{
    /**
     *
     * @var mixed 
     */
    protected $memory;
    
    /**
     * Bloc size
     * @var int 
     */
    protected $segmentSize = 64;
    
    /**
     * Bloc size
     * @var int 
     */
    protected $blocSize = 8;
    
    /**
     * Construct segment memory
     * @param type $identifier 
     */
    public function __construct($identifier = 'Z')
    {
        $this->memory = shmop_open(ftok(__FILE__, $identifier), "c", 0644, $this->segmentSize);
    }
    
    /**
     * Read fork uid
     * @param int
     */
    public function read($uid)
    {   
        $str = shmop_read($this->memory, $uid*$this->blocSize, $this->blocSize);
        return trim($str);
    }
    
    /**
     * Write fork uid
     * @param int
     */
    public function write($uid, $str)
    {   
        $str = str_pad($str, $this->blocSize);
        return shmop_write($this->memory, $str, $uid*$this->blocSize);
    }
    
    /**
     * Close segment
     * @param int
     */
    public function close()
    {   
        return shmop_close($this->memory);
    }
    
    /**
     * Get segment memory
     * @return type 
     */
    public function getSegment()
    {
        return $this->memory;
    }
    
    /**
     * Get bloc size
     * @return int 
     */
    public function getBlocSize()
    {
        return $this->blocSize;
    }
    
    /**
     * Set bloc size
     * @param int 
     */
    public function setBlocSize($size)
    {
        $this->blocSize = $size;
        return $this;
    }
    
    /**
     * Get segment size
     * @return int 
     */
    public function getSegmentSize()
    {
        return $this->segmentSize;
    }
    
    /**
     * Set segment size
     * @param int 
     */
    public function setSegmentSize($size)
    {
        $this->segmentSize = $size;
        return $this;
    }
}

?>
