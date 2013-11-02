<?php
class Mage_Console
{
    private static $_log = null;
    private static $_logEnabled = true;
    private static $_screenBuffer = '';
    private static $_commandsOutput = '';

    /**
     * Runns a Magallanes Command
     * @throws Exception
     */
    public function run($arguments)
    {
    	register_shutdown_function(function() {
    		// Only Unlock if there was an error
            if (error_get_last() !== null) {
            	if (file_exists('.mage/~working.lock')) {
            		unlink('.mage/~working.lock');
            	}
            }
    	});

        $configError = false;
        try {
            // Load Config
            $config = new Mage_Config;
            $config->load($arguments);
            $configLoadedOk = true;

        } catch (Exception $e) {
            $configError = $e->getMessage();
        }

        // Command Option
        $commandName = $config->getArgument(0);

        // Logging
        $showGrettings = true;
        if (in_array($commandName, array('install', 'upgrade', 'version'))) {
            self::$_logEnabled = false;
            $showGrettings = false;
        } else {
            self::$_logEnabled = $config->general('logging', false);
        }

        // Grettings
        if ($showGrettings) {
            Mage_Console::output('Starting <blue>Magallanes</blue>', 0, 2);
        }

        // Run Command
        if ($configError !== false) {
            Mage_Console::output('<red>' . $configError . '</red>', 1, 2);

        } else {
            try {
                $command = Mage_Command_Factory::get($commandName, $config);

                if ($command instanceOf Mage_Command_RequiresEnvironment) {
                    if ($config->getEnvironment() == false) {
                        throw new Exception('You must specify an environment for this command.');
                    }
                }
                $command->run();

            } catch (Exception $e) {
                Mage_Console::output('<red>' . $e->getMessage() . '</red>', 1, 2);
            }
        }

        if ($showGrettings) {
            Mage_Console::output('Finished <blue>Magallanes</blue>', 0, 2);
        }

        self::_checkLogs($config);
    }

    /**
     * Outputs a message to the user screen
     *
     * @param string $message
     * @param integer $tabs
     * @param integer $newLine
     */
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

    /**
     * Executes a Command on the Shell
     *
     * @param string $command
     * @param string $output
     * @return boolean
     */
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

    /**
     * Log a message to the logfile.
     *
     * @param string $message
     * @param boolean $continuation
     */
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

    /**
     * Check Logs
     * @param Mage_Config $config
     */
    private static function _checkLogs(Mage_Config $config)
    {
        if (self::$_logEnabled) {
        	$maxLogs = $config->general('maxlogs', 30);

        	$logs = array();
        	foreach (new RecursiveDirectoryIterator('.mage/logs', RecursiveDirectoryIterator::SKIP_DOTS) as $log) {
        		if (strpos($log->getFilename(), 'log-') === 0) {
        			$logs[] = $log->getFilename();
        		}
        	}

        	sort($logs);
        	if (count($logs) > $maxLogs) {
                $logsToDelete = array_slice($logs, 0, count($logs) - $maxLogs);
                foreach ($logsToDelete as $logToDeelte) {
                	unlink('.mage/logs/' . $logToDeelte);
                }
        	}
        }
    }
}