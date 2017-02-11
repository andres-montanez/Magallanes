<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\Composer;

use Symfony\Component\Process\Process;
use Mage\Task\AbstractTask;

/**
 * Composer Task - Generate Autoload
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class DumpAutoloadTask extends AbstractTask
{
    public function getName()
    {
        return 'composer/dump-autoload';
    }

    public function getDescription()
    {
        return '[Composer] Dump Autoload';
    }

    public function execute()
    {
        $options = $this->getOptions();
        $cmd = sprintf('%s dump-autoload %s', $options['path'], $options['flags']);

        /** @var Process $process */
        $process = $this->runtime->runCommand(trim($cmd));

        return $process->isSuccessful();
    }

    protected function getOptions()
    {
        $options = array_merge(
            ['path' => 'composer', 'flags' => '--optimize'],
            $this->runtime->getMergedOption('composer'),
            $this->options
        );

        return $options;
    }
}
