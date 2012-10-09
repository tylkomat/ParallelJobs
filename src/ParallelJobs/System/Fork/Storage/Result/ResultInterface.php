<?php

/*
 * This file is part of the ParallelJobs package.
 * @copyright Copyright (c) 2012 Blanchon Vincent - France (http://developpeur-zend-framework.fr - blanchon.vincent@gmail.com)
 */

namespace ParallelJobs\System\Fork\Storage\Result;

interface ResultInterface
{
    /**
     * Get result
     * @param string
     */
    public function getResult();
}