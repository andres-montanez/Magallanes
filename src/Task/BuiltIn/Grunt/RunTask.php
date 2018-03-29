<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\Grunt;

use Symfony\Component\Process\Process;
use Mage\Task\AbstractTask;

/**
 * Grunt Task - Runs grunt
 *
 * @author Benjamin Gutmann <benjamin.gutmann@bestit-online.de>
 * @author Alexander Schneider <alexanderschneider85@gmail.com>
 */
class RunTask extends AbstractTask
{
    public function getName()
    {
        return 'grunt/run';
    }

    public function getDescription()
    {
        return '[Grunt] Run';
    }

    public function execute()
    {
        $flags = isset($this->options['flags']) ? $this->options['flags'] : '';
        $cmd = sprintf('grunt %s', $flags);

        /** @var Process $process */
        $process = $this->runtime->runCommand($cmd);

        return $process->isSuccessful();
    }
}
