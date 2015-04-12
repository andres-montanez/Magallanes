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
 * Task for Updating a Working Copy
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class UpdateTask extends AbstractTask
{
    /**
     * Name of the Task
     * @var string
     */
    private $name = 'SCM Update [built-in]';

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
                $this->name = 'SCM Update (GIT) [built-in]';
                break;
        }
    }

    /**
     * Updates the Working Copy
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $command = 'cd ' . $this->getConfig()->deployment('from', './') . '; ';
        switch ($this->getConfig()->general('scm')) {
            case 'git':
                $command .= 'git pull';
                break;

            default:
                throw new SkipException;
                break;
        }

        $result = $this->runCommandLocal($command);
        $this->getConfig()->reload();

        return $result;
    }
}
