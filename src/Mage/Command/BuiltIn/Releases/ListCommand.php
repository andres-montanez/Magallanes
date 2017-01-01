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
use Mage\Runtime\Exception\InvalidEnvironmentException;
use Mage\Runtime\Exception\DeploymentException;
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
    protected function configure()
    {
        $this
            ->setName('releases:list')
            ->setDescription('List the releases on an environment')
            ->addArgument('environment', InputArgument::REQUIRED, 'Name of the environment to deploy to')
        ;
    }

    /**
     * Execute the Command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|mixed
     * @throws DeploymentException
     * @throws RuntimeException
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
            throw new DeploymentException('Releases are not enabled', 700);
        }

        $output->writeln(sprintf('    Environment: <fg=green>%s</>', $this->runtime->getEnvironment()));
        $this->log(sprintf('Environment: %s', $this->runtime->getEnvironment()));

        if ($this->runtime->getConfigOptions('log_file', false)) {
            $output->writeln(sprintf('    Logfile: <fg=green>%s</>', $this->runtime->getConfigOptions('log_file')));
        }

        $output->writeln('');

        $hosts = $this->runtime->getEnvironmentConfig('hosts');
        if (count($hosts) == 0) {
            $output->writeln('No hosts defined');
            $output->writeln('');
        } else {
            $hostPath = rtrim($this->runtime->getEnvironmentConfig('host_path'), '/');

            foreach ($hosts as $host) {
                $this->runtime->setWorkingHost($host);

                // Get List of Releases
                $cmdListReleases = sprintf('ls -1 %s/releases', $hostPath);

                /** @var Process $process */
                $process = $this->runtime->runRemoteCommand($cmdListReleases, false);
                if (!$process->isSuccessful()) {
                    throw new RuntimeException(sprintf('Unable to retrieve releases from host %s', $host), 800);
                }

                $releases = explode(PHP_EOL, trim($process->getOutput()));
                rsort($releases);

                if (count($releases) == 0) {
                    $output->writeln(sprintf('    No releases available on host <fg=black;options=bold>%s</>:', $host));
                } else {
                    // Get Current Release
                    $cmdCurrentRelease = sprintf('readlink -f %s/current', $hostPath);

                    /** @var Process $process */
                    $process = $this->runtime->runRemoteCommand($cmdCurrentRelease, false);
                    if (!$process->isSuccessful()) {
                        throw new RuntimeException(sprintf('Unable to retrieve current release from host %s', $host), 850);
                    }

                    $currentReleaseId = explode('/', trim($process->getOutput()));
                    $currentReleaseId = $currentReleaseId[count($currentReleaseId) - 1];

                    $output->writeln(sprintf('    Releases on host <fg=black;options=bold>%s</>:', $host));

                    foreach ($releases as $releaseId) {
                        $releaseDate = Utils::getReleaseDate($releaseId);

                        $output->write(sprintf('        Release ID: <fg=magenta>%s</> - Date: <fg=black;options=bold>%s</> [%s]',
                            $releaseId,
                            $releaseDate->format('Y-m-d H:i:s'),
                            Utils::getTimeDiff($releaseDate)
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

        $output->writeln('Finished <fg=blue>Magallanes</>');

        return 0;
    }
}
