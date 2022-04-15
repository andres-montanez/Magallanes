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

/**
 * Sleep task. Sleeps for a given number of seconds so you can delay task
 * execution.
 *
 * @author Yanick Witschi <https://github.com/Toflar>
 */
class SleepTask extends AbstractTask
{
    public function getName(): string
    {
        return 'sleep';
    }

    public function getDescription(): string
    {
        $options = $this->getOptions();

        return sprintf('[Sleep] Sleeping for %d second(s)', $options['seconds']);
    }

    /**
     * @throws ErrorException
     */
    public function execute(): bool
    {
        $options = $this->getOptions();

        sleep(intval($options['seconds']));

        return true;
    }

    /**
     * @return array<string, string|int>
     */
    protected function getOptions(): array
    {
        $options = array_merge(
            ['seconds' => 1],
            $this->options
        );

        return $options;
    }
}
