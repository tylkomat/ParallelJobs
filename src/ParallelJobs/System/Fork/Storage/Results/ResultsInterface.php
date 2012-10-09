<?php

/*
 * This file is part of the ParallelJobs package.
 * @copyright Copyright (c) 2012 Blanchon Vincent - France (http://developpeur-zend-framework.fr - blanchon.vincent@gmail.com)
 */

namespace ParallelJobs\System\Fork\Storage\Results;

interface ResultsInterface
{
    /**
     * Get child
     * @param int
     */
    public function getChild($num);
}