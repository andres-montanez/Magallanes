<?php

/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Command\BuiltIn\Releases;

use Mage\Task\TaskFactory;
use Mage\Runtime\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Mage\Command\BuiltIn\DeployCommand;

/**
 * Command for Rolling Back a Releases
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class RollbackCommand extends DeployCommand
{
    /**
     * Configure the Command
     */
    protected function configure(): void
    {
        $this
            ->setName('releases:rollback')
            ->setDescription('Rollback to a release on an environment')
            ->addArgument('environment', InputArgument::REQUIRED, 'Name of the environment to deploy to')
            ->addArgument('release', InputArgument::REQUIRED, 'The ID or the Index of the release to rollback to');
    }

    /**
     * Execute the Command
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->requireConfig();

        $output->writeln('Starting <fg=blue>Magallanes</>');
        $output->writeln('');

        try {
            $this->runtime->setEnvironment($input->getArgument('environment'));

            $strategy = $this->runtime->guessStrategy();
            $this->taskFactory = new TaskFactory($this->runtime);

            if (!$this->runtime->getEnvOption('releases', false)) {
                throw new RuntimeException('Releases are not enabled', 70);
            }

            $releaseToRollback = $input->getArgument('release');
            if ($this->checkReleaseAvailability($releaseToRollback) === false) {
                throw new RuntimeException(
                    sprintf('Release "%s" is not available on all hosts', $releaseToRollback),
                    72
                );
            }

            $this->runtime->setReleaseId($releaseToRollback)->setRollback(true);

            $output->writeln(sprintf('    Environment: <fg=green>%s</>', $this->runtime->getEnvironment()));
            $this->log(sprintf('Environment: %s', $this->runtime->getEnvironment()));

            $output->writeln(sprintf('    Rollback to Release Id: <fg=green>%s</>', $this->runtime->getReleaseId()));
            $this->log(sprintf('Release ID: %s', $this->runtime->getReleaseId()));

            if ($this->runtime->getConfigOption('log_file', false)) {
                $output->writeln(sprintf('    Logfile: <fg=green>%s</>', $this->runtime->getConfigOption('log_file')));
            }

            $output->writeln(sprintf('    Strategy: <fg=green>%s</>', $strategy->getName()));

            $output->writeln('');
            $this->runDeployment($output, $strategy);
        } catch (RuntimeException $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
            $this->statusCode = $exception->getCode();
        }

        $output->writeln('Finished <fg=blue>Magallanes</>');

        return intval($this->statusCode);
    }

    /**
     * Check if the provided Release ID is available in all hosts
     */
    protected function checkReleaseAvailability(string $releaseToRollback): bool
    {
        $hosts = $this->runtime->getEnvOption('hosts');
        $hostPath = rtrim($this->runtime->getEnvOption('host_path'), '/');

        $availableInHosts = 0;
        foreach ($hosts as $host) {
            $releases = [];
            $this->runtime->setWorkingHost($host);

            // Get List of Releases
            $cmdListReleases = sprintf('ls -1 %s/releases', $hostPath);

            /** @var Process $process */
            $process = $this->runtime->runRemoteCommand($cmdListReleases, false);
            if ($process->isSuccessful()) {
                $releases = explode("\n", trim($process->getOutput()));
                rsort($releases);
            }

            if (in_array($releaseToRollback, $releases)) {
                $availableInHosts++;
            }

            $this->runtime->setWorkingHost(null);
        }

        if ($availableInHosts === count($hosts)) {
            return (bool) $releaseToRollback;
        }

        return false;
    }
}
