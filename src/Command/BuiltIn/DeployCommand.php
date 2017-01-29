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

use Mage\Deploy\Strategy\StrategyInterface;
use Mage\Runtime\Exception\RuntimeException;
use Mage\Runtime\Runtime;
use Mage\Task\ExecuteOnRollbackInterface;
use Mage\Task\AbstractTask;
use Mage\Task\Exception\ErrorException;
use Mage\Task\Exception\SkipException;
use Mage\Task\TaskFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
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
            ->addOption('branch', null, InputOption::VALUE_REQUIRED, 'Force to switch to a branch other than the one defined', false)
        ;
    }

    /**
     * Execute the Command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->requireConfig();

        $output->writeln('Starting <fg=blue>Magallanes</>');
        $output->writeln('');

        try {
            $this->runtime->setEnvironment($input->getArgument('environment'));

            $strategy = $this->runtime->guessStrategy();
            $this->taskFactory = new TaskFactory($this->runtime);

            $output->writeln(sprintf('    Environment: <fg=green>%s</>', $this->runtime->getEnvironment()));
            $this->log(sprintf('Environment: %s', $this->runtime->getEnvironment()));

            if ($this->runtime->getEnvOption('releases', false)) {
                $this->runtime->generateReleaseId();
                $output->writeln(sprintf('    Release ID: <fg=green>%s</>', $this->runtime->getReleaseId()));
                $this->log(sprintf('Release ID: %s', $this->runtime->getReleaseId()));
            }

            if ($this->runtime->getConfigOption('log_file', false)) {
                $output->writeln(sprintf('    Logfile: <fg=green>%s</>', $this->runtime->getConfigOption('log_file')));
            }

            $output->writeln(sprintf('    Strategy: <fg=green>%s</>', $strategy->getName()));

            if ($input->getOption('branch') !== false) {
                $this->runtime->setEnvOption('branch', $input->getOption('branch'));
            }

            if ($this->runtime->getEnvOption('branch', false)) {
                $output->writeln(sprintf('    Branch: <fg=green>%s</>', $this->runtime->getEnvOption('branch')));
            }

            $output->writeln('');
            $this->runDeployment($output, $strategy);
        } catch (RuntimeException $exception) {
            $output->writeln('');
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
            $output->writeln('');
            $this->statusCode = 7;
        }

        $output->writeln('Finished <fg=blue>Magallanes</>');

        return $this->statusCode;
    }

    /**
     * Run the Deployment Process
     *
     * @param OutputInterface $output
     * @param StrategyInterface $strategy
     * @throws RuntimeException
     */
    protected function runDeployment(OutputInterface $output, StrategyInterface $strategy)
    {
        // Run "Pre Deploy" Tasks
        $this->runtime->setStage(Runtime::PRE_DEPLOY);
        if (!$this->runTasks($output, $strategy->getPreDeployTasks())) {
            throw $this->getException();
        }

        // Run "On Deploy" Tasks
        $this->runtime->setStage(Runtime::ON_DEPLOY);
        $this->runOnHosts($output, $strategy->getOnDeployTasks());

        // Run "On Release" Tasks
        $this->runtime->setStage(Runtime::ON_RELEASE);
        $this->runOnHosts($output, $strategy->getOnReleaseTasks());

        // Run "Post Release" Tasks
        $this->runtime->setStage(Runtime::POST_RELEASE);
        $this->runOnHosts($output, $strategy->getPostReleaseTasks());

        // Run "Post Deploy" Tasks
        $this->runtime->setStage(Runtime::POST_DEPLOY);
        if (!$this->runTasks($output, $strategy->getPostDeployTasks())) {
            throw $this->getException();
        }
    }

    protected function runOnHosts(OutputInterface $output, $tasks)
    {
        $hosts = $this->runtime->getEnvOption('hosts');
        if (count($hosts) == 0) {
            $output->writeln(sprintf('    No hosts defined, skipping %s tasks', $this->getStageName()));
            $output->writeln('');
            return true;
        }

        foreach ($hosts as $host) {
            $this->runtime->setWorkingHost($host);
            if (!$this->runTasks($output, $tasks)) {
                $this->runtime->setWorkingHost(null);
                throw $this->getException();
            }
            $this->runtime->setWorkingHost(null);
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
            $output->writeln(sprintf('    No tasks defined for <fg=black;options=bold>%s</> stage', $this->getStageName()));
            $output->writeln('');
            return true;
        }

        if ($this->runtime->getWorkingHost() !== null) {
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
                        $this->statusCode = 180;
                        $this->log(sprintf('Task %s (%s) finished with FAIL', $task->getDescription(), $task->getName()));
                    }
                } catch (SkipException $exception) {
                    $succeededTasks++;
                    $output->writeln('<fg=yellow>SKIPPED</>');
                    $this->log(sprintf('Task %s (%s) finished with SKIPPED, thrown SkipException', $task->getDescription(), $task->getName()));
                } catch (ErrorException $exception) {
                    $output->writeln(sprintf('<fg=red>ERROR</> [%s]', $exception->getTrimmedMessage()));
                    $this->log(sprintf('Task %s (%s) finished with FAIL, with Error "%s"', $task->getDescription(), $task->getName(), $exception->getMessage()));
                    $this->statusCode = 190;
                }
            }

            if ($this->statusCode !== 0) {
                break;
            }
        }

        $alertColor = 'red';
        if ($succeededTasks == $totalTasks) {
            $alertColor = 'green';
        }

        $output->writeln(sprintf('    Finished <fg=%s>%d/%d</> tasks for <fg=black;options=bold>%s</>.', $alertColor, $succeededTasks, $totalTasks, $this->getStageName()));
        $output->writeln('');

        return ($succeededTasks == $totalTasks);
    }

    /**
     * Exception for halting the the current process
     */
    protected function getException()
    {
        return new RuntimeException(sprintf('Stage "%s" did not finished successfully, halting command.', $this->getStageName()), 50);
    }
}
