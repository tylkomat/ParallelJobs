<?php
/**
 * Setup autoloading
 */
function ZendTest_Autoloader($class) 
{
    $class = ltrim($class, '\\');

    if (!preg_match('#^(Zend(Test)?|ZFPL(Test)?|PHPUnit)(\\\\|_)#', $class)) {
        return false;
    }

    // $segments = explode('\\', $class); // preg_split('#\\\\|_#', $class);//
    $segments = preg_split('#[\\\\_]#', $class); // preg_split('#\\\\|_#', $class);//
    $ns       = array_shift($segments);

    switch ($ns) {
        case 'Zend':
            $file = dirname(__DIR__) . '/vendor/ZendFramework/library/Zend/';
            break;
        case 'ZFPL':
            $file = dirname(__DIR__) . '/vendor/ZFPL/';
            break;
        case 'ZendTest':
            // temporary fix for ZendTest namespace until we can migrate files 
            // into ZendTest dir
            $file = __DIR__ . '/Zend/';
        case 'ZFPLTest\Module':
            // temporary fix for ZendTest namespace until we can migrate files 
            // into ZendTest dir
            $file = __DIR__ . '/ZFPL/';
            break;
        default:
            $file = false;
            break;
    }

    if ($file) {
        $file .= implode('/', $segments) . '.php';
        if (file_exists($file)) {
            return include_once $file;
        }
    }

    $segments = explode('_', $class);
    $ns       = array_shift($segments);

    switch ($ns) {
        case 'Zend':
            $file = dirname(__DIR__) . '/vendor/ZendFramework/library/Zend/';
            break;
        case 'ZFPL':
            $file = dirname(__DIR__) . '/vendor/ZFPL/';
            break;
        default:
            return false;
    }
    $file .= implode('/', $segments) . '.php';
    if (file_exists($file)) {
        return include_once $file;
    }

    return false;
}
spl_autoload_register('ZendTest_Autoloader', true, true);