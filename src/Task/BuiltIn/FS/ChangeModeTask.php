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
 * @author Marian Bäuerle
 */
class ChangeModeTask extends AbstractFileTask
{
    public function getName()
    {
        return 'fs/chmod';
    }

    public function getDescription()
    {
        try {
            return sprintf('[FS] Change mode of "%s" to "%s"', $this->getFile('file'), $this->options['mode']);
        } catch (Exception $exception) {
            return '[FS] Chmod [missing parameters]';
        }
    }

    public function execute()
    {
        $file = $this->getFile('file');
        $mode = $this->options['mode'];
        $flags = $this->options['flags'];

        $cmd = sprintf('chmod %s %s "%s"', $flags, $mode, $file);

        /** @var Process $process */
        $process = $this->runtime->runCommand($cmd);

        return $process->isSuccessful();
    }

    protected function getParameters()
    {
        return ['file', 'mode', 'flags'];
    }

    public function getDefaults()
    {
        return ['flags' => null];
    }
}
