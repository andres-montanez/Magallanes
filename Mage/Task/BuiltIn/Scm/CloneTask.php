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
 * Task for Clonning a Repository
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class CloneTask extends AbstractTask
{
    /**
     * Name of the Task
     * @var string
     */
    private $name = 'SCM Clone [built-in]';

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
                $this->name = 'SCM Clone (GIT) [built-in]';
                break;
        }
    }

    /**
     * Clones a Repository
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $this->runCommandLocal('mkdir -p ' . $this->source['temporal']);
        switch ($this->source['type']) {
            case 'git':
                // Clone Repo
                $command = 'cd ' . $this->source['temporal'] . ' ; '
                         . 'git clone ' . $this->source['repository'] . ' . ';
                $result = $this->runCommandLocal($command);

                // Checkout Branch
                $command = 'cd ' . $this->source['temporal'] . ' ; '
                         . 'git checkout ' . $this->source['from'];
                $result = $result && $this->runCommandLocal($command);

                $this->getConfig()->setFrom($this->source['temporal']);
                break;

            default:
                throw new SkipException;
                break;
        }

        return $result;
    }
}