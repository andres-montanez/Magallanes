<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Runtime;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Process\Process;
use Mage\Runtime\Exception\InvalidEnvironmentException;

/**
 * Interface for the Runtime container
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
interface RuntimeInterface
{
    const PRE_DEPLOY = 'pre-deploy';
    const ON_DEPLOY = 'on-deploy';
    const POST_DEPLOY = 'post-deploy';
    const ON_RELEASE = 'on-release';
    const POST_RELEASE = 'post-release';

    /**
     * Sets the Release ID
     *
     * @param string $releaseId Release ID
     * @return RuntimeInterface
     */
    public function setReleaseId($releaseId);

    /**
     * Retrieve the current Release ID
     *
     * @return null|string Release ID
     */
    public function getReleaseId();

    /**
     * Sets the Runtime in Rollback mode On or Off
     *
     * @param bool $inRollback
     * @return RuntimeInterface
     */
    public function setRollback($inRollback);

    /**
     * Indicates if Runtime is in rollback
     *
     * @return bool
     */
    public function inRollback();

    /**
     * Sets a value in the Vars bag
     *
     * @param mixed $key Variable name
     * @param mixed $value Variable value
     * @return RuntimeInterface
     */
    public function setVar($key, $value);

    /**
     * Retrieve a value from the Vars bag
     *
     * @param mixed $key Variable name
     * @param mixed $default Variable default value, returned if not found
     * @return mixed
     */
    public function getVar($key, $default = null);

    /**
     * Sets the Logger instance
     *
     * @param LoggerInterface $logger Logger instance
     * @return RuntimeInterface
     */
    public function setLogger(LoggerInterface $logger = null);

    /**
     * Sets the Magallanes Configuration to the Runtime
     *
     * @param array $configuration Configuration
     * @return RuntimeInterface
     */
    public function setConfiguration($configuration);

    /**
     * Retrieve the Configuration
     *
     * @return array
     */
    public function getConfiguration();

    /**
     * Retrieves the Configuration options for a specific section in the configuration
     *
     * @param mixed $key Section name
     * @param mixed $default Default value
     * @return mixed
     */
    public function getConfigOptions($key, $default = null);

    /**
     * Returns the configuration for the current Environment
     * If $key is provided, it will be returned only that section, if not found the default value will be returned,
     * if $key is not provided, the whole Environment's configuration will be returned
     *
     * @param mixed $key Section name
     * @param mixed $default Default value
     * @return mixed
     * @throws InvalidEnvironmentException
     */
    public function getEnvironmentConfig($key = null, $default = null);

    /**
     * Sets the working Environment
     *
     * @param string $environment Environment name
     * @return RuntimeInterface
     * @throws InvalidEnvironmentException
     */
    public function setEnvironment($environment);

    /**
     * Returns the current working Environment
     *
     * @return null|string
     */
    public function getEnvironment();

    /**
     * Sets the working stage
     *
     * @param string $stage Stage code
     * @return RuntimeInterface
     */
    public function setStage($stage);

    /**
     * Retrieve the current wokring Stage
     *
     * @return string
     */
    public function getStage();

    /**
     * Retrieve the defined Tasks for the current Environment and Stage
     *
     * @return array
     * @throws InvalidEnvironmentException
     */
    public function getTasks();

    /**
     * Sets the working Host
     *
     * @param string $host Host name
     * @return RuntimeInterface
     */
    public function setWorkingHost($host);

    /**
     * Retrieve the working Host
     *
     * @return null|string
     */
    public function getWorkingHost();

    /**
     * Logs a Message into the Logger
     *
     * @param string $message Log message
     * @param string $level Log Level
     */
    public function log($message, $level = LogLevel::DEBUG);

    /**
     * Executes a command, it will be run Locally or Remotely based on the working Stage
     *
     * @param string $cmd Command to execute
     * @param int $timeout Seconds to wait
     * @return Process
     */
    public function runCommand($cmd, $timeout = 120);

    /**
     * Execute a command locally
     *
     * @param string $cmd Command to execute
     * @param int $timeout Seconds to wait
     * @return Process
     */
    public function runLocalCommand($cmd, $timeout = 120);

    /**
     * Executes a command remotely, if jail is true, it will run inside the Host Path and the Release (if available)
     *
     * @param string $cmd Command to execute
     * @param bool $jail Jail the command
     * @param int $timeout Seconds to wait
     * @return Process
     * @throws InvalidEnvironmentException
     */
    public function runRemoteCommand($cmd, $jail = true, $timeout = 120);

    /**
     * Get the SSH configuration based on the environment
     *
     * @return array
     */
    public function getSSHConfig();
}
