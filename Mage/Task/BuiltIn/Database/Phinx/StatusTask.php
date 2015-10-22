<?php

namespace Mage\Task\BuiltIn\Database\Phinx;

/**
 * Task for running phinx status. It could be use to have the status in the
 * mage logs. You can use environment, configuration, parser & target options
 * as described in the phinx documentation.
 *
 * Usage :
 *   on-deploy:
 *     - database/phinx/status: {phinx_cmd: /path/to/phinx, environment: development, configuration: phinx.php}
 *
 * @author Jérémy Huet <jeremy.huet@gmail.com>
 * @see http://docs.phinx.org/en/latest/commands.html
 */
class StatusTask extends PhinxAbstractTask
{
    /**
     * Returns the Title of the Task
     *
     * @return string
     */
    public function getName()
    {
        return 'Phinx status [built-in]';
    }

    /**
     * Runs the task
     *
     * @return boolean
     */
    public function run()
    {
        return $this->runCommand($this->getPhinxCmd() . ' status ' . $this->getOptionsForCmd());
    }
}
