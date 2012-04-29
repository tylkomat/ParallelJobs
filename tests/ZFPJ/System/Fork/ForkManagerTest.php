<?php

namespace ZFPJTest\System\Fork;

use PHPUnit_Framework_TestCase as TestCase,
    Zend\Stdlib\CallbackHandler,
    ZFPJ\System\Fork\ForkManager;

class ManagerTest extends TestCase
{
    public function setUp()
    {
        require_once realpath(__DIR__.'/TestAsset/Job.php');
    }
    
    protected function mockHandler()
    {
        $errorH = $this->getMock ('ErrorHandler', array ('error_handler'));
        $errorH->expects ($this->atLeastOnce())->method ('error_handler');
        set_error_handler (array($errorH, 'error_handler'));
    }
    
    public function testSimpleJob()
    {
        $jobObject = new Job();
        $job = new CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new ForkManager();
        $manager->setShareResult(true);
        $manager->doTheJob($job, 'value');
        $manager->createChildren(2);
        $manager->wait();
        $results = $manager->getSharedResults();
        $manager->getContainer()->close();
        $this->assertEquals('ok', $results->getChild(1)->getResult());
    }
    
    public function testMultipleJob()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->setShareResult(true);
        $manager->doTheJob($job, 'value');
        $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
        $manager->createChildren(2);
        $manager->wait();
        $results = $manager->getSharedResults();
        $manager->getContainer()->close();
        $this->assertEquals('ko', $results->getChild(1)->getResult());
        $this->assertEquals('ok', $results->getChild(2)->getResult());
    }
    
    public function testMultipleJobStart()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->setShareResult(true);
        $manager->setAutoStart(false);
        $manager->doTheJob($job, 'value');
        $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
        $manager->createChildren(2);
        $manager->start();
        $manager->wait();
        $results = $manager->getSharedResults();
        $manager->getContainer()->close();
        $this->assertEquals('ko', $results->getChild(1)->getResult());
        $this->assertEquals('ok', $results->getChild(2)->getResult());
    }
    
    public function testMultipleJobBadStart()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->doTheJob($job, 'value');
        $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
        $manager->createChildren(2);
        $this->setExpectedException('ZFPJ\System\Fork\Exception\RuntimeException');
        $manager->start();
    }
    
    public function testMultipleJobTimeoutUnshareStopped()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->doTheJob($job, 'value');
        $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
        $manager->timeout(1);
        $manager->createChildren(2);
        $manager->wait();
        $this->assertEquals(true, $manager->isStopped());
    }
    
    public function testMultipleJobTimeoutUnshareUnStopped()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->doTheJob($job, 'value');
        $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
        $manager->timeout(30);
        $manager->createChildren(2);
        $manager->wait();
        $this->assertEquals(false, $manager->isStopped());
    }
    
    public function testMultipleJobNotShare()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->setShareResult(true);
        $this->assertEquals(false, $manager->getSharedResults());
        $manager->setShareResult(false);
        $manager->doTheJob($job, 'value');
        $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
        $manager->createChildren(2);
        $manager->wait();
        $this->assertEquals(false, $manager->getSharedResults());
    }
    
    public function testMultipleJobShareAfterStarted()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->setShareResult(true);
        $manager->doTheJob($job, 'value');
        $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
        $manager->createChildren(2);
        $this->setExpectedException('ZFPJ\System\Fork\Exception\RuntimeException');
        $manager->setShareResult(false);
        $manager->wait();
    }
    
    public function testMultipleJobShareNotFinished()
    {
        $this->mockHandler();
        $jobObject = new Job();

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->setShareResult(true);
        $manager->doTheJob(array($jobObject, 'doSomething'), 'value');
        $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
        $manager->createChildren(2);
        $this->assertEquals(false, $manager->getSharedResults());
        
        restore_error_handler ();
    }
    
    public function testMultipleJobMainKilled()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->doTheJob(array($jobObject, 'doOtherSomething'), 'value');
        $this->assertEquals(false, $manager->isStopped());
        $manager->broadcast(SIGINT);
        $this->assertEquals(true, $manager->isStopped());
    }
    
    public function testMultipleLimitNumJobsShare()
    {
        $this->setExpectedException('ZFPJ\System\Fork\Exception\RuntimeException');
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->setShareResult(true);
        $manager->doTheJob($job, 'value');
        $this->assertEquals(32, $manager->getContainer()->max());
        $manager->createChildren(40);
        $manager->wait();
    }
    
    public function testMultipleNoLimitNumJobsUnshare()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->doTheJob($job, 'value');
        $manager->createChildren(40);
        $manager->wait();
    }
    
    public function testMultipleIncreaseLimitNumJobs()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->setShareResult(true);
        $manager->doTheJob($job, 'value');
        $manager->getContainer()->setBlocSize(4);
        $manager->getContainer()->setSegmentSize(256);
        $manager->createChildren(40);
        $manager->wait();
        $this->assertEquals(64, $manager->getContainer()->max());
        $results = $manager->getSharedResults();
        $this->assertEquals('ok', $results->getChild(40)->getResult());
        $this->assertEquals(40, $results->getChild(40)->getUid());
    }
    
    public function testMultipleJobsRewind()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));
        $job2 = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doOtherSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->setShareResult(true);
        $manager->doTheJob($job, 'value');
        $manager->createChildren(2);
        $manager->wait();
        $results = $manager->getSharedResults();
        $this->assertEquals('ok', $results->getChild(1)->getResult());
        $this->assertEquals('ok', $results->getChild(2)->getResult());
        $manager->doTheJob($job2, array('value', 'value 2'));
        $manager->rewind()->start();
        $manager->wait();
        $results = $manager->getSharedResults();
        $this->assertEquals(true, $manager->isForkParent());
        $this->assertEquals('ko', $results->getChild(1)->getResult());
        $this->assertEquals('ko', $results->getChild(2)->getResult());
        $manager->doTheJobChild(1, $job, 'value');
        $manager->rewind()->start();
        $manager->wait();
        $results = $manager->getSharedResults();
        $this->assertEquals(true, $manager->isForkParent());
        $this->assertEquals('ok', $results->getChild(1)->getResult());
        $this->assertEquals('ko', $results->getChild(2)->getResult());
    }
    
    public function testMultipleJobsRewindAfterStart()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));
        $job2 = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doOtherSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->doTheJob($job, 'value');
        $manager->createChildren(2);
        $this->setExpectedException('ZFPJ\System\Fork\Exception\RuntimeException');
        $manager->rewind();
        $manager->wait();
    }
    
    public function testMultipleJobsStartAndRewind()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));
        $job2 = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doOtherSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $this->setExpectedException('ZFPJ\System\Fork\Exception\RuntimeException');
        $manager->rewind();
    }
    
    public function testMultipleJobsRewindWithChangeShare()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));
        $job2 = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doOtherSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->setShareResult(true);
        $manager->doTheJob($job, 'value');
        $manager->createChildren(2);
        $manager->wait();
        $results = $manager->getSharedResults();
        $this->assertEquals('ok', $results->getChild(1)->getResult());
        $this->assertEquals('ok', $results->getChild(2)->getResult());
        $manager->doTheJob($job2, array('value', 'value 2'));
        $manager->rewind()->setShareResult(false)->start();
        $manager->wait();
        $manager->doTheJobChild(1, $job, 'value');
        $manager->rewind()->setShareResult(true)->start();
        $manager->wait();
        $results = $manager->getSharedResults();
        $this->assertEquals('ok', $results->getChild(1)->getResult());
        $this->assertEquals('ko', $results->getChild(2)->getResult());
    }
    
    public function testMultipleJobsRewindWithBadChangeShare()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));
        $job2 = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doOtherSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->setShareResult(true);
        $manager->doTheJob($job, 'value');
        $manager->createChildren(2);
        $manager->wait();
        $results = $manager->getSharedResults();
        $this->assertEquals('ok', $results->getChild(1)->getResult());
        $this->assertEquals('ok', $results->getChild(2)->getResult());
        $manager->doTheJob($job2, array('value', 'value 2'));
        $manager->rewind()->setShareResult(false)->start();
        $manager->wait();
        $this->setExpectedException('ZFPJ\System\Fork\Exception\RuntimeException');
        $manager->setShareResult(true);
    }
    
    public function testMultipleJobsRewindWithTimeout()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));
        $job2 = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doOtherSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->doTheJob($job, 'value');
        $manager->timeout(3);
        $manager->createChildren(2);
        $manager->wait();
        
        for($i = 0; $i < 3; $i++) {
            $manager->rewind()->start();
            $manager->wait();
            $this->assertEquals(false, $manager->isStopped());
        }
        $this->assertEquals(false, $manager->isStopped());
    }
    
    public function testMultipleJobsInLoop()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));
        $job2 = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doOtherSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->setAutoStart(false);
        $manager->setShareResult(true);
        $manager->createChildren(2);
        for($i = 0; $i < 3; $i++) {
            if($i%2) {
                $manager->doTheJob(array($jobObject, 'doSomething'), 'value');
            }
            else {
                $manager->doTheJob(array($jobObject, 'doOtherSomething'), array('value', 'value 2'));
            }
            $manager->start();
            $manager->wait();
            $results = $manager->getSharedResults();
            if($i%2) {
                $this->assertEquals('ok', $results->getChild(1)->getResult());
            }
            else {
                $this->assertEquals('ko', $results->getChild(2)->getResult());
            }
            $manager->rewind();
        }
    }
}