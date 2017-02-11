<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\FS;

use Symfony\Component\Process\Process;
use Exception;

/**
 * File System Task - Copy a File
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class CopyTask extends AbstractFileTask
{
    public function getName()
    {
        return 'fs/copy';
    }

    public function getDescription()
    {
        try {
            return sprintf('[FS] Copy "%s" to "%s"', $this->getFile('from'), $this->getFile('to'));
        } catch (Exception $exception) {
            return '[FS] Copy [missing parameters]';
        }
    }

    public function execute()
    {
        $copyFrom = $this->getFile('from');
        $copyTo = $this->getFile('to');
        $flags = $this->options['flags'];

        $cmd = sprintf('cp %s "%s" "%s"', $flags, $copyFrom, $copyTo);

        /** @var Process $process */
        $process = $this->runtime->runCommand($cmd);

        return $process->isSuccessful();
    }

    protected function getParameters()
    {
        return ['from', 'to', 'flags'];
    }

    public function getDefaults()
    {
        return ['flags' => '-p'];
    }
}
