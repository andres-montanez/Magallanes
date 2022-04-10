<?php

/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\Symfony;

use Symfony\Component\Process\Process;

/**
 * Symfony Task - Install Assets
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class AssetsInstallTask extends AbstractSymfonyTask
{
    public function getName(): string
    {
        return 'symfony/assets-install';
    }

    public function getDescription(): string
    {
        return '[Symfony] Assets Install';
    }

    public function execute(): bool
    {
        $options = $this->getOptions();
        $command = sprintf(
            '%s assets:install %s --env=%s %s',
            $options['console'],
            $options['target'],
            $options['env'],
            $options['flags']
        );

        $process = $this->runtime->runCommand(trim($command));

        return $process->isSuccessful();
    }

    protected function getSymfonyOptions(): array
    {
        return ['target' => 'web', 'flags' => '--symlink --relative'];
    }
}
