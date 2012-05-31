<?php

/*
 * This file is part of the ZFPJ package.
 * @copyright Copyright (c) 2012 Blanchon Vincent - France (http://developpeur-zend-framework.fr - blanchon.vincent@gmail.com)
 */

namespace ZFPJ\System\Fork\Storage;

class File implements StorageInterface
{
    /**
     * Directory storage
     * @var string
     */
    protected $dir;
    
    /**
     * List of files
     * @var array
     */
    protected $files = array();
    
    /**
     *
     * @param string $dir 
     */
    public function __construct($dir = null)
    {   
        if($dir == null) {
            $dir = __DIR__ . '/tmp';
        }
        $this->dir = $dir;
    }
    
    /**
     * Read contents related $uid fork
     * @param int
     */
    public function read($uid)
    {
        if(!file_exists($this->dir. '/'. $uid)) {
            return false;
        }
        $contents = file_get_contents($this->dir. '/'. $uid);
        return unserialize($contents);
    }
    
    /**
     * Write contents related $uid fork
     * @param int
     */
    public function write($uid, $mixed)
    {
        $fp = @fopen($this->dir. '/'. $uid, 'w+');
        if(!$fp) {
            return false;
        }
        $r = fwrite($fp, serialize($mixed));
        fclose($fp);
        $this->files[] = $this->dir. '/'. $uid;
        return $r;
    }
    
    /**
     * Close storage
     * @param int
     */
    public function close()
    {   
        foreach($this->files as $file) {
            @unlink($file);
        }
    }
    
    /**
     * Get max bloc allow
     * @return int
     */
    public function max()
    {
        return intval(ini_get('memory_limit'))*1024*1024 / 256; // 256 bytes per file
    }
}