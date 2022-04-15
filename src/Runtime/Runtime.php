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
use Mage\Task\AbstractTask;

/**
 * Runtime is a container of all run in time configuration, stages of progress, hosts being deployed, etc.
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class Runtime
{
    public const PRE_DEPLOY = 'pre-deploy';
    public const ON_DEPLOY = 'on-deploy';
    public const POST_DEPLOY = 'post-deploy';
    public const ON_RELEASE = 'on-release';
    public const POST_RELEASE = 'post-release';

    /**
     * @var array<string, mixed> Magallanes configuration
     */
    protected array $configuration = [];

    /**
     * @var string|null Environment being deployed
     */
    protected ?string $environment = null;

    /**
     * @var string|null Stage of Deployment
     */
    protected ?string $stage = null;

    /**
     * @var string|null The host being deployed to
     */
    protected ?string $workingHost = null;

    /**
     * @var string|null The Release ID
     */
    protected ?string $releaseId = null;

    /**
     * @var array<string, string> Hold a bag of variables for sharing information between tasks, if needed
     */
    protected $vars = [];

    protected ?LoggerInterface $logger = null;

    /**
     * @var bool Indicates if a Rollback operation is in progress
     */
    protected bool $rollback = false;

    public function isWindows(): bool
    {
        return stripos(PHP_OS, 'WIN') === 0;
    }

    public function hasPosix(): bool
    {
        return function_exists('posix_getpwuid');
    }

    /**
     * Generate the Release ID
     */
    public function generateReleaseId(): self
    {
        $this->setReleaseId(date('YmdHis'));
        return $this;
    }

    /**
     * Sets the Release ID
     */
    public function setReleaseId(string $releaseId): self
    {
        $this->releaseId = $releaseId;
        return $this;
    }

    /**
     * Retrieve the current Release ID
     */
    public function getReleaseId(): ?string
    {
        return $this->releaseId;
    }

    /**
     * Sets the Runtime in Rollback mode On or Off
     */
    public function setRollback(bool $inRollback): self
    {
        $this->rollback = $inRollback;
        return $this;
    }

    /**
     * Indicates if Runtime is in rollback
     */
    public function inRollback(): bool
    {
        return $this->rollback;
    }

    /**
     * Sets a value in the Vars bag
     */
    public function setVar(string $key, string $value): self
    {
        $this->vars[$key] = $value;
        return $this;
    }

    /**
     * Retrieve a value from the Vars bag, or a default (null) if not set
     */
    public function getVar(string $key, mixed $default = null): ?string
    {
        if (array_key_exists($key, $this->vars)) {
            return $this->vars[$key];
        }

        return $default;
    }

    /**
     * Sets the Logger instance
     */
    public function setLogger(?LoggerInterface $logger = null): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Sets the Magallanes Configuration to the Runtime
     *
     * @param array<string, mixed> $configuration
     */
    public function setConfiguration(array $configuration): self
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * Retrieve the Configuration
     *
     * @return array<string, mixed> $configuration
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * Retrieves the Configuration Option for a specific section in the configuration
     */
    public function getConfigOption(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->configuration)) {
            return $this->configuration[$key];
        }

        return $default;
    }

    /**
     * Returns the Configuration Option for a specific section the current Environment
     */
    public function getEnvOption(string $key, mixed $default = null): mixed
    {
        if (
            !array_key_exists('environments', $this->configuration) ||
            !is_array($this->configuration['environments'])
        ) {
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
     * @param array<string, mixed> $defaultEnv
     * @return array<string, mixed>
     */
    public function getMergedOption(string $key, array $defaultEnv = []): array
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
     */
    public function setEnvOption(string $key, mixed $value): self
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
     * @throws RuntimeException
     */
    public function setEnvironment(string $environment): self
    {
        if (
            array_key_exists('environments', $this->configuration) &&
            array_key_exists($environment, $this->configuration['environments'])
        ) {
            $this->environment = $environment;
            return $this;
        }

        throw new RuntimeException(sprintf('The environment "%s" does not exists.', $environment), 100);
    }

    /**
     * Returns the current working Environment
     */
    public function getEnvironment(): ?string
    {
        return $this->environment;
    }

    /**
     * Sets the working stage
     */
    public function setStage(string $stage): self
    {
        $this->stage = $stage;
        return $this;
    }

    /**
     * Retrieve the current working Stage
     */
    public function getStage(): ?string
    {
        return $this->stage;
    }

    /**
     * Retrieve the defined Tasks for the current Environment and Stage
     *
     * @return string[]
     */
    public function getTasks(): array
    {
        if (
            !array_key_exists('environments', $this->configuration) ||
            !is_array($this->configuration['environments'])
        ) {
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
     */
    public function setWorkingHost(?string $host): self
    {
        $this->workingHost = $host;
        return $this;
    }

    /**
     * Retrieve the working Host
     */
    public function getWorkingHost(): ?string
    {
        return $this->workingHost;
    }

    /**
     * Logs a Message into the Logger
     */
    public function log(string $message, string $level = LogLevel::DEBUG): void
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->log($level, $message);
        }
    }

    /**
     * Executes a command, it will be run Locally or Remotely based on the working Stage
     */
    public function runCommand(string $cmd, int $timeout = 120): Process
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
     */
    public function runLocalCommand(string $cmd, int $timeout = 120): Process
    {
        $this->log($cmd, LogLevel::INFO);

        $process = Process::fromShellCommandline($cmd);
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
     */
    public function runRemoteCommand(string $cmd, bool $jail, int $timeout = 120): Process
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

        $cmdRemote = str_replace('"', '\"', $cmdDelegate);
        $cmdLocal = sprintf(
            'ssh -p %d %s %s@%s "%s"',
            $sshConfig['port'],
            $sshConfig['flags'],
            $user,
            $host,
            $cmdRemote
        );

        return $this->runLocalCommand($cmdLocal, $timeout);
    }

    /**
     * Get the SSH configuration based on the environment
     *
     * @return array<string, string>
     */
    public function getSSHConfig(): array
    {
        $sshConfig = $this->getEnvOption(
            'ssh',
            [
                'port' => 22,
                'flags' => '-q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no'
            ]
        );

        if ($this->getHostPort() !== null) {
            $sshConfig['port'] = $this->getHostPort();
        }

        if (!array_key_exists('port', $sshConfig)) {
            $sshConfig['port'] = '22';
        }

        if (!array_key_exists('flags', $sshConfig)) {
            $sshConfig['flags'] = '-q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no';
        }

        if (!array_key_exists('timeout', $sshConfig)) {
            $sshConfig['timeout'] = 300;
        }

        return $sshConfig;
    }

    /**
     * Get the current Host Port or default ssh port
     */
    public function getHostPort(): ?int
    {
        $info = explode(':', strval($this->getWorkingHost()));
        return isset($info[1]) ? intval($info[1]) : null;
    }

    /**
     * Get the current Host Name
     */
    public function getHostName(): ?string
    {
        if (strpos(strval($this->getWorkingHost()), ':') === false) {
            return $this->getWorkingHost();
        }

        $info = explode(':', $this->getWorkingHost());
        return strval($info[0]);
    }

    /**
     * Gets a Temporal File name
     */
    public function getTempFile(): string
    {
        return tempnam(sys_get_temp_dir(), 'mage');
    }

    /**
     * Get the current user
     */
    public function getCurrentUser(): string
    {
        if ($this->hasPosix()) {
            $userData = posix_getpwuid(posix_geteuid());
            return $userData['name'];
        }

        // Windows fallback
        return strval(getenv('USERNAME'));
    }

    /**
     * Shortcut for getting Branch information
     *
     * @return bool|string
     */
    public function getBranch(): mixed
    {
        return $this->getEnvOption('branch', false);
    }

    /**
     * Shortcut for getting Tag information
     *
     * @return bool|string
     */
    public function getTag(): mixed
    {
        return $this->getEnvOption('tag', false);
    }

    /**
     * Guesses the Deploy Strategy to use
     */
    public function guessStrategy(): StrategyInterface
    {
        $strategy = new RsyncStrategy();

        if ($this->getEnvOption('releases', false)) {
            $strategy = new ReleasesStrategy();
        }

        $strategy->setRuntime($this);
        return $strategy;
    }
}
