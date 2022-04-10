<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests\Task\BuiltIn\Composer;

use Mage\Task\BuiltIn\Composer\AbstractComposerTask;
use Symfony\Component\Process\Process;

/**
 * Basic Composer Task
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class BasicComposerTask extends AbstractComposerTask
{
    public function getName(): string
    {
        return 'composer/help';
    }

    public function getDescription(): string
    {
        return '[Composer] Help';
    }

    public function execute(): bool
    {
        $options = $this->getOptions();
        $cmd = sprintf('%s help', $options['path']);

        /** @var Process $process */
        $process = $this->runtime->runCommand(trim($cmd));

        return $process->isSuccessful();
    }
}
