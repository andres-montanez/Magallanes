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

/**
 * Composer Task - Install Vendors
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class InstallTask extends AbstractComposerTask
{
    public function getName()
    {
        return 'composer/install';
    }

    public function getDescription()
    {
        return '[Composer] Install';
    }

    public function execute()
    {
        $options = $this->getOptions();
        $cmd = sprintf('%s install %s', $options['path'], $options['flags']);

        /** @var Process $process */
        $process = $this->runtime->runCommand(trim($cmd), $options['timeout']);

        return $process->isSuccessful();
    }

    protected function getComposerOptions()
    {
        return ['flags' => '--optimize-autoloader', 'timeout' => 120];
    }
}
