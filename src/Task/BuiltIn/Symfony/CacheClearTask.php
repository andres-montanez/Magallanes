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
use Mage\Task\AbstractTask;

/**
 * Symfony Task - Clear Cache
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class CacheClearTask extends AbstractTask
{
    public function getName()
    {
        return 'symfony/cache-clear';
    }

    public function getDescription()
    {
        return '[Symfony] Cache Clear';
    }

    public function execute()
    {
        $options = $this->getOptions();
        $command = $options['console'] . ' cache:clear --env=' . $options['env'] . ' ' . $options['flags'];

        /** @var Process $process */
        $process = $this->runtime->runCommand(trim($command));

        return $process->isSuccessful();
    }

    protected function getOptions()
    {
        $options = array_merge(
            ['console' => 'bin/console', 'env' => 'dev', 'flags' => ''],
            $this->runtime->getMergedOption('symfony'),
            $this->options
        );

        return $options;
    }
}
