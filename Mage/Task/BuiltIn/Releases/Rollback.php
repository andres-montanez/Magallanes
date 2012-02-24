<?php
class Mage_Task_BuiltIn_Releases_Rollback
    extends Mage_Task_TaskAbstract
    implements Mage_Task_Releases_BuiltIn
{
    private $_release = null;
    
    public function getName()
    {
        return 'Rollback release [built-in]';
    }

    public function setRelease($releaseId)
    {
        $this->_release = $releaseId;
        return $this;
    }
    
    public function getRelease()
    {
        return $this->_release;
    }
    
    public function run()
    {
        if ($this->_config->release('enabled', false) == true) {
            $releasesDirectory = $this->_config->release('directory', 'releases');
            $symlink = $this->_config->release('symlink', 'current');
            
            $output = '';
            $result = $this->_runRemoteCommand('ls -1 ' . $releasesDirectory, $output);
            $releases = ($output == '') ? array() : explode(PHP_EOL, $output);
                        
            if (count($releases) == 0) {
                Mage_Console::output('Release are not available for <dark_gray>' . $this->_config->getHost() . '</dark_gray> ... <red>FAIL</red>');

            } else {
                rsort($releases);
                
                $releaseIsAvailable = false;
                if ($this->getRelease() == '') {
                    $releaseId = $releases[0];

                } else if ($this->getRelease() <= 0) {
                    $index = $this->getRelease() * -1;
                    if (isset($releases[$index])) {
                        $releaseId = $releases[$index];
                        $releaseIsAvailable = true;
                    }
                } else {
                    if (in_array($this->getRelease(), $releases)) {
                        $releaseId = $this->getRelease();
                        $releaseIsAvailable = true;
                    }
                }
                
                if (!$releaseIsAvailable) {
                    Mage_Console::output('Release <dark_gray>' . $this->getRelease() . '</dark_gray> is invalid or unavailable for <dark_gray>' . $this->_config->getHost() . '</dark_gray> ... <red>FAIL</red>');                    

                } else {
                    Mage_Console::output('Rollback release on <dark_gray>' . $this->_config->getHost() . '</dark_gray>');
                    $rollbackTo = $releasesDirectory . '/' . $releaseId;
                    
                    // Tasks
                    $tasks = 1;
                    $completedTasks = 0;
                    $tasksToRun = $this->_config->getTasks();
                    $this->_config->setReleaseId($releaseId);
                    
                    if (count($tasksToRun) == 0) {
                        Mage_Console::output('<light_purple>Warning!</light_purple> <dark_gray>No </dark_gray><light_cyan>Deployment</light_cyan> <dark_gray>tasks defined.</dark_gray>', 2);
                        Mage_Console::output('Deployment to <dark_gray>' . $this->_config->getHost() . '</dark_gray> skipped!', 1, 3);
                    
                    } else {
                        foreach ($tasksToRun as $taskName) {
                            $task = Mage_Task_Factory::get($taskName, $this->_config, true, 'deploy');
                            $task->init();
                            Mage_Console::output('Running <purple>' . $task->getName() . '</purple> ... ', 2, false);
                            
                            if ($task instanceOf Mage_Task_Releases_RollbackAware) {
                                $tasks++;
                                $result = $task->run();
                                
                                if ($result == true) {
                                    Mage_Console::output('<green>OK</green>', 0);
                                    $completedTasks++;
                                } else {
                                    Mage_Console::output('<red>FAIL</red>', 0);
                                }
                            } else {
                                Mage_Console::output('<yellow>SKIPPED</yellow>', 0);                                
                            }
                        }
                    }
                    
                    // Changing Release
                    Mage_Console::output('Running <purple>Rollback Release [id=' . $releaseId . ']</purple> ... ', 2, false);
                    
                    $userGroup = '';
                    $resultFetch = $this->_runRemoteCommand('ls -ld ' . $rollbackTo . ' | awk \'{print \$3\":\"\$4}\'', $userGroup);
                    $command = 'rm -f ' . $symlink
                             . ' && '
                             . 'ln -sf ' . $rollbackTo . ' ' . $symlink
                             . ' && '
                             . 'chown -h ' . $userGroup . ' ' . $symlink;
                    $result = $this->_runRemoteCommand($command);

                    if ($result) {
                        Mage_Console::output('<green>OK</green>', 0);
                        $completedTasks++;
                    } else {
                        Mage_Console::output('<red>FAIL</red>', 0);
                    }
                    
                    if ($completedTasks == $tasks) {
                        $tasksColor = 'green';
                    } else {
                        $tasksColor = 'red';
                    }
                    
                    Mage_Console::output('Release rollback on <dark_gray>' . $this->_config->getHost() . '</dark_gray> compted: <' . $tasksColor . '>' . $completedTasks . '/' . $tasks . '</' . $tasksColor . '> tasks done.', 1, 3);
                }
            }

            return $result;
        } else {
            return false;
        }
    }

}