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

            $output = '';
            $result = $this->runCommandRemote('ls -1 ' . $releasesDirectory, $output);
            $releases = ($output == '') ? array() : explode(PHP_EOL, $output);

            if (count($releases) == 0) {
                Console::output('Release are not available for <dark_gray>' . $this->getConfig()->getHost() . '</dark_gray> ... <red>FAIL</red>');

            } else {
                rsort($releases);
                $deleteCurrent = $this->getConfig()->getParameter('deleteCurrent', false);

                $releaseIsAvailable = false;
                if ($this->getReleaseId() == '') {
                    $releaseId = $releases[0];
                    $releaseIsAvailable = true;

                } else if ($this->getReleaseId() <= 0) {
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

                if (!$releaseIsAvailable) {
                    Console::output('Release <dark_gray>' . $this->getReleaseId() . '</dark_gray> is invalid or unavailable for <dark_gray>' . $this->getConfig()->getHost() . '</dark_gray> ... <red>FAIL</red>');

                } else {
                    Console::output('Rollback release on <dark_gray>' . $this->getConfig()->getHost() . '</dark_gray>');
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

                        if ($task instanceOf RollbackAware) {
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
                    $command = 'rm -f ' . $symlink
                             . ' && '
                             . 'ln -sf ' . $rollbackTo . ' ' . $symlink;

                    if ($resultFetch) {
                        $command .= ' && chown -h ' . $userGroup . ' ' . $symlink;
                    }

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

                        if ($task instanceOf RollbackAware) {
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

                    Console::output('Release rollback on <dark_gray>' . $this->getConfig()->getHost() . '</dark_gray> compted: <' . $tasksColor . '>' . $completedTasks . '/' . $tasks . '</' . $tasksColor . '> tasks done.', 1, 3);
                }
            }

            return $result;

        } else {
            return false;
        }
    }
}
