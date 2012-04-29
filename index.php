<?php

chdir(dirname(__DIR__));
require_once (getenv('ZF2_PATH') ?: 'vendor/ZendFramework/library') . '/Zend/Loader/AutoloaderFactory.php';
Zend\Loader\AutoloaderFactory::factory();
Zend\Loader\AutoloaderFactory::factory(array('Zend\Loader\ClassMapAutoloader' => array(include 'config/autoload_classmap.php')));

$jobObject = new Job();

$manager = new \ZFPJ\System\Fork\ForkManager();
$manager->setShareResult(true);
$manager->doTheJob(array($jobObject, 'doSomething'), 'value');
$manager->createChildren(1);
$manager->wait();
$results = $manager->getSharedResults();

echo $results->getChild(1)->getResult();


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