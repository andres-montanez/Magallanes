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
    /**
     * @return array<string, string>
     */
    protected function getOptions(): array
    {
        $options = array_merge(
            ['console' => 'bin/console', 'env' => 'dev', 'flags' => ''],
            $this->getSymfonyOptions(),
            $this->runtime->getMergedOption('symfony'),
            $this->options
        );

        return $options;
    }

    /**
     * @return array<string, string|null>
     */
    protected function getSymfonyOptions(): array
    {
        return [];
    }
}
