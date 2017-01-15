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
 * TarGz Task - Create temporal Tar
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class PrepareTask extends AbstractTask
{
    public function getName()
    {
        return 'deploy/targz/prepare';
    }

    public function getDescription()
    {
        return '[Deploy] Preparing TarGz file';
    }

    public function execute()
    {
        if (!$this->runtime->getEnvOption('releases', false)) {
            throw new ErrorException('This task is only available with releases enabled', 40);
        }

        $tarGzLocal = $this->runtime->getTempFile();
        $this->runtime->setVar('targz_local', $tarGzLocal);

        $excludes = $this->getExcludes();
        $flags = $this->runtime->getEnvOption('tar_create', 'cfzop');
        $cmdTarGz = sprintf('tar %s %s %s ./', $flags, $tarGzLocal, $excludes);

        /** @var Process $process */
        $process = $this->runtime->runLocalCommand($cmdTarGz, 300);
        return $process->isSuccessful();
    }

    protected function getExcludes()
    {
        $excludes = $this->runtime->getEnvOption('exclude', []);
        $excludes = array_merge(['.git'], $excludes);

        foreach ($excludes as &$exclude) {
            $exclude = '--exclude="' . $exclude . '"';
        }

        return implode(' ', $excludes);
    }
}
