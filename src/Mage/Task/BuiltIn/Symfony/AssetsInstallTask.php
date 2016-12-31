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
 * Symfony Task - Install Assets
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class AssetsInstallTask extends AbstractTask
{
    public function getName()
    {
        return 'symfony/assets-install';
    }

    public function getDescription()
    {
        return '[Symfony] Assets Install';
    }

    public function execute()
    {
        $options = $this->getOptions();
        $command = $options['console'] . ' assets:install --env=' . $options['env'] . ' ' . $options['flags'] . ' ' . $options['target'];

        /** @var Process $process */
        $process = $this->runtime->runCommand($command);

        return $process->isSuccessful();
    }

    protected function getOptions()
    {
        $options = array_merge(
            ['path' => 'bin/console', 'env' => 'dev', 'target' => 'web', 'flags' => '--symlink --relative'],
            $this->runtime->getConfigOptions('symfony', []),
            $this->options
        );

        return $options;
    }
}
