<?php

$jobObject = new Job();

$manager = new \ParallelJobs\System\Fork\ForkManager();
$manager->doTheJob(array($jobObject, 'doSomething'), 'value');
$manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
$manager->timeout(60);
$manager->createChildren(2);
/*
 *  ... do tasks here ...
 */
$manager->closeChildren();
echo "\n";