<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Command\BuiltIn;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Mage\Command\AbstractCommand;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

/**
 * SSH Command, allows to connect via SSH to a host with environment
 * specific configuration.
 *
 * @author Yanick Witschi <https://github.com/Toflar>
 */
class SshCommand extends AbstractCommand
{
    /**
     * Configure the Command
     */
    protected function configure()
    {
        $this
            ->setName('ssh')
            ->setDescription('Connects to an environment via SSH so you do not have to remember/copy&paste the user, host, port, key etc.')
            ->addArgument('environment', InputArgument::REQUIRED, 'Name of the environment to connect to')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Host of environment to connect to (by default the first one in the hosts array).', 0)
        ;
    }

    /**
     * Executes the Command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->requireConfig();
        $env = $input->getArgument('environment');
        $this->runtime->setEnvironment($env);
        $user = $this->runtime->getEnvOption('user', $this->runtime->getCurrentUser());
        $sshConfig = $this->runtime->getSSHConfig();
        $hosts = $this->runtime->getEnvOption('hosts');
        $host = $input->getOption('host');

        // Allow to pass the host as string
        if (!is_numeric($host)) {
            if (!in_array($host, $hosts, true)) {
                $output->writeln(sprintf('<error>%s</error>', sprintf('The host "%s" does not exist in environment "%s".', $host, $env)));

                return 1;
            }
        } else {
            // Otherwise take the index (e.g. if you want to select the 3rd host, execute "--host=2"
            $host = (int) $host;
            if (!isset($hosts[$host])) {
                $output->writeln(sprintf('<error>%s</error>', sprintf('The host key "%s" does not exist in environment "%s".', $host, $env)));

                return 1;
            }

            $host = $hosts[$host];
        }

        $cmd = sprintf('ssh -p %d %s %s@%s', $sshConfig['port'], $sshConfig['flags'], $user, $host);
        $process = new Process($cmd);

        try {
            $process->setTty(true);
        } catch (RuntimeException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 126;
        }

        $output->writeln(sprintf('Creating secure connection using the following command: %s', $process->getCommandLine()));
        $process->mustRun();
        $output->writeln('Disconnecting...');

        return 0;
    }
}
