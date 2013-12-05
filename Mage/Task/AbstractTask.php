<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task;

use Mage\Console;
use Mage\Config;
use Mage\Task\ErrorWithMessageException;
use Mage\Task\SkipException;
use Mage\Task\Releases\IsReleaseAware;

use Exception;

/**
 * Abstract Class for a Magallanes Task
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
abstract class AbstractTask
{
	/**
	 * Configuration
	 * @var Config;
	 */
    protected $config = null;

    /**
     * Indicates if the Task is running in a Rollback
     * @var boolean
     */
    protected $inRollback = false;

    /**
     * Indicates the Stage the Task is running ing
     * @var string
     */
    protected $stage = null;

    /**
     * Extra parameters
     * @var array
     */
    protected $parameters = array();

    /**
     * Executed job list
     * @var array
     */
    protected $jobList = [];

    /**
     * Returns the Title of the Task
     * @return string
     */
    public abstract function getName();

    /**
     * Runs the task
     *
     * @return boolean
     * @throws Exception
     * @throws ErrorWithMessageException
     * @throws SkipException
     */
    public abstract function run();

    /**
     * Task Constructor
     *
     * @param Config $config
     * @param boolean $inRollback
     * @param string $stage
     * @param array $parameters
     */
    public final function __construct(Config $config, $inRollback = false, $stage = null, $parameters = array())
    {
        $this->config     = $config;
        $this->inRollback = $inRollback;
        $this->stage      = $stage;
        $this->parameters = $parameters;
    }

    /**
     * Indicates if the Task is running in a Rollback operation
     * @return boolean
     */
    public function inRollback()
    {
        return $this->inRollback;
    }

    /**
     * Gets the Stage of the Deployment:
     *     - pre-deploy
     *     - deploy
     *     - post-deploy
     *     - post-release
     * @return string
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * Gets the Configuration
     * @return Config;
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Initializes the Task, optional to implement
     */
    public function init()
    {
    }

    /**
     * Returns a Parameter, or a default if not found
     *
     * @param string $name
     * @return mixed
     */
    public function getParameter($name, $default = null)
    {
        return $this->getConfig()->getParameter($name, $default, $this->parameters);
    }

    /**
     * Runs a Shell Command Localy
     * @param string $command
     * @param string $output
     * @return boolean
     */
    protected final function runCommandLocal($command, &$output = null)
    {
        return Console::executeCommand($command, $output);
    }

    /**
     * Runs a Shell Command on the Remote Host
     * @param string $command
     * @param string $output
     * @return boolean
     */
    protected final function runCommandRemote($command, &$output = null)
    {
        $localCommand = $this->generateLocalToRemoteCommand($command);

        return $this->runCommandLocal($localCommand, $output);
    }

    protected final function runJobLocal($command) {
        $verbose = $this->getParameter('verbose', false);
        $showCommands = $this->getParameter('show-commands', $verbose);
        $showErrors = $this->getParameter('show-errors', $verbose);
        $this->jobList[] =  \Mage\Job::run($command, $showErrors, $verbose, $showCommands);
        return end($this->jobList);
    }

    protected final function runJobRemote($command) {
        $localCommand = $this->generateLocalToRemoteCommand($command);
        return $this->runJobLocal($localCommand);
    }

    /**
     * Runs a Shell Command Localy or in the Remote Host based on the Task Stage.
     * If the stage is "deploy" then it will be executed in the remote host.
     * @param string $command
     * @param string $output
     * @return boolean
     */
    protected final function runCommand($command, &$output = null)
    {
        if ($this->getStage() == 'deploy') {
        	return $this->runCommandRemote($command, $output);
        } else {
        	return $this->runCommandLocal($command, $output);
        }
    }

    /**
     * @param $command
     * @return string
     */
    protected function generateLocalToRemoteCommand($command)
    {
        if (!$this instanceOf IsReleaseAware) {
            $releasesDirectory = $this->getConfig()->getReleaseDirectory();
        } else {
            $releasesDirectory = '';
        }

        $localCommand = 'ssh -p ' . $this->getConfig()->getHostPort() . ' '
            . '-q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no '
            . $this->getConfig()->getNameAtHostnameString() . ' '
            . '"cd ' . rtrim($this->getConfig()->deployment('to'), '/') . $releasesDirectory . ' && '
            . str_replace('"', '\"', $command) . '"';
        return $localCommand;
    }

    public function isAllOk() {
        /** @var $job \Mage\Job */
        foreach ($this->jobList as $job) {
            if ($job->failed()) {
                return false;
            }
        }
        return true;
    }
}