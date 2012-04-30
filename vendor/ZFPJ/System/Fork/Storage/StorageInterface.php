<?php

/*
 * This file is part of the ZFPJ package.
 * @copyright Copyright (c) 2012 Blanchon Vincent - France (http://developpeur-zend-framework.fr - blanchon.vincent@gmail.com)
 */

namespace ZFPJ\System\Fork\Storage;

interface StorageInterface
{
    /**
     * Read fork uid
     * @param int
     */
    public function read($uid);
    
    /**
     * Write fork uid
     * @param int
     */
    public function write($uid, $pid);
    
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