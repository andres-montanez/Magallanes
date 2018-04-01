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

use Mage\Deploy\Strategy\ReleasesStrategy;
use Mage\Deploy\Strategy\RsyncStrategy;
use Mage\Deploy\Strategy\StrategyInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Process\Process;
use Mage\Runtime\Exception\RuntimeException;

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
    protected $workingHost = null;

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
     * @param string $key Variable name
     * @param string $value Variable value
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
     * @param string $key Variable name
     * @param mixed $default Variable default value, returned if not found
     * @return string
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
     * Retrieves the Configuration Option for a specific section in the configuration
     *
     * @param string $key Section name
     * @param mixed $default Default value
     * @return mixed
     */
    public function getConfigOption($key, $default = null)
    {
        if (array_key_exists($key, $this->configuration)) {
            return $this->configuration[$key];
        }

        return $default;
    }

    /**
     * Returns the Configuration Option for a specific section the current Environment
     *
     * @param string $key Section/Parameter name
     * @param mixed $default Default value
     * @return mixed
     */
    public function getEnvOption($key, $default = null)
    {
        if (!array_key_exists('environments', $this->configuration) || !is_array($this->configuration['environments'])) {
            return $default;
        }

        if (!array_key_exists($this->environment, $this->configuration['environments'])) {
            return $default;
        }

        if (array_key_exists($key, $this->configuration['environments'][$this->environment])) {
            return $this->configuration['environments'][$this->environment][$key];
        }

        return $default;
    }

    /**
     * Shortcut to get the the configuration option for a specific environment and merge it with
     * the global one (environment specific overrides the global one if present).
     *
     * @param       $key
     * @param array $defaultEnv
     *
     * @return array
     */
    public function getMergedOption($key, $defaultEnv = [])
    {
        $userGlobalOptions = $this->getConfigOption($key, $defaultEnv);
        $userEnvOptions = $this->getEnvOption($key, $defaultEnv);

        return array_merge(
            (is_array($userGlobalOptions) ? $userGlobalOptions : []),
            (is_array($userEnvOptions) ? $userEnvOptions : [])
        );
    }

    /**
     * Overwrites an Environment Configuration Option
     *
     * @param string $key
     * @param mixed $value
     * @return Runtime
     */
    public function setEnvOption($key, $value)
    {
        if (array_key_exists('environments', $this->configuration) && is_array($this->configuration['environments'])) {
            if (array_key_exists($this->environment, $this->configuration['environments'])) {
                $this->configuration['environments'][$this->environment][$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Sets the working Environment
     *
     * @param string $environment Environment name
     * @return Runtime
     * @throws RuntimeException
     */
    public function setEnvironment($environment)
    {
        if (array_key_exists('environments', $this->configuration) && array_key_exists($environment, $this->configuration['environments'])) {
            $this->environment = $environment;
            return $this;
        }

        throw new RuntimeException(sprintf('The environment "%s" does not exists.', $environment), 100);
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
     * Retrieve the current working Stage
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
     */
    public function getTasks()
    {
        if (!array_key_exists('environments', $this->configuration) || !is_array($this->configuration['environments'])) {
            return [];
        }

        if (!array_key_exists($this->environment, $this->configuration['environments'])) {
            return [];
        }

        if (array_key_exists($this->stage, $this->configuration['environments'][$this->environment])) {
            if (is_array($this->configuration['environments'][$this->environment][$this->stage])) {
                return $this->configuration['environments'][$this->environment][$this->stage];
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
            default:
                return $this->runLocalCommand($cmd, $timeout);
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
     */
    public function runRemoteCommand($cmd, $jail, $timeout = 120)
    {
        $user = $this->getEnvOption('user', $this->getCurrentUser());
        $sudo = $this->getEnvOption('sudo', false);
        $host = $this->getHostName();
        $sshConfig = $this->getSSHConfig();

        $cmdDelegate = $cmd;
        if ($sudo === true) {
            $cmdDelegate = sprintf('sudo %s', $cmd);
        }

        $hostPath = rtrim($this->getEnvOption('host_path'), '/');
        if ($jail && $this->getReleaseId() !== null) {
            $cmdDelegate = sprintf('cd %s/releases/%s && %s', $hostPath, $this->getReleaseId(), $cmdDelegate);
        } elseif ($jail) {
            $cmdDelegate = sprintf('cd %s && %s', $hostPath, $cmdDelegate);
        }

        if ('localhost' !== $host) {
            $cmdRemote = str_replace('"', '\"', $cmdDelegate);
            $cmdLocal = sprintf('ssh -p %d %s %s@%s "%s"', $sshConfig['port'], $sshConfig['flags'], $user, $host, $cmdRemote);
        } else {
            $cmdLocal = $cmdDelegate;
        }

        return $this->runLocalCommand($cmdLocal, $timeout);
    }

    /**
     * Get the SSH configuration based on the environment
     *
     * @return array
     */
    public function getSSHConfig()
    {
        $sshConfig = $this->getEnvOption('ssh', ['port' => 22, 'flags' => '-q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no']);

        if ($this->getHostPort() !== null) {
            $sshConfig['port'] = $this->getHostPort();
        }

        if (!array_key_exists('port', $sshConfig)) {
            $sshConfig['port'] = '22';
        }

        if (!array_key_exists('flags', $sshConfig)) {
            $sshConfig['flags'] = '-q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no';
        }

        return $sshConfig;
    }

    /**
     * Get the current Host Port or default ssh port
     *
     * @return integer
     */
    public function getHostPort()
    {
        $info = explode(':', $this->getWorkingHost());
        return isset($info[1]) ? $info[1] : null;
    }

    /**
     * Get the current Host Name
     *
     * @return string
     */
    public function getHostName()
    {
        if (strpos($this->getWorkingHost(), ':') === false) {
            return $this->getWorkingHost();
        }

        $info = explode(':', $this->getWorkingHost());
        return $info[0];
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

    /**
     * Get the current user
     *
     * @return string
     */
    public function getCurrentUser()
    {
        $userData = posix_getpwuid(posix_geteuid());
        return $userData['name'];
    }

    /**
     * Shortcut for getting Branch information
     *
     * @return boolean|string
     */
    public function getBranch()
    {
        return $this->getEnvOption('branch', false);
    }

    /**
     * Guesses the Deploy Strategy to use
     *
     * @return StrategyInterface
     */
    public function guessStrategy()
    {
        $strategy = new RsyncStrategy();

        if ($this->getEnvOption('releases', false)) {
            $strategy = new ReleasesStrategy();
        }

        $strategy->setRuntime($this);
        return $strategy;
    }
}
