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

use Mage\Utils;
use Mage\Runtime\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Mage\Command\AbstractCommand;

/**
 * Command for Listing all Releases
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class ListCommand extends AbstractCommand
{
    /**
     * Configure the Command
     */
    protected function configure(): void
    {
        $this
            ->setName('releases:list')
            ->setDescription('List the releases on an environment')
            ->addArgument('environment', InputArgument::REQUIRED, 'Name of the environment to deploy to');
    }

    /**
     * Execute the Command
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->requireConfig();

        $utils = new Utils();
        $output->writeln('Starting <fg=blue>Magallanes</>');
        $output->writeln('');

        try {
            $this->runtime->setEnvironment($input->getArgument('environment'));

            if (!$this->runtime->getEnvOption('releases', false)) {
                throw new RuntimeException('Releases are not enabled', 70);
            }

            $output->writeln(sprintf('    Environment: <fg=green>%s</>', $this->runtime->getEnvironment()));
            $this->log(sprintf('Environment: %s', $this->runtime->getEnvironment()));

            if ($this->runtime->getConfigOption('log_file', false)) {
                $output->writeln(sprintf('    Logfile: <fg=green>%s</>', $this->runtime->getConfigOption('log_file')));
            }

            $output->writeln('');

            $hosts = $this->runtime->getEnvOption('hosts');
            if (!is_array($hosts) && !$hosts instanceof \Countable) {
                $hosts = [];
            }
            if (count($hosts) == 0) {
                $output->writeln('No hosts defined');
                $output->writeln('');
            } else {
                $hostPath = rtrim($this->runtime->getEnvOption('host_path'), '/');

                foreach ($hosts as $host) {
                    $this->runtime->setWorkingHost($host);

                    // Get List of Releases
                    $cmdListReleases = sprintf('ls -1 %s/releases', $hostPath);

                    /** @var Process $process */
                    $process = $this->runtime->runRemoteCommand($cmdListReleases, false);
                    if (!$process->isSuccessful()) {
                        throw new RuntimeException(sprintf('Unable to retrieve releases from host "%s"', $host), 80);
                    }

                    $releases = [];
                    if (trim($process->getOutput()) != '') {
                        $releases = explode("\n", trim($process->getOutput()));
                        rsort($releases);
                    }

                    if (count($releases) == 0) {
                        $output->writeln(
                            sprintf('    No releases available on host <fg=black;options=bold>%s</>:', $host)
                        );
                    } else {
                        // Get Current Release
                        $cmdCurrentRelease = sprintf('readlink -f %s/current', $hostPath);

                        /** @var Process $process */
                        $process = $this->runtime->runRemoteCommand($cmdCurrentRelease, false);
                        if (!$process->isSuccessful()) {
                            throw new RuntimeException(
                                sprintf('Unable to retrieve current release from host "%s"', $host),
                                85
                            );
                        }

                        $currentReleaseId = explode('/', trim($process->getOutput()));
                        $currentReleaseId = $currentReleaseId[count($currentReleaseId) - 1];

                        $output->writeln(sprintf('    Releases on host <fg=black;options=bold>%s</>:', $host));

                        foreach ($releases as $releaseId) {
                            $releaseDate = $utils->getReleaseDate($releaseId);

                            $output->write(sprintf(
                                '        Release ID: <fg=magenta>%s</> - Date: <fg=black;options=bold>%s</> [%s]',
                                $releaseId,
                                $releaseDate->format('Y-m-d H:i:s'),
                                $utils->getTimeDiff($releaseDate)
                            ));

                            if ($releaseId == $currentReleaseId) {
                                $output->writeln(' <fg=red;options=bold>[current]</>');
                            } else {
                                $output->writeln('');
                            }
                        }
                    }

                    $this->runtime->setWorkingHost(null);
                    $output->writeln('');
                }
            }
        } catch (RuntimeException $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
            $this->statusCode = $exception->getCode();
        }

        $output->writeln('Finished <fg=blue>Magallanes</>');

        return intval($this->statusCode);
    }
}
