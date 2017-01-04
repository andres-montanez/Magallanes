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
 * Composer Task - Install Vendors
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class InstallTask extends AbstractTask
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
        $command = $options['path'] . ' install ' . $options['flags'];

        /** @var Process $process */
        $process = $this->runtime->runCommand($command);

        return $process->isSuccessful();
    }

    protected function getOptions()
    {
        $userOptions = $this->runtime->getConfigOptions('composer', []);
        $options = array_merge(
            ['path' => 'composer', 'flags' => '--dev'],
            (is_array($userOptions) ? $userOptions : []),
            $this->options
        );

        return $options;
    }
}
