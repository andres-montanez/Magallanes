<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\Git;

use Symfony\Component\Process\Process;
use Mage\Task\AbstractTask;

/**
 * Git Task - Pull
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class UpdateTask extends AbstractTask
{
    public function getName()
    {
        return 'git/update';
    }

    public function getDescription()
    {
        return '[Git] Update';
    }

    public function execute()
    {
        $options = $this->getOptions();
        $command = $options['path'] . ' pull';

        /** @var Process $process */
        $process = $this->runtime->runLocalCommand($command);

        return $process->isSuccessful();
    }

    protected function getOptions()
    {
        $branch = $this->runtime->getEnvOption('branch', 'master');
        $options = array_merge(
            ['path' => 'git', 'branch' => $branch],
            $this->options
        );

        return $options;
    }
}
