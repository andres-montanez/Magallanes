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

use Mage\Task\Exception\ErrorException;
use Symfony\Component\Process\Process;

/**
 * Symfony Task - Cache Pool Clear
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class CachePoolClearTask extends AbstractSymfonyTask
{
    public function getName()
    {
        return 'symfony/cache-pool-clear';
    }

    public function getDescription()
    {
        return '[Symfony] Cache Pool Clear';
    }

    public function execute()
    {
        $options = $this->getOptions();

        if (!$options['pools']) {
            throw new ErrorException('Parameter "pools" is not defined');
        }

        $command = $options['console'] . ' cache:pool:clear ' . $options['pools'] . ' --env=' . $options['env'] . ' ' . $options['flags'];

        /** @var Process $process */
        $process = $this->runtime->runCommand(trim($command));

        return $process->isSuccessful();
    }

    protected function getSymfonyOptions()
    {
        return ['pools' => null];
    }
}
