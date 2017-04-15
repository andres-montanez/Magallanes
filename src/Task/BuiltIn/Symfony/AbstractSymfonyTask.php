<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\Symfony;

use Mage\Task\AbstractTask;

/**
 * Abstract Symfony Task
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
abstract class AbstractSymfonyTask extends AbstractTask
{
    protected function getOptions()
    {
        $options = array_merge(
            ['console' => 'bin/console', 'env' => 'dev', 'flags' => ''],
            $this->getSymfonyOptions(),
            $this->runtime->getMergedOption('symfony'),
            $this->options
        );

        return $options;
    }

    protected function getSymfonyOptions()
    {
        return [];
    }
}
