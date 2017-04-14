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

use Mage\Task\AbstractTask;

/**
 * Abstract Composer Task
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
abstract class AbstractComposerTask extends AbstractTask
{
    protected function getOptions()
    {
        $options = array_merge(
            ['path' => 'composer'],
            $this->getComposerOptions(),
            $this->runtime->getMergedOption('composer'),
            $this->options
        );

        return $options;
    }

    protected function getComposerOptions()
    {
        return [];
    }
}
