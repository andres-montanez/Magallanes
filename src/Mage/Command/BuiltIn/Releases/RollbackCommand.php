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
use Mage\Runtime\Exception\InvalidEnvironmentException;
use Mage\Runtime\Exception\DeploymentException;
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
    protected function configure()
    {
        $this
            ->setName('releases:rollback')
            ->setDescription('Rollback to a release on an environment')
            ->addArgument('environment', InputArgument::REQUIRED, 'Name of the environment to deploy to')
            ->addArgument('release', InputArgument::REQUIRED, 'The ID or the Index of the release to rollback to')
        ;
    }

    /**
     * Execute the Command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|mixed
     * @throws DeploymentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting <fg=blue>Magallanes</>');
        $output->writeln('');

        try {
            $this->runtime->setEnvironment($input->getArgument('environment'));
        } catch (InvalidEnvironmentException $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
            return $exception->getCode();
        }

        if (!$this->runtime->getEnvironmentConfig('releases', false)) {
            throw new DeploymentException('Releases are not enabled', 70);
        }

        // Check if the Release exists in all hosts
        $releaseToRollback = $input->getArgument('release');
        if ($releaseId = $this->checkReleaseAvailability($releaseToRollback)) {
            $this->runtime->setReleaseId($releaseId)->setRollback(true);

            $output->writeln(sprintf('    Environment: <fg=green>%s</>', $this->runtime->getEnvironment()));
            $this->log(sprintf('Environment: %s', $this->runtime->getEnvironment()));

            $output->writeln(sprintf('    Rollback to Release ID: <fg=green>%s</>', $this->runtime->getReleaseId()));
            $this->log(sprintf('Release ID: %s', $this->runtime->getReleaseId()));

            if ($this->runtime->getConfigOptions('log_file', false)) {
                $output->writeln(sprintf('    Logfile: <fg=green>%s</>', $this->runtime->getConfigOptions('log_file')));
            }

            $output->writeln('');

            // Get the Task Factory
            $this->taskFactory = new TaskFactory($this->runtime);

            try {
                $this->runDeployment($output);
            } catch (DeploymentException $exception) {
                $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
                return $exception->getCode();
            }
        } else {
            throw new DeploymentException(sprintf('Release %s is not available on all hosts', $releaseToRollback), 72);
        }

        $output->writeln('Finished <fg=blue>Magallanes</>');

        return 0;
    }

    /**
     * Check if the provided Release ID is available in all hosts
     *
     * @param string $releaseToRollback Release ID
     * @return bool
     */
    protected function checkReleaseAvailability($releaseToRollback)
    {
        $releaseIdCandidate = false;
        $hosts = $this->runtime->getEnvironmentConfig('hosts');
        $hostPath = rtrim($this->runtime->getEnvironmentConfig('host_path'), '/');

        $releaseAvailableInAllHosts = true;
        foreach ($hosts as $host) {
            $this->runtime->setWorkingHost($host);

            // Get List of Releases
            $cmdListReleases = sprintf('ls -1 %s/releases', $hostPath);

            /** @var Process $process */
            $process = $this->runtime->runRemoteCommand($cmdListReleases, false);
            if (!$process->isSuccessful()) {
                $releases = [];
            } else {
                $releases = explode(PHP_EOL, trim($process->getOutput()));
                rsort($releases);
            }

            if (in_array($releaseToRollback, $releases)) {
                if ($releaseIdCandidate === false) {
                    $releaseIdCandidate = $releaseToRollback;
                } else {
                    if ($releaseIdCandidate != $releaseToRollback) {
                        $releaseAvailableInAllHosts = false;
                    }
                }
            }

            $this->runtime->setWorkingHost(null);
        }

        if ($releaseAvailableInAllHosts) {
            return $releaseIdCandidate;
        }

        return false;
    }
}
