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
 * Runtime is a container of all run in time configuration, stages of progress, hosts being deployed, etc.
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class Runtime
{
    const PRE_DEPLOY = 'pre-deploy';
    const ON_DEPLOY = 'on-deploy';
    const POST_DEPLOY = 'post-deploy';
    const ON_RELEASE = 'on-release';
    const POST_RELEASE = 'post-release';

    /**
     * @var array Magallanes configuration
     */
    protected $configuration = [];

    /**
     * @var string|null Environment being deployed
     */
    protected $environment;

    /**
     * @var string Stage of Deployment
     */
    protected $stage;

    /**
     * @var string|null The host being deployed to
     */
    protected $workingHost;

    /**
     * @var string|null The Release ID
     */
    protected $releaseId = null;

    /**
     * @var array Hold a bag of variables for sharing information between tasks, if needed
     */
    protected $vars = [];

    /**
     * @var LoggerInterface|null The logger instance
     */
    protected $logger;

    /**
     * @var bool Indicates if a Rollback operation is in progress
     */
    protected $rollback = false;

    /**
     * Generate the Release ID
     *
     * @return Runtime
     */
    public function generateReleaseId()
    {
        $this->setReleaseId(date('YmdHis'));
        return $this;
    }

    /**
     * Sets the Release ID
     *
     * @param string $releaseId Release ID
     * @return Runtime
     */
    public function setReleaseId($releaseId)
    {
        $this->releaseId = $releaseId;
        return $this;
    }

    /**
     * Retrieve the current Release ID
     *
     * @return null|string Release ID
     */
    public function getReleaseId()
    {
        return $this->releaseId;
    }

    /**
     * Sets the Runtime in Rollback mode On or Off
     *
     * @param bool $inRollback
     * @return Runtime
     */
    public function setRollback($inRollback)
    {
        $this->rollback = $inRollback;
        return $this;
    }

    /**
     * Indicates if Runtime is in rollback
     *
     * @return bool
     */
    public function inRollback()
    {
        return $this->rollback;
    }

    /**
     * Sets a value in the Vars bag
     *
     * @param mixed $key Variable name
     * @param mixed $value Variable value
     * @return Runtime
     */
    public function setVar($key, $value)
    {
        $this->vars[$key] = $value;
        return $this;
    }

    /**
     * Retrieve a value from the Vars bag
     *
     * @param mixed $key Variable name
     * @param mixed $default Variable default value, returned if not found
     * @return mixed
     */
    public function getVar($key, $default = null)
    {
        if (array_key_exists($key, $this->vars)) {
            return $this->vars[$key];
        }

        return $default;
    }

    /**
     * Sets the Logger instance
     *
     * @param LoggerInterface $logger Logger instance
     * @return Runtime
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Sets the Magallanes Configuration to the Runtime
     *
     * @param array $configuration Configuration
     * @return Runtime
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * Retrieve the Configuration
     *
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Retrieves the Configuration options for a specific section in the configuration
     *
     * @param mixed $key Section name
     * @param mixed $default Default value
     * @return mixed
     */
    public function getConfigOptions($key, $default = null)
    {
        if (array_key_exists($key, $this->configuration)) {
            return $this->configuration[$key];
        }

        return $default;
    }

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
    public function getEnvironmentConfig($key = null, $default = null)
    {
        if (!array_key_exists('environments', $this->configuration) || !is_array($this->configuration['environments'])) {
            return [];
        }

        if (!array_key_exists($this->environment, $this->configuration['environments'])) {
            return [];
        }

        $config = $this->configuration['environments'][$this->environment];
        if ($key !== null) {
            if (array_key_exists($key, $config)) {
                return $config[$key];
            } else {
                return $default;
            }
        }

        return $config;
    }

    /**
     * Sets the working Environment
     *
     * @param string $environment Environment name
     * @return Runtime
     * @throws InvalidEnvironmentException
     */
    public function setEnvironment($environment)
    {
        if (array_key_exists('environments', $this->configuration) && array_key_exists($environment, $this->configuration['environments'])) {
            $this->environment = $environment;
            return $this;
        }

        throw new InvalidEnvironmentException(sprintf('The environment "%s" does not exists.', $environment), 100);
    }

    /**
     * Returns the current working Environment
     *
     * @return null|string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Sets the working stage
     *
     * @param string $stage Stage code
     * @return Runtime
     */
    public function setStage($stage)
    {
        $this->stage = $stage;
        return $this;
    }

    /**
     * Retrieve the current wokring Stage
     *
     * @return string
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * Retrieve the defined Tasks for the current Environment and Stage
     *
     * @return array
     * @throws InvalidEnvironmentException
     */
    public function getTasks()
    {
        $config = $this->getEnvironmentConfig();
        if (array_key_exists($this->stage, $config)) {
            if (is_array($config[$this->stage])) {
                return $config[$this->stage];
            }
        }

        return [];
    }

    /**
     * Sets the working Host
     *
     * @param string $host Host name
     * @return Runtime
     */
    public function setWorkingHost($host)
    {
        $this->workingHost = $host;
        return $this;
    }

    /**
     * Retrieve the working Host
     *
     * @return null|string
     */
    public function getWorkingHost()
    {
        return $this->workingHost;
    }

    /**
     * Logs a Message into the Logger
     *
     * @param string $message Log message
     * @param string $level Log Level
     */
    public function log($message, $level = LogLevel::DEBUG)
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->log($level, $message);
        }
    }

    /**
     * Executes a command, it will be run Locally or Remotely based on the working Stage
     *
     * @param string $cmd Command to execute
     * @param int $timeout Seconds to wait
     * @return Process
     */
    public function runCommand($cmd, $timeout = 120)
    {
        switch ($this->getStage()) {
            case self::ON_DEPLOY:
            case self::ON_RELEASE:
            case self::POST_RELEASE:
                return $this->runRemoteCommand($cmd, true, $timeout);
                break;
            default:
                return $this->runLocalCommand($cmd, $timeout);
                break;
        }
    }

    /**
     * Execute a command locally
     *
     * @param string $cmd Command to execute
     * @param int $timeout Seconds to wait
     * @return Process
     */
    public function runLocalCommand($cmd, $timeout = 120)
    {
        $this->log($cmd, LogLevel::INFO);

        $process = new Process($cmd);
        $process->setTimeout($timeout);
        $process->run();

        $this->log($process->getOutput(), LogLevel::DEBUG);
        if (!$process->isSuccessful()) {
            $this->log($process->getErrorOutput(), LogLevel::ERROR);
        }

        return $process;
    }

    /**
     * Executes a command remotely, if jail is true, it will run inside the Host Path and the Release (if available)
     *
     * @param string $cmd Command to execute
     * @param bool $jail Jail the command
     * @param int $timeout Seconds to wait
     * @return Process
     * @throws InvalidEnvironmentException
     */
    public function runRemoteCommand($cmd, $jail = true, $timeout = 120)
    {
        $user = $this->getEnvironmentConfig('user');
        $host = $this->getWorkingHost();
        $sshConfig = $this->getSSHConfig();

        $cmdDelegate = $cmd;
        if ($jail) {
            $hostPath = rtrim($this->getEnvironmentConfig('host_path'), '/');
            if ($this->getReleaseId()) {
                $cmdDelegate = sprintf('cd %s/releases/%s && %s', $hostPath, $this->getReleaseId(), $cmdDelegate);
            } else {
                $cmdDelegate = sprintf('cd %s && %s', $hostPath, $cmdDelegate);
            }
        }

        $cmdRemote = str_replace(['"', '&', ';'], ['\"', '\&', '\;'], $cmdDelegate);
        $cmdLocal = sprintf('ssh -p %d %s %s@%s sh -c \"%s\"', $sshConfig['port'], $sshConfig['flags'], $user, $host, $cmdRemote);

        return $this->runLocalCommand($cmdLocal, $timeout);
    }

    /**
     * Get the SSH configuration based on the environment
     *
     * @return array
     */
    public function getSSHConfig()
    {
        $sshConfig = $this->getEnvironmentConfig('ssh', ['port' => '22', 'flags' => '-q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no']);

        if (!array_key_exists('port', $sshConfig)) {
            $sshConfig['port'] = '22';
        }

        if (!array_key_exists('flags', $sshConfig)) {
            $sshConfig['flags'] = '-q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no';
        }

        return $sshConfig;
    }

    /**
     * Gets a Temporal File name
     *
     * @return string
     */
    public function getTempFile()
    {
        return tempnam(sys_get_temp_dir(), 'mage');
    }
}
