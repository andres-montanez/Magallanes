<?php
class Mage_Console
{
    private $_args;
    private $_action;
    private $_actionOptions;
    private $_environment;
    
    public function setArgs($args)
    {
        $this->_args = $args;
        array_shift($this->_args);
    }
    
    public function parse()
    {
        foreach ($this->_args as $argument) {
            if ($argument == 'deploy') {
                $this->_action = 'deploy';

            } else if ($argument == 'update') {
                $this->_action = 'update';

            } else if (preg_match('/to:[\w]+/i', $argument)) {
                $this->_environment = str_replace('to:', '', $argument);
            }
        }
    }
    
    public function getAction()
    {
        return $this->_action;
    }
    
    public function getEnvironment()
    {
        return $this->_environment;
    }
    
    public static function output($message)
    {
        echo $message;
    }
    
    public static function executeCommand($command)
    {
        ob_start();
        system($command . ' 2>&1', $return);
        $log = ob_get_clean();

        return !$return;
    }
    
    public function run()
    {
        $config = new Mage_Config;
        $config->loadEnvironment($this->getEnvironment());
        $config->loadSCM();

        switch ($this->getAction()) {
            case 'deploy':
                $task = new Mage_Task_Deploy;
                $task->run($config);
                break;

            case 'update';
                $config->loadCSM();
                $task = new Mage_Task_Update;
                        $task->run($config);
                break;
        }
    }
}

define('PHP_TAB', "\t");