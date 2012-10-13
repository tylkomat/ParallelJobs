<?php

/**
 * sudo memcached -d -u nobody -m 128 127.0.0.1 -p 11211 // to run memcached for tests
 */

namespace ParallelJobsTest\System\Fork;

use PHPUnit_Framework_TestCase as TestCase;
use ParallelJobs\System\Fork\ForkManager;
use Zend\Stdlib\CallbackHandler;
use Zend\ServiceManager;

class ManagerTest extends TestCase
{
    protected $sm;
    
    protected $sms;
    
    public function setUp()
    {
        require_once realpath(__DIR__.'/TestAsset/Job.php');
        require_once realpath(__DIR__.'/TestAsset/JobObject.php');
        require_once realpath(__DIR__.'/TestAsset/JobLongString.php');
        
        // load ParallelJobs config
        require_once __DIR__ . '/../../../../Module.php';
        $module = new \ParallelJobs\Module();
        $serviceConfig = $module->getServiceConfig();
        $config = include __DIR__ . '/../../../../config/module.config.php';
        
        // load SimpleMemoryShared config
        if(null !== DIR_SMS) {
            require_once DIR_SMS . '/Module.php';
            $module = new \SimpleMemoryShared\Module();
            $serviceConfig = array_replace_recursive($serviceConfig, $module->getServiceConfig());
            $config = array_replace_recursive($config, include DIR_SMS . '/config/module.config.php');
        }
        
        $this->sm = new ServiceManager\ServiceManager(new ServiceManager\Config($serviceConfig));
        $this->sm->setService('Config', $config);
        $this->sm->setAllowOverride(true);
        
        if(null !== DIR_SMS) {
            $this->sms = $this->sm->get('SimpleMemoryShared');
        }
    }
    
    protected function mockHandler()
    {
        $errorH = $this->getMock('ErrorHandler', array ('error_handler'));
        $errorH->expects($this->atLeastOnce())->method ('error_handler');
        set_error_handler (array($errorH, 'error_handler'));
    }
    
    // ZF2 specifics tests
    public function testCanRetrieveFactory()
    {
        $manager = $this->sm->get('ForkManager');
        $this->assertEquals('ParallelJobs\System\Fork\ForkManager', get_class($manager));
        $manager = $this->sm->get('ParallelJobsManager');
        $this->assertEquals('ParallelJobs\System\Fork\ForkManager', get_class($manager));
    }
    
    public function testSimpleJob()
    {
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $jobObject = new Job();
        $job = new CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
        $manager->doTheJob($job, 'value');
        $manager->createChildren(2);
        $manager->wait();
        $results = $manager->getSharedResults();
        $manager->getStorage()->close();
        $this->assertEquals('ok', $results->getChild(1)->getResult());
    }
    
    public function testMultipleJob()
    {
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
        $manager->doTheJob($job, 'value');
        $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
        $manager->createChildren(2);
        $manager->wait();
        $results = $manager->getSharedResults();
        $manager->getStorage()->close();
        $this->assertEquals('ko', $results->getChild(1)->getResult());
        $this->assertEquals('ok', $results->getChild(2)->getResult());
    }
    
    public function testMultipleJobStart()
    {
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
        $manager->setAutoStart(false);
        $manager->doTheJob($job, 'value');
        $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
        $manager->createChildren(2);
        $manager->start();
        $manager->wait();
        $results = $manager->getSharedResults();
        $manager->getStorage()->close();
        $this->assertEquals('ko', $results->getChild(1)->getResult());
        $this->assertEquals('ok', $results->getChild(2)->getResult());
    }
    
    public function testMultipleJobBadStart()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new ForkManager();
        $manager->doTheJob($job, 'value');
        $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
        $manager->createChildren(2);
        $this->setExpectedException('ParallelJobs\System\Fork\Exception\RuntimeException');
        $manager->start();
    }
    
    public function testMultipleJobTimeoutUnshareStopped()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new ForkManager();
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

        $manager = new ForkManager();
        $manager->doTheJob($job, 'value');
        $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
        $manager->timeout(30);
        $manager->createChildren(2);
        $manager->wait();
        $this->assertEquals(false, $manager->isStopped());
    }
    
    public function testMultipleJobNotShare()
    {
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
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
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
        $manager->doTheJob($job, 'value');
        $manager->doTheJobChild(1, array($jobObject, 'doOtherSomething'), array('value 1', 'value 2'));
        $manager->createChildren(2);
        $this->setExpectedException('ParallelJobs\System\Fork\Exception\RuntimeException');
        $manager->setShareResult(false);
        $manager->wait();
    }
    
    public function testMultipleJobShareNotFinished()
    {
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $this->mockHandler();
        $jobObject = new Job();

        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
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

        $manager = new ForkManager();
        $manager->doTheJob(array($jobObject, 'doOtherSomething'), 'value');
        $this->assertEquals(false, $manager->isStopped());
        $manager->broadcast(SIGINT);
        $this->assertEquals(true, $manager->isStopped());
    }
    
    public function testMultipleLimitNumJobsShare()
    {
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $this->setExpectedException('ParallelJobs\System\Fork\Exception\RuntimeException');
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
        $manager->setStorage('segment');
        $manager->doTheJob($job, 'value');
        $this->assertEquals(true, $manager->getStorage()->canAllowBlocsMemory(32));
        $manager->createChildren(40);
        $manager->wait();
    }
    
    public function testMultipleNoLimitNumJobsUnshare()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new ForkManager();
        $manager->doTheJob($job, 'value');
        $manager->createChildren(40);
        $manager->wait();
    }
    
    public function testMultipleIncreaseLimitNumJobs()
    {
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
        $manager->doTheJob($job, 'value');
        $manager->setStorage('segment');
        $manager->getStorage()->setBlocSize(4);
        $manager->getStorage()->setSegmentSize(256);
        $manager->createChildren(40);
        $manager->wait();
        $this->assertEquals(true, $manager->getStorage()->canAllowBlocsMemory(64));
        $results = $manager->getSharedResults();
        $this->assertEquals('ok', $results->getChild(40)->getResult());
        $this->assertEquals(40, $results->getChild(40)->getUid());
    }
    
    public function testMultipleJobsRewind()
    {
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));
        $job2 = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doOtherSomething'));

        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
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

        $manager = new ForkManager();
        $manager->doTheJob($job, 'value');
        $manager->createChildren(2);
        $this->setExpectedException('ParallelJobs\System\Fork\Exception\RuntimeException');
        $manager->rewind();
        $manager->wait();
    }
    
    public function testMultipleJobsStartAndRewind()
    {
        $manager = new ForkManager();
        $this->setExpectedException('ParallelJobs\System\Fork\Exception\RuntimeException');
        $manager->rewind();
    }
    
    public function testMultipleJobsRewindWithChangeShare()
    {
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));
        $job2 = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doOtherSomething'));

        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
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
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));
        $job2 = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doOtherSomething'));

        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
        $manager->doTheJob($job, 'value');
        $manager->createChildren(2);
        $manager->wait();
        $results = $manager->getSharedResults();
        $this->assertEquals('ok', $results->getChild(1)->getResult());
        $this->assertEquals('ok', $results->getChild(2)->getResult());
        $manager->doTheJob($job2, array('value', 'value 2'));
        $manager->rewind()->setShareResult(false)->start();
        $manager->wait();
        $this->setExpectedException('ParallelJobs\System\Fork\Exception\RuntimeException');
        $manager->setMemoryManager($this->sms)->setShareResult(true);
    }
    
    public function testMultipleJobsRewindWithTimeout()
    {
        $jobObject = new Job();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'doSomething'));

        $manager = new ForkManager();
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
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $jobObject = new Job();
        
        $manager = new ForkManager();
        $manager->setAutoStart(false);
        $manager->setMemoryManager($this->sms)->setShareResult(true);
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
    
    public function testMultipleJobsWhithShareObject()
    {
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $job = new Job();
        $jobObject = new JobObject();

        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
        $manager->setStorage('segment');
        $manager->doTheJob(array($jobObject, 'doSomething'), 'value');
        $manager->doTheJobChild(2, array($job, 'doSomething'), 'value');
        $manager->createChildren(2);
        $manager->wait();
        $results = $manager->getSharedResults();
        
        $this->assertEquals('nc', $results->getChild(1)->getResult());
        $this->assertEquals('ok', $results->getChild(2)->getResult());
    }
    
    public function testMultipleJobsWhithShareObjectString()
    {
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $jobObject = new JobInvalidObject();

        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
        $manager->setStorage('segment');
        $manager->doTheJob(array($jobObject, 'doSomething'), 'value');
        $manager->createChildren(2);
        $manager->wait();
        $results = $manager->getSharedResults();
        
        $this->assertEquals('', $results->getChild(1)->getResult());
        $this->assertEquals('', $results->getChild(2)->getResult());
    }
    
    public function testMultipleJobsWhithShareLongString()
    {
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $jobObject = new JobLongString();

        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
        $manager->setStorage('segment');
        $manager->doTheJob(array($jobObject, 'doSomething'), 'value');
        $manager->createChildren(2);
        $manager->wait();
        $results = $manager->getSharedResults();
        $size = $manager->getStorage()->getBlocSize();
        $this->assertEquals($size, strlen($results->getChild(1)->getResult()));
        $this->assertEquals(substr('azertyuiopazertyuiopazertyuiopazertyuiop', 0, $size), $results->getChild(1)->getResult());
    }
    
    public function testMultipleJobsWhithFileContainer()
    {
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $jobObject = new JobObject();

        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
        $manager->setStorage('file');
        $manager->doTheJob(array($jobObject, 'doSomething'), 'value');
        $manager->createChildren(1);
        $manager->wait();
        $results = $manager->getSharedResults();
        
        $this->assertEquals(true, is_object($results->getChild(1)->getResult()));
        $this->assertEquals('SimpleMemoryShared\Storage\File', get_class($manager->getStorage()));
        $this->assertInstanceOf('ParallelJobsTest\System\Fork\JobObjectString', $results->getChild(1)->getResult());
    }
    
    public function testMultipleJobsWhithBadFileContainer()
    {
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $this->setExpectedException('SimpleMemoryShared\Storage\Exception\RuntimeException');
        $manager = new ForkManager();
        $manager->setStorage(new \SimpleMemoryShared\Storage\File('./unknow-directory'));
    }
    
    public function testMultipleJobsWhithMemcachedContainer()
    {
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $jobObject = new JobObject();

        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
        $manager->setStorage('memcached', array('host' => '127.0.0.1','port' => 11211));
        $manager->doTheJob(array($jobObject, 'doSomething'), 'value');
        $manager->createChildren(1);
        $manager->wait();
        $results = $manager->getSharedResults();
        
        $this->assertEquals(true, is_object($results->getChild(1)->getResult()));
        $this->assertEquals('SimpleMemoryShared\Storage\Memcached', get_class($manager->getStorage()));
        $this->assertInstanceOf('ParallelJobsTest\System\Fork\JobObjectString', $results->getChild(1)->getResult());
    }
    
    public function testWithoutJobRegister()
    {
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $jobObject = new JobObjectReturnParam();

        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
        $manager->setStorage('memcached');
        $object = new \stdClass();
        $object->key = 'value';
        $manager->doTheJob(array($jobObject, 'doSomething'), $object);
        $manager->createChildren(1);
        $manager->wait();
        $results = $manager->getSharedResults();
        
        $child1 = $results->getChild(1)->getResult();
        $this->assertEquals(true, is_object($child1));
        $this->assertEquals('value', $child1->key);
        $this->assertEquals('SimpleMemoryShared\Storage\Memcached', get_class($manager->getStorage()));
    }
    
    public function testCanRegisterObjectInJobParams()
    {
        if(null === DIR_SMS) {
            $this->markTestSkipped('The share of result is not activate.');
        }
        $manager = new ForkManager();
        $manager->setMemoryManager($this->sms)->setShareResult(true);
        $manager->createChildren(2);
        $manager->wait();
        $results = $manager->getSharedResults();
        $manager->getStorage()->close();
        $this->assertEquals(null, $results->getChild(1)->getResult());
    }
}