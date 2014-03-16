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
	 * Stage Constant for Pre Deployment
	 * @var string
	 */
	const STAGE_PRE_DEPLOY = 'pre-deploy';

	/**
	 * Stage Constant for Deployment
	 * @var string
	 */
	const STAGE_DEPLOY = 'deploy';

	/**
	 * Stage Constant for Post Deployment
	 * @var string
	 */
	const STAGE_POST_DEPLOY = 'post-deploy';

	/**
	 * Stage Constant for Post Release
	 * @var string
	 */
	const STAGE_POST_RELEASE = 'post-release';

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
     * @param mixed $default
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
        if ($this->getConfig()->release('enabled', false) == true) {
            if ($this instanceOf IsReleaseAware) {
                $releasesDirectory = '';

            } else {
                $releasesDirectory = '/'
                                   . $this->getConfig()->release('directory', 'releases')
                                   . '/'
                                   . $this->getConfig()->getReleaseId();
            }

        } else {
            $releasesDirectory = '';
        }
        
        // if general.yml includes "ssy_needs_tty: true", then add "-t" to the ssh command
        $needs_tty = ($this->getConfig()->general('ssh_needs_tty',false) ? "-t" : "");

        $localCommand = 'ssh ' . $needs_tty . ' -p ' . $this->getConfig()->getHostPort() . ' '
                      . '-q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no '
                      . $this->getConfig()->deployment('user') . '@' . $this->getConfig()->getHostName() . ' '
                      . '"cd ' . rtrim($this->getConfig()->deployment('to'), '/') . $releasesDirectory . ' && '
                      . str_replace('"', '\"', $command) . '"';

        return $this->runCommandLocal($localCommand, $output);
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
        if ($this->getStage() == self::STAGE_DEPLOY) {
        	return $this->runCommandRemote($command, $output);
        } else {
        	return $this->runCommandLocal($command, $output);
        }
    }
}
