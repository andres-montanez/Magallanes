<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Scm;

use Mage\Task\AbstractTask;
use Mage\Task\SkipException;

/**
 * Task for Force Updating a Working Copy
 *
 * 'git fetch' downloads the latest from remote without trying to merge or rebase anything.
 * 'git reset' resets the master branch to what you just fetched.
 * The '--hard' option changes all the files in your working tree to match the files in origin/master,
 * so if you have any local changes, they will be lost.
 *
 * @author Samuel Chiriluta <samuel4x4@gmail.com>
 */
class ForceUpdateTask extends AbstractTask
{
    /**
     * Name of the Task
     * @var string
     */
    private $name = 'SCM Force Update [built-in]';

    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::init()
     */
    public function init()
    {
        switch ($this->getConfig()->general('scm')) {
            case 'git':
                $this->name = 'SCM Force Update (GIT) [built-in]';
                break;
        }
    }

    /**
     * Force Updates the Working Copy
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        switch ($this->getConfig()->general('scm')) {
            case 'git':
                $branch = $this->getParameter('branch', 'master');
                $remote = $this->getParameter('remote', 'origin');
                
                $command = 'git fetch ' . $remote . ' ' . $branch;
                $result = $this->runCommand($command);

                $command = 'git reset --hard ' . $remote . '/' . $branch;
                $result = $result && $this->runCommand($command);

                $command = 'git pull ' . $remote . ' ' . $branch;
                $result = $result && $this->runCommand($command);
                break;

            default:
                throw new SkipException;
                break;
        }

        $this->getConfig()->reload();

        return $result;
    }
}
