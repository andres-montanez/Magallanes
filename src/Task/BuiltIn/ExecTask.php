<?php

/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn;

use Mage\Task\Exception\ErrorException;
use Mage\Task\AbstractTask;
use Symfony\Component\Process\Process;

/**
 * Exec task. Allows you to execute arbitrary commands.
 *
 * @author Yanick Witschi <https://github.com/Toflar>
 */
class ExecTask extends AbstractTask
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'exec';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        $options = $this->getOptions();

        if ($options['desc']) {
            return '[Exec] ' . $options['desc'];
        }

        return '[Exec] Custom command';
    }

    /**
     * @return bool
     *
     * @throws ErrorException
     */
    public function execute(): bool
    {
        $options = $this->getOptions();

        if (!$options['cmd']) {
            throw new ErrorException('Parameter "cmd" is not defined');
        }

        $mapping = [
            '%environment%' => $this->runtime->getEnvironment(),
        ];

        if ($this->runtime->getReleaseId() !== null) {
            $mapping['%release%'] = $this->runtime->getReleaseId();
        }

        $cmd = str_replace(
            array_keys($mapping),
            array_values($mapping),
            strval($options['cmd'])
        );

        /** @var Process $process */
        $process = $this->runtime->runCommand($cmd, intval($options['timeout']));
        return $process->isSuccessful();
    }

    /**
     * @return array<string, string|int>
     */
    protected function getOptions(): array
    {
        $options = array_merge(
            ['cmd' => '', 'desc' => '', 'timeout' => 120],
            $this->options
        );

        return $options;
    }
}
