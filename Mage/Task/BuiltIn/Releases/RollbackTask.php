<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Releases;

use Mage\Console;
use Mage\Task\Factory;
use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;
use Mage\Task\Releases\RollbackAware;

/**
 * Task for Performing a Rollback Operation
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class RollbackTask extends AbstractTask implements IsReleaseAware
{
    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        return 'Rollback release [built-in]';
    }

    /**
     * Gets the Release ID to Rollback To
     * @return integer
     */
    public function getReleaseId()
    {
        return $this->getConfig()->getReleaseId();
    }

    /**
     * Performs a Rollback Operation
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        if ($this->getConfig()->release('enabled', false) === true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');
            $symlink = $this->getConfig()->release('symlink', 'current');

            if (substr($symlink, 0, 1) == '/') {
                $releasesDirectory = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $releasesDirectory;
            }

            $output = '';
            $result = $this->runCommandRemote('ls -1 ' . $releasesDirectory, $output);
            $releases = ($output == '') ? array() : explode(PHP_EOL, $output);

            if (count($releases) == 0) {
                Console::output('Release are not available for <bold>' . $this->getConfig()->getHost() . '</bold> ... <red>FAIL</red>');
            } else {
                rsort($releases);
                $deleteCurrent = $this->getConfig()->getParameter('deleteCurrent',
                    $this->getConfig()->deployment('delete-on-rollback',
                        $this->getConfig()->general('delete-on-rollback', false)
                    )
                );

                $releaseIsAvailable = false;
                if ($this->getReleaseId() == '') {
                    $releaseId = $releases[0];
                    $releaseIsAvailable = true;
                } elseif ($this->getReleaseId() <= 0) {
                    $index = $this->getReleaseId() * -1;
                    if (isset($releases[$index])) {
                        $releaseId = $releases[$index];
                        $releaseIsAvailable = true;
                    }
                } else {
                    if (in_array($this->getReleaseId(), $releases)) {
                        $releaseId = $this->getReleaseId();
                        $releaseIsAvailable = true;
                    }
                }

                $currentCopy = rtrim($releasesDirectory, '/') . '/' . $releaseId;

                if (!$releaseIsAvailable) {
                    Console::output('Release <bold>' . $this->getReleaseId() . '</bold> is invalid or unavailable for <bold>' . $this->getConfig()->getHost() . '</bold> ... <red>FAIL</red>');
                } else {
                    Console::output('Rollback release on <bold>' . $this->getConfig()->getHost() . '</bold>');
                    $rollbackTo = $releasesDirectory . '/' . $releaseId;

                    // Get Current Release
                    if ($deleteCurrent) {
                        $result = $this->runCommandRemote('ls -l ' . $symlink, $output) && $result;
                        $currentRelease = explode('/', $output);
                        $currentRelease = trim(array_pop($currentRelease));
                    }

                    // Tasks
                    $tasks = 1;
                    $completedTasks = 0;
                    $tasksToRun = $this->getConfig()->getTasks();
                    $this->getConfig()->setReleaseId($releaseId);

                    // Run Deploy Tasks
                    foreach ($tasksToRun as $taskData) {
                        $task = Factory::get($taskData, $this->getConfig(), true, self::STAGE_DEPLOY);
                        $task->init();
                        Console::output('Running <purple>' . $task->getName() . '</purple> ... ', 2, false);

                        if ($task instanceof RollbackAware) {
                            /* @var $task AbstractTask */
                            $tasks++;
                            $result = $task->run();

                            if ($result === true) {
                                Console::output('<green>OK</green>', 0);
                                $completedTasks++;
                            } else {
                                Console::output('<red>FAIL</red>', 0);
                            }
                        } else {
                            Console::output('<yellow>SKIPPED</yellow>', 0);
                        }
                    }

                    // Changing Release
                    Console::output('Running <purple>Rollback Release [id=' . $releaseId . ']</purple> ... ', 2, false);

                    $userGroup = '';
                    $resultFetch = $this->runCommandRemote('ls -ld ' . $rollbackTo . ' | awk \'{print \$3":"\$4}\'', $userGroup);

                    $tmplink = $symlink . '.tmp';
                    $command = "ln -sfn {$currentCopy} {$tmplink}";
                    if ($resultFetch && $userGroup) {
                        $command .= " && chown -h {$userGroup} ${tmplink}";
                    }
                    $command .= " && mv -T {$tmplink} {$symlink}";

                    $result = $this->runCommandRemote($command);

                    if ($result) {
                        Console::output('<green>OK</green>', 0);
                        $completedTasks++;

                        // Delete Old Current Release
                        if ($deleteCurrent && $currentRelease) {
                            $this->runCommandRemote('rm -rf ' . $releasesDirectory . '/' . $currentRelease, $output);
                        }
                    } else {
                        Console::output('<red>FAIL</red>', 0);
                    }

                    // Run Post Release Tasks
                    $tasksToRun = $this->getConfig()->getTasks(AbstractTask::STAGE_POST_DEPLOY);
                    foreach ($tasksToRun as $taskData) {
                        $task = Factory::get($taskData, $this->getConfig(), true, self::STAGE_POST_DEPLOY);
                        $task->init();
                        Console::output('Running <purple>' . $task->getName() . '</purple> ... ', 2, false);

                        if ($task instanceof RollbackAware) {
                            /* @var $task AbstractTask */
                            $tasks++;
                            $result = $task->run();

                            if ($result === true) {
                                Console::output('<green>OK</green>', 0);
                                $completedTasks++;
                            } else {
                                Console::output('<red>FAIL</red>', 0);
                            }
                        } else {
                            Console::output('<yellow>SKIPPED</yellow>', 0);
                        }
                    }

                    if ($completedTasks == $tasks) {
                        $tasksColor = 'green';
                    } else {
                        $tasksColor = 'red';
                    }

                    Console::output('Release rollback on <bold>' . $this->getConfig()->getHost() . '</bold> compted: <' . $tasksColor . '>' . $completedTasks . '/' . $tasks . '</' . $tasksColor . '> tasks done.', 1, 3);
                }
            }

            return $result;
        } else {
            return false;
        }
    }
}
