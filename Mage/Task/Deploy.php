<?php
class Mage_Task_Deploy
{
    private $_config = null;
    private $_releaseId = null;
    private $_startTime = null;
    private $_startTimeHosts = null;
    private $_endTimeHosts = null;
    private $_hostsCount = 0;
    
    public function __construct()
    {
        $this->_releaseId = date('YmdHis');
    }
    
    public function run(Mage_Config $config)
    {
        $this->_startTime = time();
        $this->_config = $config;

        // Run Pre-Deployment Tasks
        $this->_runNonDeploymentTasks('pre-deploy', $config, 'Pre-Deployment');
        
        // Run Tasks for Deployment
        $hosts = $config->getHosts();
        
        if (count($hosts) == 0) {
            Mage_Console::output('<light_purple>Warning!</light_purple> <dark_gray>No hosts defined, skipping deployment tasks.</dark_gray>', 1, 3);
            
        } else {
            $this->_startTimeHosts = time();
            foreach ($hosts as $host) {
                $this->_hostsCount++;
                $config->setHost($host);
                $tasks = 0;
                $completedTasks = 0;
    
                Mage_Console::output('Deploying to <dark_gray>' . $config->getHost() . '</dark_gray>');
                
                $tasksToRun = $config->getTasks();
                array_unshift($tasksToRun, 'deployment/rsync');

                if ($config->release('enabled', false) == true) {
                    $config->setReleaseId($this->_releaseId);
                    array_push($tasksToRun, 'deployment/releases');                    
                }

                if (count($tasksToRun) == 0) {
                    Mage_Console::output('<light_purple>Warning!</light_purple> <dark_gray>No </dark_gray><light_cyan>Deployment</light_cyan> <dark_gray>tasks defined.</dark_gray>', 2);
                    Mage_Console::output('Deployment to <dark_gray>' . $config->getHost() . '</dark_gray> skipped!', 1, 3);

                } else {
                    foreach ($tasksToRun as $taskName) {
                        $tasks++;
                        $task = Mage_Task_Factory::get($taskName, $config, false, 'deploy');
                        $task->init();
                        
                        Mage_Console::output('Running <purple>' . $task->getName() . '</purple> ... ', 2, false);
                        $result = $task->run();
        
                        if ($result == true) {
                            Mage_Console::output('<green>OK</green>', 0);
                            $completedTasks++;
                        } else {
                            Mage_Console::output('<red>FAIL</red>', 0);
                        }
                    }
                    
                    if ($completedTasks == $tasks) {
                        $tasksColor = 'green';                
                    } else {
                        $tasksColor = 'red';                
                    }
        
                    Mage_Console::output('Deployment to <dark_gray>' . $config->getHost() . '</dark_gray> compted: <' . $tasksColor . '>' . $completedTasks . '/' . $tasks . '</' . $tasksColor . '> tasks done.', 1, 3);
                }
            }
            $this->_endTimeHosts = time();
        }

        // Run Post-Deployment Tasks
        $this->_runNonDeploymentTasks('post-deploy', $config, 'Post-Deployment');
        
        // Time Information General
        $timeText = $this->_transcurredTime(time() - $this->_startTime);
        Mage_Console::output('Total time: <dark_gray>' . $timeText . '</dark_gray>.');

        // Time Information Hosts
        if ($this->_hostsCount > 0) {
            $timeTextPerHost = $this->_transcurredTime(round(($this->_endTimeHosts - $this->_startTimeHosts) / $this->_hostsCount));
            Mage_Console::output('Average time per host: <dark_gray>' . $timeTextPerHost . '</dark_gray>.');            
        }
    }

    private function _runNonDeploymentTasks($stage, Mage_Config $config, $title)
    {
        $tasksToRun = $config->getTasks($stage);
        
        // Look for Remote Source
        if ($this->_config->deployment('from', false) == false) {
            if (is_array($this->_config->deployment('source', null))) {
                if ($stage == 'pre-deploy') {
                    array_unshift($tasksToRun, 'scm/clone');                    
                } elseif ($stage == 'post-deploy') {
                    array_unshift($tasksToRun, 'scm/remove-clone');
                }
            }
        }

        if (count($tasksToRun) == 0) {
            Mage_Console::output('<dark_gray>No </dark_gray><light_cyan>' . $title . '</light_cyan> <dark_gray>tasks defined.</dark_gray>', 1, 3);
            
        } else {
            Mage_Console::output('Starting <dark_gray>' . $title . '</dark_gray> tasks:');
    
            $tasks = 0;
            $completedTasks = 0;
    
            foreach ($tasksToRun as $taskName) {
                $tasks++;
                $task = Mage_Task_Factory::get($taskName, $config, false, $stage);
                $task->init();
                    
                Mage_Console::output('Running <purple>' . $task->getName() . '</purple> ... ', 2, 0);
                $result = $task->run();
    
                if ($result == true) {
                    Mage_Console::output('<green>OK</green>', 0);
                    $completedTasks++;
                } else {
                    Mage_Console::output('<red>FAIL</red>', 0);
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