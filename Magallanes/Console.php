<?php
class Magallanes_Console
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
    
    public function run()
    {
        $config = new Magallanes_Config;

        switch ($this->getAction()) {
            case 'deploy':
                $config->loadEnvironment($this->getEnvironment());
                $task = new Magallanes_Task_Deploy;
                break;

            case 'update';
                $config->loadCSM();
                $task = new Magallanes_Task_Update;
                break;
        }

        $task->run($config);
    }
}

define('PHP_TAB', "\t");