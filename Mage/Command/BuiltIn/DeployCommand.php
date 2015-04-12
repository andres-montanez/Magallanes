<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
* (c) Alex V Kotelnikov <gudron@gudron.me>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Command\BuiltIn;

use Mage\Command\AbstractCommand;
use Mage\Command\RequiresEnvironment;
use Mage\Task\Factory;
use Mage\Task\AbstractTask;
use Mage\Task\Releases\SkipOnOverride;
use Mage\Task\ErrorWithMessageException;
use Mage\Task\RollbackException;
use Mage\Task\SkipException;
use Mage\Console;
use Mage\Config;
use Mage\Mailer;
use Exception;

/**
 * Command for Deploying
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class DeployCommand extends AbstractCommand implements RequiresEnvironment
{
    const DEFAULT_RELEASE_IS_ENABLED = false;
    const DEPLOY_STRATEGY_DISABLED = 'disabled';
    const DEPLOY_STRATEGY_RSYNC = 'rsync';
    const DEPLOY_STRATEGY_TARGZ = 'targz';
    const DEPLOY_STRATEGY_GIT_REBASE = 'git-rebase';
    const DEPLOY_STRATEGY_GIT_REMOTE_CACHE = 'git-remote-cache';
    const DEPLOY_STRATEGY_GUESS = 'guess';
    const DEFAULT_DEPLOY_STRATEGY = self::DEPLOY_STRATEGY_GUESS;

    /**
     * Deploy has Failed
     * @var string
     */
    const FAILED = 'failed';

    /**
     * Deploy has Succeded
     * @var string
     */
    const SUCCEDED = 'succeded';

    /**
     * Deploy is in progress
     * @var string
     */
    const IN_PROGRESS = 'in_progress';

    /**
     * Stage where possible throw Rollback Exception
     * @var array
     */
    public $acceptedStagesToRollback = array(
        AbstractTask::STAGE_POST_RELEASE,
        AbstractTask::STAGE_POST_DEPLOY
    );

    /**
     * Time the Deployment has Started
     * @var integer
     */
    protected $startTime = null;

    /**
     * Time the Deployment has Started to the current Host
     * @var integer
     */
    protected $startTimeHosts = null;

    /**
     * Time the Deployment to the Hosts has Finished
     * @var integer
     */
    protected $endTimeHosts = null;

    /**
     * Quantity of Hosts to Deploy to.
     * @var integer
     */
    protected $hostsCount = 0;

    /**
     * Current Status of the Deployment (in progress, succeded, failed)
     * @var string
     */
    protected static $deployStatus = 'in_progress';

    /**
     * Total of Failed tasks
     * @var integer
     */
    protected static $failedTasks = 0;

    /**
     * Returns the Status of the Deployment
     *
     * @return string
     */
    public static function getStatus()
    {
        return self::$deployStatus;
    }

    /**
     * Deploys the Application
     * @see \Mage\Command\AbstractCommand::run()
     */
    public function run()
    {
        $exitCode = 240;

        // Check if Environment is not Locked
        $lockFile = getcwd() . '/.mage/' . $this->getConfig()->getEnvironment() . '.lock';
        if (file_exists($lockFile)) {
            Console::output('<red>This environment is locked!</red>', 1, 2);
            echo file_get_contents($lockFile);
            return 231;
        }

        // Check for running instance and Lock
        if (file_exists(getcwd() . '/.mage/~working.lock')) {
            Console::output('<red>There is already an instance of Magallanes running!</red>', 1, 2);
            return 230;
        } else {
            touch(getcwd() . '/.mage/~working.lock');
        }

        // Release ID
        $this->getConfig()->setReleaseId(date('YmdHis'));

        // Deploy Summary
        Console::output('<bold>Deploy summary</bold>', 1, 1);

        // Deploy Summary - Environment
        Console::output('<bold>Environment:</bold> <purple>' . $this->getConfig()->getEnvironment() . '</purple>', 2, 1);

        // Deploy Summary - Releases
        if ($this->getConfig()->release('enabled', false)) {
            Console::output('<bold>Release ID:</bold>  <purple>' . $this->getConfig()->getReleaseId() . '</purple>', 2, 1);
        }

        // Deploy Summary - SCM
        if ($this->getConfig()->deployment('scm', false)) {
            $scmConfig = $this->getConfig()->deployment('scm');
            if (isset($scmConfig['branch'])) {
                Console::output('<bold>SCM Branch:</bold>  <purple>' . $scmConfig['branch'] . '</purple>', 2, 1);
            }
        }

        // Deploy Summary - Separator Line
        Console::output('', 0, 1);

        $this->startTime = time();

        // Run Pre-Deployment Tasks
        $this->runNonDeploymentTasks(AbstractTask::STAGE_PRE_DEPLOY, $this->getConfig(), 'Pre-Deployment');

        // Check Status
        if (self::$failedTasks > 0) {
            self::$deployStatus = self::FAILED;
            Console::output('A total of <bold>' . self::$failedTasks . '</bold> deployment tasks failed: <red>ABORTING</red>', 1, 2);
        } else {
            // Run Deployment Tasks
            $this->runDeploymentTasks();

            // Check Status
            if (self::$failedTasks > 0) {
                self::$deployStatus = self::FAILED;
                Console::output('A total of <bold>' . self::$failedTasks . '</bold> deployment tasks failed: <red>ABORTING</red>', 1, 2);
            }

            // Run Post-Deployment Tasks
            $this->runNonDeploymentTasks(AbstractTask::STAGE_POST_DEPLOY, $this->getConfig(), 'Post-Deployment');
        }

        // Time Information Hosts
        if ($this->hostsCount > 0) {
            $timeTextHost = $this->transcurredTime($this->endTimeHosts - $this->startTimeHosts);
            Console::output('Time for deployment: <bold>' . $timeTextHost . '</bold>.');

            $timeTextPerHost = $this->transcurredTime(round(($this->endTimeHosts - $this->startTimeHosts) / $this->hostsCount));
            Console::output('Average time per host: <bold>' . $timeTextPerHost . '</bold>.');
        }

        // Time Information General
        $timeText = $this->transcurredTime(time() - $this->startTime);
        Console::output('Total time: <bold>' . $timeText . '</bold>.', 1, 2);

        // Send Notifications
        $this->sendNotification(self::$failedTasks > 0 ? false : true);

        // Unlock
        if (file_exists(getcwd() . '/.mage/~working.lock')) {
            unlink(getcwd() . '/.mage/~working.lock');
        }

        if (self::$failedTasks === 0) {
            $exitCode = 0;
        }
        
        if (self::$deployStatus === self::FAILED) {
            $exitCode = 1;
        }
        
        return $exitCode;
    }

    /**
     * Execute Pre and Post Deployment Tasks
     *
     * @param string $stage
     * @param Config $config
     * @param string $title
     */
    protected function runNonDeploymentTasks($stage, Config $config, $title)
    {
        $tasksToRun = $config->getTasks($stage);
        self::$failedTasks = 0;

        // PreDeployment Hook
        if ($stage == AbstractTask::STAGE_PRE_DEPLOY) {
            // Look for Remote Source
            if (is_array($config->deployment('source', null))) {
                array_unshift($tasksToRun, 'scm/clone');
            }

            // Change Branch
            if ($config->deployment('scm', false)) {
                array_unshift($tasksToRun, 'scm/change-branch');
            }
        }

        // PostDeployment Hook
        if ($stage == AbstractTask::STAGE_POST_DEPLOY) {
            // If Deploy failed, clear post deploy tasks
            if (self::$deployStatus == self::FAILED) {
                $tasksToRun = array();
            }

            // Change Branch Back
            if ($config->deployment('scm', false)) {
                array_unshift($tasksToRun, 'scm/change-branch');
                $config->addParameter('_changeBranchRevert');
            }

            // Remove Remote Source
            if (is_array($config->deployment('source', null))) {
                array_push($tasksToRun, 'scm/remove-clone');
            }
        }

        if (count($tasksToRun) == 0) {
            Console::output('<bold>No </bold><light_cyan>' . $title . '</light_cyan> <bold>tasks defined.</bold>', 1, 3);
        } else {
            Console::output('Starting <bold>' . $title . '</bold> tasks:');

            $tasks = 0;
            $completedTasks = 0;

            foreach ($tasksToRun as $taskData) {
                $tasks++;
                $task = Factory::get($taskData, $config, false, $stage);

                if ($this->runTask($task)) {
                    $completedTasks++;
                } else {
                    self::$failedTasks++;
                }
            }

            if ($completedTasks == $tasks) {
                $tasksColor = 'green';
            } else {
                $tasksColor = 'red';
            }

            Console::output('Finished <bold>' . $title . '</bold> tasks: <' . $tasksColor . '>' . $completedTasks . '/' . $tasks . '</' . $tasksColor . '> tasks done.', 1, 3);
        }
    }

    protected function runDeploymentTasks()
    {
        if (self::$deployStatus == self::FAILED) {
            return;
        }

        // Run Tasks for Deployment
        $hosts = $this->getConfig()->getHosts();
        $this->hostsCount = count($hosts);
        self::$failedTasks = 0;

        if ($this->hostsCount == 0) {
            Console::output('<light_purple>Warning!</light_purple> <bold>No hosts defined, skipping deployment tasks.</bold>', 1, 3);
        } else {
            $this->startTimeHosts = time();
            foreach ($hosts as $hostKey => $host) {

                // Check if Host has specific configuration
                $hostConfig = null;
                if (is_array($host)) {
                    $hostConfig = $host;
                    $host = $hostKey;
                }

                // Set Host and Host Specific Config
                $this->getConfig()->setHost($host);
                $this->getConfig()->setHostConfig($hostConfig);

                // Prepare Tasks
                $tasks = 0;
                $completedTasks = 0;

                Console::output('Deploying to <bold>' . $this->getConfig()->getHost() . '</bold>');

                $tasksToRun = $this->getConfig()->getTasks();

                $deployStrategy = $this->chooseDeployStrategy();

                array_unshift($tasksToRun, $deployStrategy);

                if (count($tasksToRun) == 0) {
                    Console::output('<light_purple>Warning!</light_purple> <bold>No </bold><light_cyan>Deployment</light_cyan> <bold>tasks defined.</bold>', 2);
                    Console::output('Deployment to <bold>' . $host . '</bold> skipped!', 1, 3);
                } else {
                    foreach ($tasksToRun as $taskData) {
                        $tasks++;
                        $task = Factory::get($taskData, $this->getConfig(), false, AbstractTask::STAGE_DEPLOY);

                        if ($this->runTask($task)) {
                            $completedTasks++;
                        } else {
                            self::$failedTasks++;
                        }
                    }

                    if ($completedTasks == $tasks) {
                        $tasksColor = 'green';
                    } else {
                        $tasksColor = 'red';
                    }

                    Console::output('Deployment to <bold>' . $this->getConfig()->getHost() . '</bold> completed: <' . $tasksColor . '>' . $completedTasks . '/' . $tasks . '</' . $tasksColor . '> tasks done.', 1, 3);
                }

                // Reset Host Config
                $this->getConfig()->setHostConfig(null);
            }
            $this->endTimeHosts = time();

            if (self::$failedTasks > 0) {
                self::$deployStatus = self::FAILED;
            } else {
                self::$deployStatus = self::SUCCEDED;
            }

            // Releasing
            if (self::$deployStatus == self::SUCCEDED && $this->getConfig()->release('enabled', false) === true) {
                // Execute the Releases
                Console::output('Starting the <bold>Releasing</bold>');
                $completedTasks = 0;
                foreach ($hosts as $hostKey => $host) {

                    // Check if Host has specific configuration
                    $hostConfig = null;
                    if (is_array($host)) {
                        $hostConfig = $host;
                        $host = $hostKey;
                    }

                    // Set Host
                    $this->getConfig()->setHost($host);
                    $this->getConfig()->setHostConfig($hostConfig);

                    $task = Factory::get($this->chooseReleaseStrategy(), $this->getConfig(), false, AbstractTask::STAGE_DEPLOY);

                    if ($this->runTask($task, 'Releasing on host <purple>' . $host . '</purple> ... ')) {
                        $completedTasks++;
                    }

                    // Reset Host Config
                    $this->getConfig()->setHostConfig(null);
                }
                Console::output('Finished the <bold>Releasing</bold>', 1, 3);

                // Execute the Post-Release Tasks
                foreach ($hosts as $hostKey => $host) {

                    // Check if Host has specific configuration
                    $hostConfig = null;
                    if (is_array($host)) {
                        $hostConfig = $host;
                        $host = $hostKey;
                    }

                    // Set Host
                    $this->getConfig()->setHost($host);
                    $this->getConfig()->setHostConfig($hostConfig);

                    $tasksToRun = $this->getConfig()->getTasks(AbstractTask::STAGE_POST_RELEASE);
                    $tasks = count($tasksToRun);
                    $completedTasks = 0;

                    if (count($tasksToRun) > 0) {
                        Console::output('Starting <bold>Post-Release</bold> tasks for <bold>' . $host . '</bold>:');

                        foreach ($tasksToRun as $task) {
                            $task = Factory::get($task, $this->getConfig(), false, AbstractTask::STAGE_POST_RELEASE);

                            if ($this->runTask($task)) {
                                $completedTasks++;
                            }
                        }

                        if ($completedTasks == $tasks) {
                            $tasksColor = 'green';
                        } else {
                            $tasksColor = 'red';
                        }
                        Console::output('Finished <bold>Post-Release</bold> tasks for <bold>' . $host . '</bold>: <' . $tasksColor . '>' . $completedTasks . '/' . $tasks . '</' . $tasksColor . '> tasks done.', 1, 3);
                    }

                    // Reset Host Config
                    $this->getConfig()->setHostConfig(null);
                }
            }
        }
    }

    protected function runRollbackTask(AbstractTask $task)
    {
        $this->getConfig()->reload();
        $hosts = $this->getConfig()->getHosts();

        Console::output("", 1, 2);
        Console::output("Starting the <bold>rollback</bold>", 1, 1);

        if (!in_array($task->getStage(), $this->acceptedStagesToRollback)) {
            $stagesString = implode(', ', $this->acceptedStagesToRollback);
            Console::output("<light_purple>Warning!</light_purple> <bold>Rollback during deployment can be called only at the stages: $stagesString <bold>", 1);
            Console::output("<bold>Rollback:<bold> <red>ABORTING</red>", 1, 3);
        } elseif (count($hosts) == 0) {
            Console::output('<light_purple>Warning!</light_purple> <bold>No hosts defined, unable to get releases.</bold>', 1, 3);
        } else {
            $result = true;
            foreach ($hosts as $hostKey => $host) {
                $hostConfig = null;
                if (is_array($host)) {
                    $hostConfig = $host;
                    $host = $hostKey;
                }

                // Set Host and Host Specific Config
                $this->getConfig()->setHost($host);
                $this->getConfig()->setHostConfig($hostConfig);
                $this->getConfig()->setReleaseId(-1);

                $task = Factory::get(array(
                        'name'=>'releases/rollback',
                        'parameters' => array('inDeploy'=>true)
                    ),
                    $this->getConfig(),
                    false,
                    $task->getStage()
                );
                $task->init();
                $result = $task->run() && $result;
            }
            return $result;
        }
        return false;
    }

    /**
     * Runs a Task
     *
     * @param AbstractTask $task
     * @param string $title
     * @return boolean
     */
    protected function runTask(AbstractTask $task, $title = null)
    {
        $task->init();

        if ($title === null) {
            $title = 'Running <purple>' . $task->getName() . '</purple> ... ';
        }
        Console::output($title, 2, 0);

        $runTask = true;
        if (($task instanceof SkipOnOverride) && $this->getConfig()->getParameter('overrideRelease', false)) {
            $runTask = false;
        }

        if ($runTask === true) {
            try {
                $result = $task->run();

                if ($result === true) {
                    Console::output('<green>OK</green>', 0);
                    $result = true;
                } else {
                    Console::output('<red>FAIL</red>', 0);
                    $result = false;
                }
            } catch (RollbackException $e) {
                Console::output('<red>FAIL, Rollback catched</red> [Message: ' . $e->getMessage() . ']', 0);
                $this->runRollbackTask($task);
                $result = false;
            } catch (ErrorWithMessageException $e) {
                Console::output('<red>FAIL</red> [Message: ' . $e->getMessage() . ']', 0);
                $result = false;
            } catch (SkipException $e) {
                Console::output('<yellow>SKIPPED</yellow>', 0);
                $result = true;
            } catch (Exception $e) {
                Console::output('<red>FAIL</red>', 0);
                $result = false;
            }
        } else {
            Console::output('<yellow>SKIPPED</yellow>', 0);
            $result = true;
        }

        return $result;
    }

    /**
     * Humanize Transcurred time
     *
     * @param integer $time
     * @return string
     */
    protected function transcurredTime($time)
    {
        $hours = floor($time / 3600);
        $minutes = floor(($time - ($hours * 3600)) / 60);
        $seconds = $time - ($minutes * 60) - ($hours * 3600);
        $timeText = array();

        if ($hours > 0) {
            $timeText[] = $hours . ' hours';
        }

        if ($minutes > 0) {
            $timeText[] = $minutes . ' minutes';
        }

        if ($seconds >= 0) {
            $timeText[] = $seconds . ' seconds';
        }

        return implode(' ', $timeText);
    }

    /**
     * Send Email Notification if enabled
     * @param boolean $result
     * @return boolean
     */
    protected function sendNotification($result)
    {
        $projectName = $this->getConfig()->general('name', false);
        $projectEmail = $this->getConfig()->general('email', false);
        $notificationsEnabled = $this->getConfig()->general('notifications', false);

        // We need notifications enabled, and a project name and email to send the notification
        if (!$projectName || !$projectEmail || !$notificationsEnabled) {
            return false;
        }

        $mailer = new Mailer;
        $mailer->setAddress($projectEmail)
            ->setProject($projectName)
            ->setLogFile(Console::getLogFile())
            ->setEnvironment($this->getConfig()->getEnvironment())
            ->send($result);

        return true;
    }

    /**
     * @return string
     */
    protected function chooseDeployStrategy()
    {
        // Guess a Deploy Strategy
        switch ($this->getConfig()->deployment('strategy', self::DEFAULT_DEPLOY_STRATEGY)) {
            case self::DEPLOY_STRATEGY_DISABLED:
                $deployStrategy = 'deployment/strategy/disabled';
                break;

            case self::DEPLOY_STRATEGY_RSYNC:
                $deployStrategy = 'deployment/strategy/rsync';
                break;

            case self::DEPLOY_STRATEGY_TARGZ:
                $deployStrategy = 'deployment/strategy/tar-gz';
                break;

            case self::DEPLOY_STRATEGY_GIT_REBASE:
                $deployStrategy = 'deployment/strategy/git-rebase';
                break;

            case self::DEPLOY_STRATEGY_GIT_REMOTE_CACHE:
                $deployStrategy = 'deployment/strategy/git-remote-cache';
                break;

            case self::DEPLOY_STRATEGY_GUESS:
            default:
                if ($this->getConfig()->release('enabled', false) === true) {
                    $deployStrategy = 'deployment/strategy/tar-gz';
                } else {
                    $deployStrategy = 'deployment/strategy/rsync';
                }
                break;
        }
        return $deployStrategy;
    }

    /**
     * @return string
     */
    protected function chooseReleaseStrategy()
    {
        if ($this->getConfig()->release('enabled', self::DEFAULT_RELEASE_IS_ENABLED)
            && $this->getConfig()->deployment('strategy', self::DEFAULT_DEPLOY_STRATEGY) !== self::DEPLOY_STRATEGY_DISABLED
        ) {
            $strategy = 'deployment/release';
        } else {
            $strategy = 'deployment/strategy/disabled';
        }

        return $strategy;
    }
}
