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
 * Symfony Task - Cache Warmup
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class CacheWarmupTask extends AbstractSymfonyTask
{
    public function getName(): string
    {
        return 'symfony/cache-warmup';
    }

    public function getDescription(): string
    {
        return '[Symfony] Cache Warmup';
    }

    public function execute(): bool
    {
        $options = $this->getOptions();
        $command = $options['console'] . ' cache:warmup --env=' . $options['env'] . ' ' . $options['flags'];

        /** @var Process $process */
        $process = $this->runtime->runCommand(trim($command));

        return $process->isSuccessful();
    }
}
