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
            if ($this->_args[0] == 'deploy') {
                $this->_action = 'deploy';

            } else if ($this->_args[0] == 'update') {
                $this->_action = 'update';

            } else if ($this->_args[0] == 'add') {
                $this->_action = 'add';
            } 
        
        foreach ($this->_args as $argument) {
            if (preg_match('/to:[\w]+/i', $argument)) {
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
    
    public static function output($message, $tabs = 1, $newLine = 1)
    {
        $output = str_repeat("\t", $tabs)
                . Mage_Console_Colors::color($message)
                . str_repeat(PHP_EOL, $newLine);

        echo $output;
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
                $task = new Mage_Task_Update;
                $task->run($config);
                break;
                
            case 'add';
                switch ($this->_args[1]) {
                    case 'environment':
                        $task = new Mage_Task_Add;
                        $task->environment($this->_args[2]);
                        break;
                }
                break;
        }
    }
}