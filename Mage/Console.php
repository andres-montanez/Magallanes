<?php
class Mage_Console
{
    private $_args = null;
    private $_action = null;
    private $_actionOptions = null;
    private $_environment = null;
    private static $_log = null;
    private static $_logEnabled = true;
    private static $_screenBuffer = '';
    
    public function setArgs($args)
    {
        $this->_args = $args;
        array_shift($this->_args);
    }
    
    public function parse()
    {
            if ($this->_args[0] == 'deploy') {
                $this->_action = 'deploy';

            } else if ($this->_args[0] == 'releases') {
                $this->_action = 'releases';
                
            } else if ($this->_args[0] == 'update') {
                $this->_action = 'update';

            } else if ($this->_args[0] == 'add') {
                $this->_action = 'add';
                
            } else if ($this->_args[0] == 'install') {
                $this->_action = 'install';

            } else if ($this->_args[0] == 'init') {
                $this->_action = 'init';
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
        self::log(strip_tags($message));
        
        self::$_screenBuffer .= str_repeat("\t", $tabs)
                              . strip_tags($message)
                              . str_repeat(PHP_EOL, $newLine);
        
        $output = str_repeat("\t", $tabs)
                . Mage_Console_Colors::color($message)
                . str_repeat(PHP_EOL, $newLine);

        echo $output;
    }
    
    public static function executeCommand($command, &$output = null)
    {
        self::log('---------------------------------');
        self::log('---- Executing: $ ' . $command);
        
        ob_start();
        system($command . ' 2>&1', $return);
        $log = ob_get_clean();
        
        if (!$return) {
            $output = trim($log);            
        }
        
        self::log($log);
        self::log('---------------------------------');

        return !$return;
    }
    
    public function run()
    {
        // Disable Loging
        if ($this->getAction() == 'install') {
            self::$_logEnabled = false;
        }
        
        Mage_Console::output('Starting <blue>Magallanes</blue>', 0, 2);
        
        $config = new Mage_Config;
        $config->loadEnvironment($this->getEnvironment());
        $config->loadSCM();

        switch ($this->getAction()) {
            case 'deploy':
                $task = new Mage_Task_Deploy;
                $task->run($config);
                break;
                
            case 'releases':
                $task = new Mage_Task_Releases;
                switch ($this->_args[1]) {
                    case 'list':
                        $task->setAction($this->_args[1]);
                        break;

                    case 'rollback':
                        $task->setAction($this->_args[1]);
                        $task->setRelease($this->_args[2]);
                        break;
                }
                $task->run($config);
                break;

            case 'update';
                $task = new Mage_Task_Update;
                $task->run($config);
                break;

            case 'install';
                $task = new Mage_Task_Install;
                $task->run();
                break;
                
            case 'init';
                $task = new Mage_Task_Init;
                $task->run();
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
        
        Mage_Console::output('Finished <blue>Magallanes</blue>', 0, 2);
    }

    public static function log($message, $continuation = false)
    {
        if (self::$_logEnabled) {
            if (self::$_log == null) {
                self::$_log = fopen('.mage/logs/log-' . date('Ymd-His') . '.log', 'w');
            }
            
            $message = date('Y-m-d H:i:s -- ') . $message;
            fwrite(self::$_log, $message . PHP_EOL);            
        }
    }
}