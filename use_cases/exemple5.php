<?php

$jobObject = new Job();
$job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

$manager = new \ParallelJobs\System\Fork\ForkManager();
$manager->setShareResult(true);
$manager->doTheJob($job, 'value');
$manager->createChildren(8);
$manager->wait();
$results = $manager->getSharedResults();

echo $results->getChild(1)->getResult();
echo ", ";
echo $results->getChild(8)->getResult();
echo "\n";