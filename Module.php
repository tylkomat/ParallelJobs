<?php

namespace ParallelJobs;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface,
        ServiceProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/config/autoload_classmap.php'
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'ForkManager' => function($sm) {
                    $config = $sm->get('Config');
                    $config = $config['fork_manager'];
                    $manager = new System\Fork\ForkManager();
                    $manager->setShareResult($config['share_result']);
                    $manager->setAutoStart($config['auto_start']);
                    $container = $sm->get($config['container']);
                    $manager->setContainer($container);
                    return $manager;
                },
                'ForkManagerFileContainer' => function($sm) {
                    $config = $sm->get('Config');
                    $config = $config['fork_manager_file_container'];
                    $container = new System\Fork\Storage\File($config['dir']);
                    return $container;
                },
                'ForkManagerMemcachedContainer' => function($sm) {
                    $config = $sm->get('Config');
                    $config = $config['fork_manager_memcached_container'];
                    $container = new System\Fork\Storage\Memcached($config['config']);
                    return $container;
                },
                'ForkManagerSegmentContainer' => function($sm) {
                    $config = $sm->get('Config');
                    $config = $config['fork_manager_segment_container'];
                    $container = new System\Fork\Storage\Segment($config['identifier']);
                    return $container;
                },
            ),
        );
    }
}
