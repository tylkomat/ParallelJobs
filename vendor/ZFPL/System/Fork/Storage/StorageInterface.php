<?php

namespace ZFPL\System\Fork\Storage;

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
}

?>
