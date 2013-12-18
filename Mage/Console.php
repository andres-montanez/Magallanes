<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage;

use Exception;
use RecursiveDirectoryIterator;

/**
 * Magallanes interface between the Tasks and Commands and the User's Console.
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class Console
{
	/**
	 * Handler to the current Log File.
	 * @var handler
	 */
    private static $log = null;

    /**
     * The current logfile
     * @var string
     */
    private static $logFile = null;

    /**
     * Enables or Disables Logging
     * @var boolean
     */
    private static $logEnabled = true;

    /**
     * String Buffer for the screen output
     * @var string
     */
    private static $screenBuffer = '';

    /**
     * Output of executed commands
     * @var string
     */
    private static $commandsOutput = '';

    /**
     * Runns a Magallanes Command
     * @throws Exception
     */
    public function run($arguments)
    {
    	// Declare a Shutdown Closure
    	register_shutdown_function(function() {
    		// Only Unlock if there was an error
            if (error_get_last() !== null) {
            	if (file_exists('.mage/~working.lock')) {
            		unlink('.mage/~working.lock');
            	}
            }
    	});

    	// Load configuration
        $configError = false;
        try {
            // Load Config
            $config = new Config;
            $config->load($arguments);
            $configLoadedOk = true;

        } catch (Exception $exception) {
            $configError = $exception->getMessage();
        }

        // Command Option
        $commandName = $config->getArgument(0);

        // Logging
        $showGrettings = true;
        if (in_array($commandName, array('install', 'upgrade', 'version'))) {
            self::$logEnabled = false;
            $showGrettings = false;
        } else {
            self::$logEnabled = $config->general('logging', false);
        }

        // Grettings
        if ($showGrettings) {
            self::output('Starting <blue>Magallanes</blue>', 0, 2);
        }

        // Run Command - Check if there is a Configuration Error
        if ($configError !== false) {
            self::output('<red>' . $configError . '</red>', 1, 2);

        } else {
        	// Run Command and check for Command Requirements
            try {
                $command = Command\Factory::get($commandName, $config);

                if ($command instanceOf Command\RequiresEnvironment) {
                    if ($config->getEnvironment() == false) {
                        throw new Exception('You must specify an environment for this command.');
                    }
                }
                $command->run();

            } catch (Exception $exception) {
                self::output('<red>' . $exception->getMessage() . '</red>', 1, 2);
            }
        }

        if ($showGrettings) {
            self::output('Finished <blue>Magallanes</blue>', 0, 2);
            if (file_exists('.mage/~working.lock')) {
            	unlink('.mage/~working.lock');
            }
        }

        // Check if logs need to be deleted
        self::checkLogs($config);
    }

    /**
     * Outputs a message to the user's screen.
     *
     * @param string $message
     * @param integer $tabs
     * @param integer $newLine
     */
    public static function output($message, $tabs = 1, $newLine = 1)
    {
        self::log(strip_tags($message));

        self::$screenBuffer .= str_repeat("\t", $tabs)
                              . strip_tags($message)
                              . str_repeat(PHP_EOL, $newLine);

        $output = str_repeat("\t", $tabs)
                . Console\Colors::color($message)
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
        self::$commandsOutput .= PHP_EOL . trim($log) . PHP_EOL;

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
        if (self::$logEnabled) {
            if (self::$log == null) {
            	self::$logFile = realpath('.mage/logs') . '/log-' . date('Ymd-His') . '.log';
                self::$log = fopen(self::$logFile, 'w');
            }

            $message = date('Y-m-d H:i:s -- ') . $message;
            fwrite(self::$log, $message . PHP_EOL);
        }
    }

    /**
     * Return the screen buffer
     * @return string
     */
    public static function getOutput()
    {
    	return self::$screenBuffer;
    }

    /**
     * Returns the Log File
     * @return string
     */
    public static function getLogFile()
    {
    	return self::$logFile;
    }

    /**
     * Check Logs
     * @param \Mage\Config $config
     */
    private static function checkLogs(Config $config)
    {
        if (self::$logEnabled) {
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