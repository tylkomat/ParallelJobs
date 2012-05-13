<?php

/*
 * This file is part of the ZFPJ package.
 * @copyright Copyright (c) 2012 Blanchon Vincent - France (http://developpeur-zend-framework.fr - blanchon.vincent@gmail.com)
 */

namespace ZFPJ\System\Fork\Storage;

interface StorageInterface
{
    /**
     * Read contents related $uid fork
     * @param int
     */
    public function read($uid);
    
    /**
     * Write contents related $uid fork
     * @param int $uid
     * @param string $str
     */
    public function write($uid, $mixed);
    
    /**
     * Close storage
     * @param int
     */
    public function close();
    
    /**
     * Get max bloc allow
     * @return int
     */
    public function max();
}