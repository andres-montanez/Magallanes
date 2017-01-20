<?php

namespace Mage\Task\BuiltIn\Database\Phinx;

/**
 * Task for running available phinx migrations. You can use environment,
 * configuration, parser & target options as described in the phinx
 * documentation.
 *
 * Usage :
 *   on-deploy:
 *     - database/phinx/migrate: {phinx_cmd: /path/to/phinx, environment: development, configuration: phinx.php}
 *
 * You may also set the target version to migrate to at runtime in the command
 * line by adding : phinx-target:20151002103807 and phinx_cmd in the general
 * configuration.
 *
 * @author Jérémy Huet <jeremy.huet@gmail.com>
 * @see http://docs.phinx.org/en/latest/commands.html
 */
class MigrateTask extends PhinxAbstractTask
{
    /**
     * Returns the Title of the Task
     *
     * @return string
     */
    public function getName()
    {
        return 'Phinx migrate [built-in]';
    }

    /**
     * Runs the task
     *
     * @return boolean
     */
    public function run()
    {
        if (! $this->inRollback()) {
            return $this->runCommand($this->getPhinxCmd() . ' migrate ' . $this->getOptionsForCmd());
        }

        return true;
    }
}
