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
    public function getName()
    {
        return 'exec';
    }

    /**
     * @return string
     */
    public function getDescription()
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
    public function execute()
    {
        $options = $this->getOptions();

        if (!$options['cmd']) {
            throw new ErrorException('Parameter "cmd" is not defined');
        }

        /** @var Process $process */
        $process = $this->runtime->runCommand($options['cmd'], $options['timeout']);
        return $process->isSuccessful();
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        $options = array_merge(
            ['cmd' => '', 'desc' => '', 'timeout' => 120],
            $this->options
        );

        return $options;
    }
}
