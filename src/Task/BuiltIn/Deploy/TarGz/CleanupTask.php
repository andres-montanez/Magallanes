<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\Deploy\TarGz;

use Mage\Task\Exception\ErrorException;
use Symfony\Component\Process\Process;
use Mage\Task\AbstractTask;

/**
 * TarGz Task - Delete temporal Tar
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class CleanupTask extends AbstractTask
{
    public function getName()
    {
        return 'deploy/targz/cleanup';
    }

    public function getDescription()
    {
        return '[Deploy] Cleanup TarGZ file';
    }

    public function execute()
    {
        if (!$this->runtime->getEnvParam('releases', false)) {
            throw new ErrorException('This task is only available with releases enabled', 40);
        }

        $tarGzLocal = $this->runtime->getVar('targz_local');

        $cmdDeleteTarGz = sprintf('rm %s', $tarGzLocal);

        /** @var Process $process */
        $process = $this->runtime->runLocalCommand($cmdDeleteTarGz);
        return $process->isSuccessful();
    }
}
