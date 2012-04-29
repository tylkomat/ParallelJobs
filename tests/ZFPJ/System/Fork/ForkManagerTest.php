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
        $this->setExpectedException('ZFPJ\System\Fork\Exception\RuntimeException');
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new \ZFPJ\System\Fork\ForkManager();
        $manager->doTheJob($job, 'value');
        $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
        $manager->createChildren(2);
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
        $manager->doTheJob($job, 'value');
        $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
        $manager->createChildren(2);
        $manager->wait();
        $this->assertEquals(false, $manager->getSharedResults());
    }
    
    public function testMultipleJobShareNotFinished()
    {
        $this->mockHandler();
        $jobObject = new Job();

        $manager = new \ZFPJ\System\Fork\ForkManager();
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
        $manager->broadcast(SIGINT);
        $this->assertEquals(true, $manager->isStopped());
    }
}