<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Halis Duraki <duraki.halis@nsoft.ba>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\System\Exec;

use Mage\Task\BuiltIn\System\AbstractSystemTask;
use Symfony\Component\Process\Process;
use Exception;

/**
 * System Task - Execute a system command
 *
 * @author Halis Duraki <duraki.halis@nsoft.ba>
 */
class ExecuteSystem extends AbstractSystemTask
{
    public function getName()
    {
        return 'system/exec';
    }

    public function getDescription()
    {
        try {
            return sprintf('[SYSTEM] Execute "%s" on system level', $this->getCommand('exec'));
        } catch (Exception $exception) {
            return '[SYSTEM] Exec [missing parameters]';
        }
    }

    public function execute()
    {
        $execCommand = $this->getCommand('exec');
        $execArgument = $this->getCommand('arg');

        $cmd = sprintf("$execCommand $execArgument");

        /** @var Process $process */
        $process = $this->runtime->runCommand($cmd);

        return $process->isSuccessful();
    }

    protected function getParameters()
    {
        return ['exec', 'arg'];
    }
}
