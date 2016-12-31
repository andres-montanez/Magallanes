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
 * Symfony Task - Dump Assetics
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class AsseticDumpTask extends AbstractTask
{
    public function getName()
    {
        return 'symfony/assetic-dump';
    }

    public function getDescription()
    {
        return '[Symfony] Assetic Dump';
    }

    public function execute()
    {
        $options = $this->getOptions();
        $command = $options['console'] . ' assetic:dump --env=' . $options['env'] . ' ' . $options['flags'];

        /** @var Process $process */
        $process = $this->runtime->runCommand($command);

        return $process->isSuccessful();
    }

    protected function getOptions()
    {
        $options = array_merge(
            ['path' => 'bin/console', 'env' => 'dev', 'flags' => ''],
            $this->runtime->getConfigOptions('symfony', []),
            $this->options
        );

        return $options;
    }
}
