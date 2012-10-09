<?php

$jobObject = new Job();

$manager = new \ParallelJobs\System\Fork\ForkManager();
$manager->doTheJob(array($jobObject, 'doSomething'), 'value');
$manager->setAutoStart(false);
$manager->createChildren(2);
for($i = 0; $i < 3; $i++) {
    $manager->start();
    $manager->wait();
    $manager->rewind();
}
echo "\n";