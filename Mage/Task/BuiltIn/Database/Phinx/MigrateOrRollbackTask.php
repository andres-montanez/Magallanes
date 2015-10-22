<?php

namespace Mage\Task\BuiltIn\Database\Phinx;

use Mage\Task\Releases\RollbackAware;

/**
 * Task for running available phinx migrations or rollbacking them if in a
 * mage rollback context. You can use environment, configuration, parser &
 * target options as described in the phinx documentation.
 *
 * Usage :
 *   on-deploy:
 *     - database/phinx/migrate-or-rollback: {phinx_cmd: /path/to/phinx, environment: development, configuration: phinx.php}
 *
 * You may also set the target version to migrate or rollback to at runtime in
 * the command line by adding : phinx-target:20151002103807 and phinx_cmd in the
 * general configuration.
 *
 * @author Jérémy Huet <jeremy.huet@gmail.com>
 * @see http://docs.phinx.org/en/latest/commands.html
 */
class MigrateOrRollbackTask extends PhinxAbstractTask implements RollbackAware
{
    /**
     * Returns the Title of the Task
     *
     * @return string
     */
    public function getName()
    {
        return 'Phinx migrate or rollback [built-in]';
    }

    /**
     * Runs the task
     *
     * @return boolean
     */
    public function run()
    {
        $migrateOrRollback = $this->inRollback() ? 'rollback' : 'migrate';

        return $this->runCommand($this->getPhinxCmd() . ' ' .$migrateOrRollback . ' ' . $this->getOptionsForCmd());
    }
}
