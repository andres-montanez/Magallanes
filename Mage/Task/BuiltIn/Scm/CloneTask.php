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

        // Create temporal directory for clone
        if (is_array($this->source)) {
            if (trim($this->source['temporal']) == '') {
                $this->source['temporal'] = sys_get_temp_dir();
            }
            $this->source['temporal'] = rtrim($this->source['temporal'], '/') . '/' . md5(microtime()) . '/';
            $this->getConfig()->setSourceTemporal($this->source['temporal']);
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
                // Fast clone Repo form Branch
                $command = 'cd ' . $this->source['temporal'] . ' ; '
                    . 'git clone --depth 1 -q -b ' . $this->source['from']
                    . ' ' . $this->source['repository'] . ' . ';
                $result = $this->runCommandLocal($command);

                $this->getConfig()->setFrom($this->source['temporal']);
                break;

            default:
                throw new SkipException;
                break;
        }

        return $result;
    }
}
