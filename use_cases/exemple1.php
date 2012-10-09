<?php

$jobObject = new Job();

$manager = new \ParallelJobs\System\Fork\ForkManager();
$manager->setShareResult(true);
$manager->doTheJob(array($jobObject, 'doSomething'), 'value');
$manager->createChildren(1);
$manager->wait();
$results = $manager->getSharedResults();

echo $results->getChild(1)->getResult();
echo "\n";