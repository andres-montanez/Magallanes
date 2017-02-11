<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task;

use Mage\Runtime\Runtime;

/**
 * Abstract base class for Magallanes Tasks
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
abstract class AbstractTask
{
    /**
     * @var array Task custom options
     */
    protected $options = [];

    /**
     * @var Runtime
     */
    protected $runtime;

    /**
     * Get the Name/Code of the Task
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Get a short Description of the Task
     *
     * @return string
     */
    abstract public function getDescription();

    /**
     * Executes the Command
     *
     * @return bool
     */
    abstract public function execute();

    /**
     * Set additional Options for the Task
     *
     * @param array $options Options
     * @return AbstractTask
     */
    public function setOptions($options = [])
    {
        if (!is_array($options)) {
            $options = [];
        }

        $this->options = array_merge($this->getDefaults(), $options);
        return $this;
    }

    /**
     * Set the Runtime instance
     *
     * @param Runtime $runtime
     * @return AbstractTask
     */
    public function setRuntime(Runtime $runtime)
    {
        $this->runtime = $runtime;
        return $this;
    }

    /**
     * Return Default options
     * @return array
     */
    public function getDefaults()
    {
        return [];
    }
}
