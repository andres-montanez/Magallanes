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
 * File System Task - Move a File
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class MoveTask extends AbstractFileTask
{
    public function getName(): string
    {
        return 'fs/move';
    }

    public function getDescription(): string
    {
        try {
            return sprintf('[FS] Move "%s" to "%s"', $this->getFile('from'), $this->getFile('to'));
        } catch (Exception $exception) {
            return '[FS] Move [missing parameters]';
        }
    }

    public function execute(): bool
    {
        $moveFrom = $this->getFile('from');
        $moveTo = $this->getFile('to');
        $flags = $this->options['flags'];

        $cmd = sprintf('mv %s "%s" "%s"', $flags, $moveFrom, $moveTo);

        /** @var Process $process */
        $process = $this->runtime->runCommand($cmd);

        return $process->isSuccessful();
    }

    protected function getParameters(): array
    {
        return ['from', 'to', 'flags'];
    }

    public function getDefaults(): array
    {
        return ['flags' => null];
    }
}
