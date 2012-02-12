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
    private static $_commandsOutput = '';
    
    public function setArgs($args)
    {
        $this->_args = $args;
        array_shift($this->_args);
    }
    
    public function parse()
    {
        if (count($this->_args) == 0) {
            return false;
        }

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
                
        } else if ($this->_args[0] == 'upgrade') {
            $this->_action = 'upgrade';
                
        } else if ($this->_args[0] == 'version') {
            $this->_action = 'version';

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

        $return = 1;
        $log = array();
        exec($command . ' 2>&1', $log, $return);
        $log = implode(PHP_EOL, $log);
        
        if (!$return) {
            $output = trim($log);            
        }
        self::$_commandsOutput .= PHP_EOL . trim($log) . PHP_EOL; 
        
        self::log($log);
        self::log('---------------------------------');

        return !$return;
    }
    
    public function run()
    {               
        // Load Config
        $config = new Mage_Config;
        $config->loadGeneral();
        $config->loadEnvironment($this->getEnvironment());
        $config->loadSCM();

        // Logging
        $showGrettings = true;
        if (in_array($this->getAction(), array('install', 'upgrade', 'version'))) {
            self::$_logEnabled = false;
            $showGrettings = false;
        } else {
            self::$_logEnabled = $config->general('logging', false);
        }
        
        // Grettings
        if ($showGrettings) {
            Mage_Console::output('Starting <blue>Magallanes</blue>', 0, 2);
        }

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
                
            case 'upgrade';
                $task = new Mage_Task_Upgrade;
                $task->run();
                break;
                
            case 'init';
                $task = new Mage_Task_Init;
                $task->run();
                break;
                
            case 'add';
                switch ($this->_args[1]) {
                    case 'environment':
                        if (isset($this->_args[3]) && ($this->_args[3] == '--with-releases')) {
                            $withRelases = true;
                        } else {
                            $withRelases = false;
                        }

                        $task = new Mage_Task_Add;
                        $task->environment($this->_args[2], $withRelases);
                        break;
                }
                break;
                
            case 'version';
                $this->showVersion();
                break;
                
            default:
                Mage_Console::output('<red>Invalid action</red>', 0, 2);
                break;
        }
        
        if ($showGrettings) {
            Mage_Console::output('Finished <blue>Magallanes</blue>', 0, 2);
        }
    }
    
    public function showVersion()
    {
        Mage_Console::output('Running <blue>Magallanes</blue> version <dark_gray>' . MAGALLANES_VERSION .'</dark_gray>', 0, 2);
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