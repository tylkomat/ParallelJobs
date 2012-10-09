<?php

$jobObject = new Job();
$jobObjectSimple = new JobObjectSimple();
$job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));
$job2 = new \Zend\Stdlib\CallbackHandler(array($jobObjectSimple, 'doSomething'));

$manager = new \ParallelJobs\System\Fork\ForkManager();
$manager->setContainer(new \ParallelJobs\System\Fork\Storage\File());
$manager->setShareResult(true);
$manager->doTheJob($job, 'value');
$manager->doTheJobChild(1, $job2, array('value 1', 'value 2'));
$manager->createChildren(2);
$manager->wait();
$results = $manager->getSharedResults();

echo get_class($results->getChild(1)->getResult());
echo ", ";
echo $results->getChild(2)->getResult();
echo "\n";