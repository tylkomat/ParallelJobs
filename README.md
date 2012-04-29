ZF2-Parallel
============

ZF2 parallel provide a fork manager.
Fork manager can create children, run specific jobs and share result.

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

1) Exemple with two process for a simple job :

    $jobObject = new Job();
    $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

    $manager = new \ZFPL\System\Fork\ForkManager();
    $manager->setShareResult(true);
    $manager->doTheJob($job, 'value');
    $manager->createChildren(2);
    $manager->wait();
    $results = $manager->getSharedResults();
    $manager->getContainer()->close();

    echo $results->getChild(1)->getResult();

Run in command line :

    php index.php // display "ok"

2) Exemple with three process for multiple job :

    $jobObject = new Job();
    $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

    $manager = new \ZFPL\System\Fork\ForkManager();
    $manager->setShareResult(true);
    $manager->doTheJob($job, 'value');
    $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
    $manager->createChildren(2);
    $manager->wait();
    $results = $manager->getSharedResults();
    $manager->getContainer()->close();

    echo $results->getChild(1)->getResult();
    
Run in command line :

    php index.php // display "ko"

3) Exemple with manage start :

    $jobObject = new Job();
    $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

    $manager = new \ZFPL\System\Fork\ForkManager();
    $manager->setShareResult(true);
    $manager->setAutoStart(false);
    $manager->doTheJob($job, 'value');
    $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
    $manager->createChildren(2);
    // do multiple tasks
    $manager->start();
    $manager->wait();
    $results = $manager->getSharedResults();
    $manager->getContainer()->close();

    echo $results->getChild(1)->getResult();

Run in command line :

    php index.php // display "ko"

4) Exemple with timeout and unshare :

    $jobObject = new Job();
    $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

    $manager = new \ZFPL\System\Fork\ForkManager();
    $manager->doTheJob($job, 'value');
    $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
    $manager->timeout(60);
    $manager->createChildren(2);
    $manager->wait();

    echo $manager->isStopped();

Run in command line :

    php index.php // display "0"

4) Exemple with stop children :

    $jobObject = new Job();
    $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

    $manager = new \ZFPL\System\Fork\ForkManager();
    $manager->doTheJob($job, 'value');
    $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
    $manager->timeout(60);
    $manager->createChildren(2);
    // do multiple tasks
    $manager->closeChildren();

Run in command line :

    php index.php