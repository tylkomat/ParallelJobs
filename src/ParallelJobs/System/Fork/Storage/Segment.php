<?php

/*
 * This file is part of the ParallelJobs package.
 * @copyright Copyright (c) 2012 Blanchon Vincent - France (http://developpeur-zend-framework.fr - blanchon.vincent@gmail.com)
 */

namespace ParallelJobs\System\Fork\Storage;

class Segment implements StorageInterface
{
    /**
     * identifier
     * @var string
     */
    protected $identifier;
    
    /**
     *
     * @var mixed 
     */
    protected $memory;
    
    /**
     * Bloc size
     * @var int 
     */
    protected $segmentSize = 256;
    
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
        $this->identifier = $identifier;
    }
    
    /**
     * Memory alloc
     */
    public function alloc()
    {
        if(null !== $this->memory) {
            return;
        }
        $this->memory = shmop_open(ftok(__FILE__, $this->identifier), "c", 0644, $this->segmentSize);
    }
    
    /**
     * Read contents related $uid fork
     * @param int
     */
    public function read($uid)
    {   
        $this->alloc();
        $str = shmop_read($this->memory, $uid*$this->blocSize, $this->blocSize);
        return trim($str);
    }
    
    /**
     * Write contents related $uid fork
     * @param int
     */
    public function write($uid, $mixed)
    {   
        $this->alloc();
        if(is_object($mixed) && method_exists($mixed, '__toString')) {
            $mixed = $mixed->__toString();
        }
        if(!is_string($mixed)) {
            $mixed = '';
        }
        $limit = $this->getBlocSize();
        $str = mb_substr($mixed, 0, $limit);
        $str = str_pad($str, $this->blocSize);
        return shmop_write($this->memory, $str, $uid*$this->blocSize);
    }
    
    /**
     * Close segment
     * @param int
     */
    public function close()
    {   
        if(null === $this->memory) {
            return;
        }
        shmop_close($this->memory);
        $this->memory = null;
    }
    
    /**
     * Get max bloc allow
     */
    public function max()
    {
        return floor($this->segmentSize/$this->blocSize);
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