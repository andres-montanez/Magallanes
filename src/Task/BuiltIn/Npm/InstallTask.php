<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\Npm;

use Symfony\Component\Process\Process;
use Mage\Task\AbstractTask;

/**
 * NPM Task - Installs npn packages
 *
 * @author Benjamin Gutmann <benjamin.gutmann@bestit-online.de>
 * @author Alexander Schneider <alexanderschneider85@gmail.com>
 */
class InstallTask extends AbstractTask
{
    public function getName()
    {
        return 'npm/install';
    }

    public function getDescription()
    {
        return '[NPM] Install';
    }

    public function execute()
    {
        $flags = isset($this->options['flags']) ? $this->options['flags'] : '';
        $cmd = sprintf('npm install %s', $flags);

        /** @var Process $process */
        $process = $this->runtime->runCommand($cmd);

        return $process->isSuccessful();
    }
}
