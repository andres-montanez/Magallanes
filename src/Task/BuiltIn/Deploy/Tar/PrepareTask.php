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
 * Tar Task - Create temporal Tar
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class PrepareTask extends AbstractTask
{
    public function getName(): string
    {
        return 'deploy/tar/prepare';
    }

    public function getDescription(): string
    {
        return '[Deploy] Preparing Tar file';
    }

    public function execute(): bool
    {
        if (!$this->runtime->getEnvOption('releases', false)) {
            throw new ErrorException('This task is only available with releases enabled', 40);
        }

        $tarLocal = $this->runtime->getTempFile();
        $this->runtime->setVar('tar_local', $tarLocal);

        $excludes = $this->getExcludes();
        $tarPath = $this->runtime->getEnvOption('tar_create_path', 'tar');
        $flags = $this->runtime->getEnvOption(
            'tar_create',
            $this->runtime->isWindows() ? '--force-local -c -z -p -f' : 'cfzp'
        );
        $from = $this->runtime->getEnvOption('from', './');

        if ($this->runtime->getEnvOption('copyDirectory', false)) {
            $from = sprintf('-C %s ./', $from);
        }

        $cmdTar = sprintf('%s %s %s %s %s', $tarPath, $flags, $tarLocal, $excludes, $from);

        /** @var Process $process */
        $process = $this->runtime->runLocalCommand($cmdTar, 300);
        return $process->isSuccessful();
    }

    protected function getExcludes(): string
    {
        $excludes = $this->runtime->getMergedOption('exclude', []);
        $excludes = array_merge(['.git'], array_filter($excludes));

        foreach ($excludes as &$exclude) {
            $exclude = '--exclude="' . $exclude . '"';
        }

        return implode(' ', $excludes);
    }
}
