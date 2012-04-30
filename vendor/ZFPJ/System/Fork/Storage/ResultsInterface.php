<?php

/*
 * This file is part of the ZFPJ package.
 * @copyright Copyright (c) 2012 Blanchon Vincent - France (http://developpeur-zend-framework.fr - blanchon.vincent@gmail.com)
 */

namespace ZFPJ\System\Fork\Storage;

interface ResultsInterface
{
    /**
     * Get child
     * @param int
     */
    public function getChild($num);
}