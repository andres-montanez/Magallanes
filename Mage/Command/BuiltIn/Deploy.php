<?php
class Mage_Command_BuiltIn_Deploy
    extends Mage_Command_CommandAbstract
    implements Mage_Command_RequiresEnvironment
{
    private $_startTime = null;
    private $_startTimeHosts = null;
    private $_endTimeHosts = null;
    private $_hostsCount = 0;

    public function __construct()
    {
    }

    public function run()
    {
        $this->getConfig()->setReleaseId(date('YmdHis'));
        $failedTasks = 0;

        $this->_startTime = time();

        $lockFile = '.mage/' . $this->getConfig()->getEnvironment() . '.lock';
        if (file_exists($lockFile)) {
            Mage_Console::output('<red>This environment is locked!</red>', 1, 2);
            return;
        }

        // Run Pre-Deployment Tasks
        $this->_runNonDeploymentTasks('pre-deploy', $this->getConfig(), 'Pre-Deployment');

        // Run Tasks for Deployment
        $hosts = $this->getConfig()->getHosts();
        $this->_hostsCount = count($hosts);

        if ($this->_hostsCount == 0) {
            Mage_Console::output('<light_purple>Warning!</light_purple> <dark_gray>No hosts defined, skipping deployment tasks.</dark_gray>', 1, 3);

        } else {
            $this->_startTimeHosts = time();
            foreach ($hosts as $host) {
                $this->getConfig()->setHost($host);
                $tasks = 0;
                $completedTasks = 0;

                Mage_Console::output('Deploying to <dark_gray>' . $this->getConfig()->getHost() . '</dark_gray>');

                $tasksToRun = $this->getConfig()->getTasks();
                array_unshift($tasksToRun, 'deployment/rsync');

                if (count($tasksToRun) == 0) {
                    Mage_Console::output('<light_purple>Warning!</light_purple> <dark_gray>No </dark_gray><light_cyan>Deployment</light_cyan> <dark_gray>tasks defined.</dark_gray>', 2);
                    Mage_Console::output('Deployment to <dark_gray>' . $host . '</dark_gray> skipped!', 1, 3);

                } else {
                    foreach ($tasksToRun as $taskData) {
                        $tasks++;
                        $task = Mage_Task_Factory::get($taskData, $this->getConfig(), false, 'deploy');

                        if ($this->_runTask($task)) {
                            $completedTasks++;
                        } else {
                            $failedTasks++;
                        }
                    }

                    if ($completedTasks == $tasks) {
                        $tasksColor = 'green';
                    } else {
                        $tasksColor = 'red';
                    }

                    Mage_Console::output('Deployment to <dark_gray>' . $this->getConfig()->getHost() . '</dark_gray> completed: <' . $tasksColor . '>' . $completedTasks . '/' . $tasks . '</' . $tasksColor . '> tasks done.', 1, 3);
                }
            }
            $this->_endTimeHosts = time();

            if ($failedTasks > 0) {
                Mage_Console::output('A total of <dark_gray>' . $failedTasks . '</dark_gray> deployment tasks failed: <red>ABORTING</red>', 1, 2);
                return;
            }

            // Releasing
            if ($this->getConfig()->release('enabled', false) == true) {
                // Execute the Releases
                Mage_Console::output('Starting the <dark_gray>Releaseing</dark_gray>');
                foreach ($hosts as $host) {
                    $this->getConfig()->setHost($host);
                    $task = Mage_Task_Factory::get('deployment/release', $this->getConfig(), false, 'deploy');

                    if ($this->_runTask($task, 'Releasing on host <purple>' . $host . '</purple> ... ')) {
                        $completedTasks++;
                    }
                }
                Mage_Console::output('Finished the <dark_gray>Releaseing</dark_gray>', 1, 3);

                // Execute the Post-Release Tasks
                foreach ($hosts as $host) {
                    $this->getConfig()->setHost($host);
                    $tasksToRun = $this->getConfig()->getTasks('post-release');
                    $tasks = count($tasksToRun);
                    $completedTasks = 0;

                    if (count($tasksToRun) > 0) {
                        Mage_Console::output('Starting <dark_gray>Post-Release</dark_gray> tasks for <dark_gray>' . $host . '</dark_gray>:');

                        foreach ($tasksToRun as $task) {
                            $task = Mage_Task_Factory::get($task, $this->getConfig(), false, 'post-release');

                            if ($this->_runTask($task)) {
                                $completedTasks++;
                            }
                        }

                        if ($completedTasks == $tasks) {
                            $tasksColor = 'green';
                        } else {
                            $tasksColor = 'red';
                        }
                        Mage_Console::output('Finished <dark_gray>Post-Release</dark_gray> tasks for <dark_gray>' . $host . '</dark_gray>: <' . $tasksColor . '>' . $completedTasks . '/' . $tasks . '</' . $tasksColor . '> tasks done.', 1, 3);
                    }
                }
            }
        }

        // Run Post-Deployment Tasks
        $this->_runNonDeploymentTasks('post-deploy', $this->getConfig(), 'Post-Deployment');

        // Time Information Hosts
        if ($this->_hostsCount > 0) {
            $timeTextHost = $this->_transcurredTime($this->_endTimeHosts - $this->_startTimeHosts);
            Mage_Console::output('Time for deployment: <dark_gray>' . $timeTextHost . '</dark_gray>.');

            $timeTextPerHost = $this->_transcurredTime(round(($this->_endTimeHosts - $this->_startTimeHosts) / $this->_hostsCount));
            Mage_Console::output('Average time per host: <dark_gray>' . $timeTextPerHost . '</dark_gray>.');
        }

        // Time Information General
        $timeText = $this->_transcurredTime(time() - $this->_startTime);
        Mage_Console::output('Total time: <dark_gray>' . $timeText . '</dark_gray>.', 1, 2);
    }

    /**
     * Execute Pre and Post Deployment Tasks
     *
     * @param string $stage
     * @param Mage_Config $config
     * @param string $title
     */
    private function _runNonDeploymentTasks($stage, Mage_Config $config, $title)
    {
        $tasksToRun = $config->getTasks($stage);

        // PreDeployment Hook
        if ($stage == 'pre-deploy') {
        	// Look for Remote Source
        	if (is_array($this->_config->deployment('source', null))) {
        		array_unshift($tasksToRun, 'scm/clone');
        	}

        	// Change Branch
        	if ($this->getConfig()->deployment('scm', false)) {
        		array_unshift($tasksToRun, 'scm/change-branch');
        	}
        }

        // PostDeployment Hook
        if ($stage == 'post-deploy') {
        	// Change Branch Back
        	if ($this->getConfig()->deployment('scm', false)) {
        		array_unshift($tasksToRun, 'scm/change-branch-back');
        	}

        	// Remove Remote Source
        	if (is_array($this->_config->deployment('source', null))) {
        		 array_push($tasksToRun, 'scm/remove-clone');
            }
        }

        if (count($tasksToRun) == 0) {
            Mage_Console::output('<dark_gray>No </dark_gray><light_cyan>' . $title . '</light_cyan> <dark_gray>tasks defined.</dark_gray>', 1, 3);

        } else {
            Mage_Console::output('Starting <dark_gray>' . $title . '</dark_gray> tasks:');

            $tasks = 0;
            $completedTasks = 0;

            foreach ($tasksToRun as $taskData) {
                $tasks++;
                $task = Mage_Task_Factory::get($taskData, $config, false, $stage);

                if ($this->_runTask($task)) {
                    $completedTasks++;
                }
            }

            if ($completedTasks == $tasks) {
                $tasksColor = 'green';
            } else {
                $tasksColor = 'red';
            }

            Mage_Console::output('Finished <dark_gray>' . $title . '</dark_gray> tasks: <' . $tasksColor . '>' . $completedTasks . '/' . $tasks . '</' . $tasksColor . '> tasks done.', 1, 3);
        }
    }

    private function _runTask($task, $title = null)
    {
        $task->init();

        if ($title == null) {
            $title = 'Running <purple>' . $task->getName() . '</purple> ... ';
        }
        Mage_Console::output($title, 2, 0);

        $runTask = true;
        if (($task instanceOf Mage_Task_Releases_SkipOnOverride) && $this->getConfig()->getParameter('overrideRelease', false)) {
            $runTask == false;
        }

        $result = false;
        if ($runTask == true) {
            try {
                $result = $task->run();

                if ($result == true) {
                    Mage_Console::output('<green>OK</green>', 0);
                    $result = true;

                } else {
                    Mage_Console::output('<red>FAIL</red>', 0);
                    $result = false;
                }
            } catch (Mage_Task_SkipException $e) {
                Mage_Console::output('<yellow>SKIPPED</yellow>', 0);
                $result = true;

            } catch (Exception $e) {
                Mage_Console::output('<red>FAIL</red>', 0);
                $result = false;
            }
        } else {
            Mage_Console::output('<yellow>SKIPPED</yellow>', 0);
            $result = true;
        }

        return $result;
    }

    /**
     * Humanize Transcurred time
     * @param integer $time
     * @return string
     */
    private function _transcurredTime($time)
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

        if ($seconds > 0) {
            $timeText[] = $seconds . ' seconds';
        }

        return implode(' ', $timeText);
    }
}
