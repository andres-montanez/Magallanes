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
    /**
     * @return string
     */
    public function getName()
    {
        return 'sleep';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $options = $this->getOptions();

        return sprintf('[Sleep] Sleeping for %s second(s)', (int) $options['seconds']);
    }

    /**
     * @return bool
     *
     * @throws ErrorException
     */
    public function execute()
    {
        $options = $this->getOptions();

        sleep((int) $options['seconds']);

        return true;
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        $options = array_merge(
            ['seconds' => 1],
            $this->options
        );

        return $options;
    }
}
