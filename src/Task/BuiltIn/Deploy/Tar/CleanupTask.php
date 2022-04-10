<?php

/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\Deploy\Tar;

use Mage\Task\Exception\ErrorException;
use Symfony\Component\Process\Process;
use Mage\Task\AbstractTask;

/**
 * Tar Task - Delete temporal Tar
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class CleanupTask extends AbstractTask
{
    public function getName(): string
    {
        return 'deploy/tar/cleanup';
    }

    public function getDescription(): string
    {
        return '[Deploy] Cleanup Tar file';
    }

    public function execute(): bool
    {
        if (!$this->runtime->getEnvOption('releases', false)) {
            throw new ErrorException('This task is only available with releases enabled', 40);
        }

        $tarLocal = $this->runtime->getVar('tar_local');

        $cmdDeleteTar = sprintf('rm %s', $tarLocal);

        /** @var Process $process */
        $process = $this->runtime->runLocalCommand($cmdDeleteTar);
        return $process->isSuccessful();
    }
}
