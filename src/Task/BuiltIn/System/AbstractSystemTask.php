<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Halis Duraki <duraki.halis@nsoft.ba>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\System;

use Mage\Task\Exception\ErrorException;
use Mage\Task\AbstractTask;

/**
 * System Kernel Task - Abstract Base class for System Operations
 *
 * @author Halis Duraki <duraki.halis@nsoft.ba>
 */
abstract class AbstractSystemTask extends AbstractTask
{
    /**
     * Returns the Task options
     *
     * @return array
     * @throws ErrorException
     */
    protected function getOptions()
    {
        $mandatory = $this->getParameters();

        foreach ($mandatory as $parameter) {
            if (!array_key_exists($parameter, $this->options)) {
                throw new ErrorException(sprintf('Parameter "%s" is not defined', $parameter));
            }
        }

        return $this->options;
    }

    /**
     * Returns the mandatory parameters
     *
     * @return array
     */
    abstract protected function getParameters();

    /**
     * Returns command with the placeholders replaced
     *
     * @param  string $command 
     * @return string          
     * @throws ErrorException 
     */
    protected function getCommand($command)
    {
        $mapping = [
            '%environment%' => $this->runtime->getEnvironment(),
        ];

        if ($this->runtime->getWorkingHost() !== null) {
            $mapping['%host%'] = $this->runtime->getWorkingHost();
        }

        if ($this->runtime->getReleaseId() !== null) {
            $mapping['%release%'] = $this->runtime->getReleaseId();
        }

        $options = $this->getOptions();
        return str_replace(
            array_keys($mapping),
            array_values($mapping),
            $options[$command]
        );
    }

}
