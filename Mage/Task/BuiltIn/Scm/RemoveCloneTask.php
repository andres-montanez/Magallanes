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

/**
 * Task for Removing an used Cloned Repository
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class RemoveCloneTask extends AbstractTask
{
    /**
     * Name of the Task
     * @var string
     */
    private $name = 'SCM Remove Clone [built-in]';

    /**
     * Source of the Repo
     * @var string
     */
    private $source = null;

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
        $this->source = $this->getConfig()->deployment('source');
        switch ($this->source['type']) {
            case 'git':
                $this->name = 'SCM Remove Clone (GIT) [built-in]';
                break;
        }
    }

    public function run()
    {
        return $this->runCommandLocal('rm -rf ' . $this->source['temporal']);
    }
}
