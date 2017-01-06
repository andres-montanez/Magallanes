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

/**
 * File System Task - Symlink a File
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class LinkTask extends AbstractFileTask
{
    public function getName()
    {
        return 'fs/link';
    }

    public function getDescription()
    {
        return sprintf('[FS] Link "%s" to "%s"', $this->getFile('from'), $this->getFile('to'));
    }

    public function execute()
    {
        $from = $this->getFile('from');
        $to = $this->getFile('to');

        $cmd = sprintf('ln -snf %s %s', $from, $to);

        /** @var Process $process */
        $process = $this->runtime->runCommand($cmd);

        return $process->isSuccessful();
    }

    protected function getParameters()
    {
        return ['from', 'to'];
    }
}
