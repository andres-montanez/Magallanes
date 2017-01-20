<?php

namespace Mage\Task\BuiltIn\Database\Phinx;

use Mage\Task\Releases\RollbackAware;

/**
 * Task for running available phinx rollbacks. You can use environment,
 * configuration, parser & target options as described in the phinx
 * documentation.
 *
 * Usage :
 *   on-deploy:
 *     - database/phinx/rollback: {phinx_cmd: /path/to/phinx, environment: development, configuration: phinx.php, target: 20151002103807}
 *
 * You may also set the target version to rollback to at runtime in the command
 * line by adding : phinx-target:20151002103807 and phinx_cmd in the general
 * configuration.
 *
 * @author Jérémy Huet <jeremy.huet@gmail.com>
 * @see http://docs.phinx.org/en/latest/commands.html
 */
class RollbackTask extends PhinxAbstractTask implements RollbackAware
{
    /**
     * Returns the Title of the Task
     *
     * @return string
     */
    public function getName()
    {
        return 'Phinx rollback [built-in]';
    }

    /**
     * Runs the task only on rollback
     *
     * @return boolean
     */
    public function run()
    {
        if ($this->inRollback()) {
            return $this->runCommand($this->getPhinxCmd() . ' rollback ' . $this->getOptionsForCmd());
        }

        return true;
    }
}
