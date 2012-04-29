ZF2 Parallel Jobs
============

Version 1.0beta Created by [Vincent Blanchon](http://developpeur-zend-framework.fr/)

Introduction
------------

ZF2 parallel provide a fork manager.
Fork manager can create children, run specific jobs and share result.


Fork manager usage
------------

Class Job exemple :

    class Job
    {
        public function doSomething($arg)
        {
            sleep(2);
            // complex job
            return 'ok';
        }

        public function doOtherSomething($arg1, $arg2)
        {
            sleep(1);
            // bad job
            return 'ko';
        }
    }

1) Exemple with one process for a simple job :
    
    $jobObject = new Job();
    
    $manager = new \ZFPJ\System\Fork\ForkManager();
    $manager->setShareResult(true);
    $manager->doTheJob(array($jobObject, 'doSomething'), 'value');
    $manager->createChildren(1);
    $manager->wait();
    $results = $manager->getSharedResults();

    echo $results->getChild(1)->getResult();

Run in command line :

    php index.php // display "ok"

2) Exemple with two process for multiple job :

    $jobObject = new Job();
    $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));
    $job2 = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doOtherSomething'));

    $manager = new \ZFPJ\System\Fork\ForkManager();
    $manager->setShareResult(true);
    $manager->doTheJob($job, 'value');
    $manager->doTheJobChild(1, $job2, array('value 1', 'value 2'));
    $manager->createChildren(2);
    $manager->wait();
    $results = $manager->getSharedResults();

    echo $results->getChild(1)->getResult();
    echo ", ";
    echo $results->getChild(2)->getResult();
    
Run in command line :

    php index.php // display "ko, ok"

3) Exemple with several job :

    $jobObject = new Job();
    $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

    $manager = new \ZFPJ\System\Fork\ForkManager();
    $manager->setShareResult(true);
    $manager->doTheJob($job, 'value');
    $manager->createChildren(10);
    $manager->wait();
    $results = $manager->getSharedResults();

    echo $results->getChild(1)->getResult();
    echo ", ";
    echo $results->getChild(10)->getResult();
    
Run in command line :

    php index.php // display "ok, ok"

4) Exemple with manage start :

    $jobObject = new Job();
    $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));
    $job2 = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doOtherSomething'));   

    $manager = new \ZFPJ\System\Fork\ForkManager();
    $manager->setShareResult(true);
    $manager->setAutoStart(false);
    $manager->doTheJob($job, 'value');
    $manager->doTheJobChild(1, $job2, array('value 1', 'value 2'));
    $manager->createChildren(2);
    // do multiple tasks
    $manager->start();
    $manager->wait();
    $results = $manager->getSharedResults();

    echo $results->getChild(1)->getPid() . ':' . $results->getChild(1)->getResult();
    echo ", ";
    echo $results->getChild(2)->getPid() . ':' . $results->getChild(2)->getResult();

Run in command line :

    php index.php // display "8139:ko, 8140:ok"

5) Exemple with timeout and unshare :

    $jobObject = new Job();
    
    $manager = new \ZFPJ\System\Fork\ForkManager();
    $manager->doTheJob(array($jobObject, 'doSomething'), 'value');
    $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
    $manager->timeout(60);
    $manager->createChildren(2);
    $manager->wait();

    echo intval($manager->isStopped());

Run in command line :

    php index.php // display "0"

6) Exemple with stop children :

    $jobObject = new Job();

    $manager = new \ZFPJ\System\Fork\ForkManager();
    $manager->doTheJob(array($jobObject, 'doSomething'), 'value');
    $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
    $manager->timeout(60);
    $manager->createChildren(2);
    // do multiple tasks
    $manager->closeChildren();

Run in command line :

    php index.php

7) Exemple in loop :

    $jobObject = new Job();

    $manager = new \ZFPJ\System\Fork\ForkManager();
    $manager->doTheJob(array($jobObject, 'doSomething'), 'value');
    $manager->setAutoStart(false);
    $manager->createChildren(2);
    for($i = 0; $i < 3; $i++) {
        $manager->start();
        $manager->wait();
        $manager->rewind();
    }

Run in command line :

    php index.php