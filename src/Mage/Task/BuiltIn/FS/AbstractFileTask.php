<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\FS;

use Mage\Runtime\Exception\RuntimeException;
use Mage\Task\AbstractTask;

/**
 * File System Task - Abstract Base class for File Operations
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
abstract class AbstractFileTask extends AbstractTask
{
    protected function getOptions()
    {
        $mandatory = $this->getParameters();

        foreach ($mandatory as $parameter) {
            if (!array_key_exists($parameter, $this->options)) {
                throw new RuntimeException(sprintf('Parameter "%s" is not defined', $parameter));
            }
        }

        return $this->options;
    }

    abstract protected function getParameters();

    protected function getFile($file)
    {
        $mapping = [
            '%environment%' => $this->runtime->getEnvironment(),
        ];

        if ($this->runtime->getWorkingHost()) {
            $mapping['%host%'] = $this->runtime->getWorkingHost();
        }

        if ($this->runtime->getReleaseId()) {
            $mapping['%release%'] = $this->runtime->getReleaseId();
        }

        $options = $this->getOptions();
        return str_replace(
            array_keys($mapping),
            array_values($mapping),
            $options[$file]
        );
    }
}
