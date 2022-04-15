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
 * Symfony Task - Cache Pool Prune
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class CachePoolPruneTask extends AbstractSymfonyTask
{
    public function getName(): string
    {
        return 'symfony/cache-pool-prune';
    }

    public function getDescription(): string
    {
        return '[Symfony] Cache Pool Prune';
    }

    public function execute(): bool
    {
        $options = $this->getOptions();
        $command = $options['console'] . ' cache:pool:prune --env=' . $options['env'] . ' ' . $options['flags'];

        /** @var Process $process */
        $process = $this->runtime->runCommand(trim($command));

        return $process->isSuccessful();
    }
}
