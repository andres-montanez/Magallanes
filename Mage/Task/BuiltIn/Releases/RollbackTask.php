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

use Exception;

/**
 * Task for Performing a Rollback Operation
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class RollbackTask extends AbstractTask implements IsReleaseAware
{
	/**
	 * The Relase ID to Rollback To
	 * @var integer
	 */
    protected $release = null;

    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        return 'Rollback release [built-in]';
    }

    /**
     * Sets the Release ID to Rollback To
     * @param integer $releaseId
     * @return \Mage\Task\BuiltIn\Releases\RollbackTask
     */
    public function setRelease($releaseId)
    {
        $this->release = $releaseId;
        return $this;
    }

    /**
     * Gets the Release ID to Rollback To
     * @return integer
     */
    public function getRelease()
    {
        return $this->release;
    }

    /**
     * Performs a Rollback Operation
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        if ($this->getConfig()->release('enabled', false) == true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');
            $symlink = $this->getConfig()->release('symlink', 'current');

            $output = '';
            $result = $this->runCommandRemote('ls -1 ' . $releasesDirectory, $output);
            $releases = ($output == '') ? array() : explode(PHP_EOL, $output);

            if (count($releases) == 0) {
                Console::output('Release are not available for <dark_gray>' . $this->getConfig()->getHost() . '</dark_gray> ... <red>FAIL</red>');

            } else {
                rsort($releases);

                $releaseIsAvailable = false;
                if ($this->getRelease() == '') {
                    $releaseId = $releases[0];
                    $releaseIsAvailable = true;

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
                    Console::output('Release <dark_gray>' . $this->getRelease() . '</dark_gray> is invalid or unavailable for <dark_gray>' . $this->getConfig()->getHost() . '</dark_gray> ... <red>FAIL</red>');

                } else {
                    Console::output('Rollback release on <dark_gray>' . $this->getConfig()->getHost() . '</dark_gray>');
                    $rollbackTo = $releasesDirectory . '/' . $releaseId;

                    // Tasks
                    $tasks = 1;
                    $completedTasks = 0;
                    $tasksToRun = $this->getConfig()->getTasks();
                    $this->getConfig()->setReleaseId($releaseId);

                    foreach ($tasksToRun as $taskData) {
                        $task = Factory::get($taskData, $this->getConfig(), true, 'deploy');
                        $task->init();
                        Console::output('Running <purple>' . $task->getName() . '</purple> ... ', 2, false);

                        if ($task instanceOf RollbackAware) {
                            $tasks++;
                            $result = $task->run();

                            if ($result == true) {
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
                             . 'ln -sf ' . $rollbackTo . ' ' . $symlink
                             . ' && '
                             . 'chown -h ' . $userGroup . ' ' . $symlink;
                    $result = $this->runCommandRemote($command);

                    if ($result) {
                        Console::output('<green>OK</green>', 0);
                        $completedTasks++;
                    } else {
                        Console::output('<red>FAIL</red>', 0);
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