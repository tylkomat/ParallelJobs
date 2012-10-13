<?php

require_once DIR_ZF2 . 'Zend/Loader/AutoloaderFactory.php';
Zend\Loader\AutoloaderFactory::factory(array(
    'Zend\Loader\StandardAutoloader' => array(
        'autoregister_zf' => true,
        'namespaces' => array(
            'SimpleMemoryShared' => DIR_SMS . 'src/SimpleMemoryShared', // write your own path to run the tests !
            'ParallelJobs' => __DIR__ . '/../src/ParallelJobs',
            'ParallelJobsTest' => __DIR__ . '/ParallelJobs',
        ),
    ),
));