<?php

/*
 * This file is part of the ZFPJ package.
 * @copyright Copyright (c) 2012 Blanchon Vincent - France (http://developpeur-zend-framework.fr - blanchon.vincent@gmail.com)
 */

namespace ZFPJ\System\Fork;

use ZFPJ\System\Fork\Storage\Segment,
    ZFPJ\System\Fork\Storage\StorageInterface,
    Zend\Stdlib\CallbackHandler;

class ForkManager
{
    /**
     * Current id
     * @var int 
     */
    protected $uid;
    
    /**
     * Current pid
     * @var int 
     */
    protected $pid;
        
    /**
     * Fork container
     * @var Segment 
     */
    protected $container = null;
        
    /**
     * fork parent
     * @var type 
     */
    protected $forkParent;
    
    /**
     * Callback default
     * @var mixed 
     */
    protected $callback;
    
    /**
     * Callback default params
     * @var mixed 
     */
    protected $callbackParam;
    
    /**
     * Callback children
     * @var mixed 
     */
    protected $callbackChildren = array();
    
    /**
     * Callback children params
     * @var mixed 
     */
    protected $callbackParamChildren = array();
    
    /**
     * Auto start job for children
     * @var bool
     */
    protected $autoStart = true;
    
    /**
     * Share children result
     * @var bool 
     */
    protected $shareResult = false;
    
    /**
     * Children handler
     * @var array
     */
    protected $handlers = array();
    
    /**
     * Timeout
     * @var int
     */
    protected $timeout;
    
    /**
     * Num of children
     * @var int
     */
    protected $numChildren;
    
    /**
     * Flag start
     * @var bool
     */
    protected $isStarted = false;
    
    /**
     * Flag finish
     * @var bool
     */
    protected $isFinished = false;
    
    /**
     * Flag finish
     * @var bool
     */
    protected $isStopped = false;
    
    /**
     * Default results container
     * @var string
     */
    protected $defaultResultsContainer = 'ZFPJ\System\Fork\Storage\Results\Results';
    
    /**
     * Default result container
     * @var string
     */
    protected $defaultResultContainer = 'ZFPJ\System\Fork\Storage\Result\Result';
    
    /**
     * Manager instance
     */
    public function __construct()
    {   
        if(!function_exists('pcntl_fork')) {
            throw new RuntimeException('pcntl functions must exists to run this module');
        }
        
        $this->forkParent = getmypid();
    }
    
    /**
     * Children construction
     * @param int $num 
     */
    public function createChildren($num, $start = null)
    {   
        $this->numChildren = $num;
        if(!is_null($start)) {
            $this->autoStart = $start;
        }
        if($this->autoStart) {
            $this->start();
        }
    }
    
    /**
     * Fork start
     */
    public function start()
    {
        if($this->isStarted) {
            $this->closeChildren();
            throw new Exception\RuntimeException('Manager is already started');
        }
        $this->isStarted = true;
        $this->_createChildren();
    }
    
    /**
     * Children construction
     * @param int $num 
     */
    protected function _createChildren()
    {   
        if($this->shareResult) {
            $max = $this->getContainer()->max();
            if($max<$this->numChildren) {
                throw new Exception\RuntimeException('Max creation child is ' . $max . ', increase container memory size to fork more child');
            }
        }
        
        for ($i = 0; $i < $this->numChildren; $i++) {
            
            $pid = pcntl_fork();
            
            if($pid == -1) {
                throw new Exception\RuntimeException('Fork error in the children create');
            }
            else if($pid == 0) {
                $this->uid = $i+1;
                $this->pid = getmypid();
                $this->runJob();
                break;
            }
            else {
                $this->handlers[$i+1] = $pid;
            }
        }
        
        if($this->isForkParent()) {
            $this->registerTimeout();
            $this->uid = 0;
            $this->pid = getmypid();
            declare(ticks = 1);
            pcntl_signal(SIGINT, array($this, 'handler'));
        }
    }
    
    /**
     * Set timeout
     * @param type $time
     * @return ForkManager 
     */
    public function timeout($time)
    {
        if($time <= 0) {
            throw new Exception\RuntimeException('Invalid timeout value');
        }
        $this->timeout = $time;
        return $this;
    }
    
    /**
     * Register a timetout
     */
    protected function registerTimeout()
    {
        if($this->timeout) {
            pcntl_alarm($this->timeout);
            pcntl_signal(SIGALRM, array($this, "handler"));
        }
    }
    
    /**
     * Run the fork job
     */
    protected function runJob()
    {
        if(isset($this->callbackChildren[$this->uid])) {
            $callback = $this->callbackChildren[$this->uid];
            $result = $callback->call($this->callbackParamChildren[$this->uid]);
        }
        else if($this->callback) {
            $result = $this->callback->call($this->callbackParam);
        }

        if($this->shareResult) {
            $this->getContainer()->write($this->uid, $result);
        }
        posix_kill($this->pid, 9);
    }
    
    /**
     * Set default jobs
     * @param mixed $callback
     * @param mixed $params 
     */
    public function doTheJob($callback, $params = array())
    {
        if(!$callback instanceof CallbackHandler) {
            $callback = new CallbackHandler($callback);
        }
        if(is_string($params)) {
            $params = array($params);
        }
        $this->callback = $callback;
        $this->callbackParam = $params;
        return $this;
    }
    
    /**
     * Set cild jobs
     * @param mixed $callback
     * @param mixed $params 
     */
    public function doTheJobChild($num, $callback, $params = array())
    {
        if(!$callback instanceof CallbackHandler) {
            $callback = new CallbackHandler($callback);
        }
        if(is_string($params)) {
            $params = array($params);
        }
        $this->callbackChildren[$num] = $callback;
        $this->callbackParamChildren[$num] = $params;
        return $this;
    }
    
    /**
     * ISignal handler.
     *
     * @param integer $signal signal number
     */
    public function handler($signal) {
        
        switch($signal)
        {
            case SIGALRM :
                $this->isStopped = true;
                $this->closeChildren();
                break;
            case SIGINT :
            case SIGKILL :
                $this->broadcast($signal);
                exit;
            default: break;
        }
    }
    
    /**
     * Broadcast signal
     * @param type $signal 
     */
    public function broadcast($signal)
    {
        if($signal == SIGINT || $signal == SIGKILL ) {
            $this->isStopped = true;
        }
        foreach($this->handlers as $handler) {
           posix_kill($handler, $signal);
        }
    }
    
    /**
     * Close all children
     */
    public function closeChildren()
    {
       foreach($this->handlers as $handler) {
           posix_kill($handler, 9);
       }
    }
    
    /**
     * Close all children
     */
    public function getSharedResults()
    {
        if(!$this->shareResult) {
            return false;
        }
        if(!$this->isStarted) {
            return false;
        }
        if(!$this->isFinished) {
            trigger_error('children process was not interrupted', E_USER_NOTICE);
            return false;
        }
        $resultsContainer = $this->getDefaultResultsContainer();
        $results = new $resultsContainer();
        
        foreach($this->handlers as $uid => $handler) {
            
            $resultContainer = $this->getDefaultResultContainer();
            $result = new $resultContainer();
            $result->setUid($uid);
            $result->setPid($handler);
            $result->setResult($this->getContainer()->read($uid));
            $results->addResult($uid, $result);
        }
        
        return $results;
    }
    
    /**
     * Wait children
     */
    public function wait()
    {
        $status = array();
        foreach($this->handlers as $uid => $handler) {
            pcntl_waitpid($handler, $statut, WUNTRACED);
            $status[$uid] = $statut;
            if(intval($statut) !== 9 && intval($statut) != 0) {
                trigger_error('Pid killed "' . $handler . '" has statut ' . $statut, E_USER_NOTICE);
            }
        }
        pcntl_alarm(0);
        $this->getContainer()->close();
        $this->isFinished = true;
        return $status;
    }
    
    /**
     * Run again
     */
    public function rewind()
    {
        if(!$this->isFinished) {
            throw new Exception\RuntimeException('Fork must be finished to rewind and replay');
        }
        
        pcntl_alarm(0);
        $this->getContainer()->close();
        $this->container = null;
        $this->handlers = array();
        $this->timeout = null;
        $this->isStopped = false;
        $this->isStarted = false;
        $this->isFinished = false;
        return $this;
    }
    
    /**
     * is the fork Parent
     * @return bool 
     */
    public function isForkParent()
    {
        return $this->forkParent == getmypid();
    }
        
    /**
     * Get fork container
     * @return StorageInterface 
     */
    public function getContainer()
    {
        if(null === $this->container) {
            $this->container = new Segment();
        }
        return $this->container;
    }
    
    /**
     * Get fork container
     * @return ForkManager 
     */
    public function setContainer(StorageInterface $container)
    {
        $this->container = $container;
        return $this;
    }
    
    /**
     * Get flag to share result
     * @return bool 
     */
    public function getShareResult()
    {
        return $this->shareResult;
    }
    
    /**
     * Set flag to share result
     * @param bool $b
     * @return ForkManager 
     */
    public function setShareResult($b)
    {
        if($this->isStarted) {
            throw new Exception\RuntimeException('Invalid timeout value');
        }
        $this->shareResult = $b;
        return $this;
    }
     
    /**
     * Get flag to auto start
     * @return bool 
     */
    public function getAutoStart()
    {
        return $this->autoStart;
    }
    
    /**
     * Set flag to auto start
     * @param bool $b
     * @return ForkManager 
     */
    public function setAutoStart($b)
    {
        $this->autoStart = $b;
        return $this;
    }
    
    /**
     * Get default container
     * @return string
     */
    public function getDefaultResultContainer()
    {
        return $this->defaultResultContainer;
    }
    
    /**
     * Set default container
     * @param string $container
     * @return ForkManager 
     */
    public function setDefaultResultContainer(Storage\Result\ResultInterface $container)
    {
        $this->defaultResultContainer = $container;
        return $this;
    }
    
    /**
     * Get default container
     * @return string
     */
    public function getDefaultResultsContainer()
    {
        return $this->defaultResultsContainer;
    }
    
    /**
     * Set default container
     * @param string $container
     * @return ForkManager 
     */
    public function setDefaultResultsContainer(Storage\Results\ResultsInterface $container)
    {
        $this->defaultResultsContainer = $container;
        return $this;
    }
    
    /**
     * Get flag stopped
     * @return bool 
     */
    public function isStopped()
    {
        return $this->isStopped;
    }
}