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
use Mage\Task\Exception\SkipException;

/**
 * Git Task - Pull
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class UpdateTask extends AbstractTask
{
    public function getName(): string
    {
        return 'git/update';
    }

    public function getDescription(): string
    {
        return '[Git] Update';
    }

    public function execute(): bool
    {
        $options = $this->getOptions();
        if ($options['tag']) {
            throw new SkipException();
        }

        $command = $options['path'] . ' pull';

        $process = $this->runtime->runLocalCommand($command);

        return $process->isSuccessful();
    }

    /**
     * @return array<string, string>
     */
    protected function getOptions(): array
    {
        $branch = $this->runtime->getEnvOption('branch', 'master');
        $tag = $this->runtime->getEnvOption('tag', false);

        $options = array_merge(
            ['path' => 'git', 'branch' => $branch, 'tag' => $tag],
            $this->options
        );

        return $options;
    }
}
