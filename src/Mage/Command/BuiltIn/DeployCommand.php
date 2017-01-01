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

use Mage\Runtime\Exception\DeploymentException;
use Mage\Runtime\Exception\InvalidEnvironmentException;
use Mage\Runtime\Exception\RuntimeException;
use Mage\Runtime\RuntimeInterface;
use Mage\Task\ErrorException;
use Mage\Task\ExecuteOnRollbackInterface;
use Mage\Task\AbstractTask;
use Mage\Task\SkipException;
use Mage\Task\TaskFactory;
use Mage\Utils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Mage\Command\AbstractCommand;

/**
 * The Deployment Command
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class DeployCommand extends AbstractCommand
{
    /**
     * @var TaskFactory
     */
    protected $taskFactory;

    /**
     * Configure the Command
     */
    protected function configure()
    {
        $this
            ->setName('deploy')
            ->setDescription('Deploy code to hosts')
            ->addArgument('environment', InputArgument::REQUIRED, 'Name of the environment to deploy to')
        ;
    }

    /**
     * Execute the Command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|mixed
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

        $output->writeln(sprintf('    Environment: <fg=green>%s</>', $this->runtime->getEnvironment()));
        $this->log(sprintf('Environment: %s', $this->runtime->getEnvironment()));

        if ($this->runtime->getEnvironmentConfig('releases', false)) {
            $this->runtime->setReleaseId(date('YmdHis'));
            $output->writeln(sprintf('    Release ID: <fg=green>%s</>', $this->runtime->getReleaseId()));
            $this->log(sprintf('Release ID: %s', $this->runtime->getReleaseId()));
        }

        if ($this->runtime->getConfigOptions('log_file', false)) {
            $output->writeln(sprintf('    Logfile: <fg=green>%s</>', $this->runtime->getConfigOptions('log_file')));
        }

        $output->writeln('');

        try {
            $this->taskFactory = new TaskFactory($this->runtime);
            $this->runDeployment($output);
        } catch (DeploymentException $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
            return $exception->getCode();
        }

        $output->writeln('Finished <fg=blue>Magallanes</>');

        return 0;
    }

    /**
     * Run the Deployment Process
     *
     * @param OutputInterface $output
     * @throws DeploymentException
     */
    protected function runDeployment(OutputInterface $output)
    {
        // Run Pre Deploy Tasks
        $this->runtime->setStage(RuntimeInterface::PRE_DEPLOY);
        $preDeployTasks = $this->runtime->getTasks();

        if ($this->runtime->getEnvironmentConfig('branch', false) && !$this->runtime->inRollback()) {
            if (!in_array('git/change-branch', $preDeployTasks)) {
                array_unshift($preDeployTasks, 'git/change-branch');
            }
        }

        if ($this->runtime->getEnvironmentConfig('releases', false) && !$this->runtime->inRollback()) {
            if (!in_array('deploy/targz/prepare', $preDeployTasks)) {
                array_push($preDeployTasks, 'deploy/targz/prepare');
            }
        }

        if (!$this->runTasks($output, $preDeployTasks)) {
            throw new DeploymentException(sprintf('    Tasks failed on %s stage, halting deployment', $this->getStageName()), 500);
        }

        // Run On Deploy Tasks
        $hosts = $this->runtime->getEnvironmentConfig('hosts');
        if (count($hosts) == 0) {
            $output->writeln('    No hosts defined, skipping On Deploy tasks');
            $output->writeln('');
        } else {
            $this->runtime->setStage(RuntimeInterface::ON_DEPLOY);
            $onDeployTasks = $this->runtime->getTasks();

            if ($this->runtime->getEnvironmentConfig('releases', false) && !$this->runtime->inRollback()) {
                if (!in_array('deploy/targz/copy', $onDeployTasks)) {
                    array_unshift($onDeployTasks, 'deploy/targz/copy');
                }
            } else {
                if (!in_array('deploy/rsync', $onDeployTasks) && !$this->runtime->inRollback()) {
                    array_unshift($onDeployTasks, 'deploy/rsync');
                }
            }

            if ($this->runtime->getEnvironmentConfig('releases', false) && !$this->runtime->inRollback()) {
                if (!in_array('deploy/release/prepare', $onDeployTasks)) {
                    array_unshift($onDeployTasks, 'deploy/release/prepare');
                }
            }

            foreach ($hosts as $host) {
                $this->runtime->setWorkingHost($host);
                if (!$this->runTasks($output, $onDeployTasks)) {
                    throw new DeploymentException(sprintf('    Tasks failed on <fg=black;options=bold>%s</> stage, halting deployment', $this->getStageName()), 500);
                }
                $this->runtime->setWorkingHost(null);
            }
        }

        // Run On Release Tasks
        $hosts = $this->runtime->getEnvironmentConfig('hosts');
        if (count($hosts) == 0) {
            $output->writeln('    No hosts defined, skipping On Release tasks');
            $output->writeln('');
        } else {
            $this->runtime->setStage(RuntimeInterface::ON_RELEASE);
            $onReleaseTasks = $this->runtime->getTasks();

            if ($this->runtime->getEnvironmentConfig('releases', false)) {
                if (!in_array('deploy/release', $onReleaseTasks)) {
                    array_unshift($onReleaseTasks, 'deploy/release');
                }
            }

            foreach ($hosts as $host) {
                $this->runtime->setWorkingHost($host);
                if (!$this->runTasks($output, $onReleaseTasks)) {
                    throw new DeploymentException(sprintf('    Tasks failed on <fg=black;options=bold>%s</> stage, halting deployment', $this->getStageName()), 500);
                }
                $this->runtime->setWorkingHost(null);
            }
        }

        // Run Post Release Tasks
        $hosts = $this->runtime->getEnvironmentConfig('hosts');
        if (count($hosts) == 0) {
            $output->writeln('    No hosts defined, skipping Post Release tasks');
            $output->writeln('');
        } else {
            $this->runtime->setStage(RuntimeInterface::POST_RELEASE);
            $postReleaseTasks = $this->runtime->getTasks();

            if ($this->runtime->getEnvironmentConfig('releases', false) && !$this->runtime->inRollback()) {
                if (!in_array('deploy/release/cleanup', $postReleaseTasks)) {
                    array_unshift($postReleaseTasks, 'deploy/release/cleanup');
                }
            }

            foreach ($hosts as $host) {
                $this->runtime->setWorkingHost($host);
                if (!$this->runTasks($output, $postReleaseTasks)) {
                    throw new DeploymentException(sprintf('    Tasks failed on <fg=black;options=bold>%s</> stage, halting deployment', $this->getStageName()), 500);
                }
                $this->runtime->setWorkingHost(null);
            }
        }

        // Run Post Deploy Tasks
        $this->runtime->setStage(RuntimeInterface::POST_DEPLOY);
        $postDeployTasks = $this->runtime->getTasks();
        if ($this->runtime->getEnvironmentConfig('releases', false) && !$this->runtime->inRollback()) {
            if (!in_array('deploy/targz/cleanup', $postDeployTasks)) {
                array_unshift($postDeployTasks, 'deploy/targz/cleanup');
            }
        }

        if ($this->runtime->getEnvironmentConfig('branch', false) && !$this->runtime->inRollback()) {
            if (!in_array('git/change-branch', $postDeployTasks)) {
                array_push($postDeployTasks, 'git/change-branch');
            }
        }

        if (!$this->runTasks($output, $postDeployTasks)) {
            throw new DeploymentException(sprintf('    Tasks failed on <fg=black;options=bold>%s</> stage, halting deployment', $this->getStageName()), 500);
        }
    }

    /**
     * Runs all the tasks
     *
     * @param OutputInterface $output
     * @param $tasks
     * @return bool
     * @throws RuntimeException
     */
    protected function runTasks(OutputInterface $output, $tasks)
    {
        if (count($tasks) == 0) {
            $output->writeln(sprintf('    No tasks defined for <fg=black;options=bold>%s</>', $this->getStageName()));
            $output->writeln('');
            return true;
        }

        if ($this->runtime->getWorkingHost()) {
            $output->writeln(sprintf('    Starting <fg=black;options=bold>%s</> tasks on host <fg=black;options=bold>%s</>:', $this->getStageName(), $this->runtime->getWorkingHost()));
        } else {
            $output->writeln(sprintf('    Starting <fg=black;options=bold>%s</> tasks:', $this->getStageName()));
        }

        $totalTasks = count($tasks);
        $succeededTasks = 0;

        foreach ($tasks as $taskName) {
            /** @var AbstractTask $task */
            $task = $this->taskFactory->get($taskName);
            $output->write(sprintf('        Running <fg=magenta>%s</> ... ', $task->getDescription()));
            $this->log(sprintf('Running task %s (%s)', $task->getDescription(), $task->getName()));

            if ($this->runtime->inRollback() && !$task instanceof ExecuteOnRollbackInterface) {
                $succeededTasks++;
                $output->writeln('<fg=yellow>SKIPPED</>');
                $this->log(sprintf('Task %s (%s) finished with SKIPPED, it was in a Rollback', $task->getDescription(), $task->getName()));
            } else {
                try {
                    if ($task->execute()) {
                        $succeededTasks++;
                        $output->writeln('<fg=green>OK</>');
                        $this->log(sprintf('Task %s (%s) finished with OK', $task->getDescription(), $task->getName()));
                    } else {
                        $output->writeln('<fg=red>FAIL</>');
                        $this->log(sprintf('Task %s (%s) finished with FAIL', $task->getDescription(), $task->getName()));
                    }
                } catch (SkipException $exception) {
                    $succeededTasks++;
                    $output->writeln('<fg=yellow>SKIPPED</>');
                    $this->log(sprintf('Task %s (%s) finished with SKIPPED, thrown SkipException', $task->getDescription(), $task->getName()));
                } catch (ErrorException $exception) {
                    $output->writeln(sprintf('<fg=red>FAIL</> [%s]', $exception->getTrimmedMessage()));
                    $this->log(sprintf('Task %s (%s) finished with FAIL, with Error "%s"', $task->getDescription(), $task->getName(), $exception->getMessage()));
                }
            }
        }

        if ($succeededTasks != $totalTasks) {
            $alertColor = 'red';
        } else {
            $alertColor = 'green';
        }

        $output->writeln(sprintf('    Finished <fg=black;options=bold>%s</> tasks: <fg=%s>%d/%d</> done.', $this->getStageName(), $alertColor, $succeededTasks, $totalTasks));
        $output->writeln('');

        return ($succeededTasks == $totalTasks);
    }

    /**
     * Get the Human friendly Stage name
     *
     * @return string
     */
    protected function getStageName()
    {
        return Utils::getStageName($this->runtime->getStage());
    }
}
