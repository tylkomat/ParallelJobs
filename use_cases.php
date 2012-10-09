<?php

require 'classes.php';

chdir(dirname(__DIR__));
require_once (getenv('ZF2_PATH') ?: 'vendor/ZendFramework/library') . '/Zend/Loader/AutoloaderFactory.php';
Zend\Loader\AutoloaderFactory::factory();
Zend\Loader\AutoloaderFactory::factory(array('Zend\Loader\ClassMapAutoloader' => array(include 'config/autoload_classmap.php')));

require 'use_cases/exemple1.php';
require 'use_cases/exemple2.php';
require 'use_cases/exemple3.php';
require 'use_cases/exemple4.php';
require 'use_cases/exemple5.php';
require 'use_cases/exemple6.php';
require 'use_cases/exemple7.php';
require 'use_cases/exemple8.php';
require 'use_cases/exemple9.php';