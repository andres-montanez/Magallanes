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

use Mage\Task\Exception\ErrorException;
use Mage\Task\AbstractTask;

/**
 * File System Task - Abstract Base class for File Operations
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
abstract class AbstractFileTask extends AbstractTask
{
    /**
     * Returns the Task options
     *
     * @return array<string, string|int|null>
     * @throws ErrorException
     */
    protected function getOptions(): array
    {
        $mandatory = $this->getParameters();
        $defaults = array_keys($this->getDefaults());
        $missing = array_diff($mandatory, $defaults);

        foreach ($missing as $parameter) {
            if (!array_key_exists($parameter, $this->options)) {
                throw new ErrorException(sprintf('Parameter "%s" is not defined', $parameter));
            }
        }

        return $this->options;
    }

    /**
     * Returns the mandatory parameters
     *
     * @return string[]
     */
    abstract protected function getParameters(): array;

    /**
     * Returns a file with the placeholders replaced
     *
     * @throws ErrorException
     */
    protected function getFile(string $file): string
    {
        $mapping = [
            '%environment%' => $this->runtime->getEnvironment(),
        ];

        if ($this->runtime->getHostName() !== null) {
            $mapping['%host%'] = $this->runtime->getHostName();
        }

        if ($this->runtime->getReleaseId() !== null) {
            $mapping['%release%'] = $this->runtime->getReleaseId();
        }

        $options = $this->getOptions();
        return str_replace(
            array_keys($mapping),
            array_values($mapping),
            strval($options[$file])
        );
    }
}
