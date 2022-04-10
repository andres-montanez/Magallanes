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
    /** @var array<string, string|int|null> */
    protected array $options = [];

    protected Runtime $runtime;

    /**
     * Get the Name/Code of the Task
     */
    abstract public function getName(): string;

    /**
     * Get a short Description of the Task
     */
    abstract public function getDescription(): string;

    /**
     * Executes the Command
     */
    abstract public function execute(): bool;

    /**
     * Set additional Options for the Task
     *
     * @param array<string, string|int|null> $options
     */
    public function setOptions(array $options = []): self
    {
        $this->options = array_merge($this->getDefaults(), $options);
        return $this;
    }

    /**
     * Set the Runtime instance
     */
    public function setRuntime(Runtime $runtime): self
    {
        $this->runtime = $runtime;
        return $this;
    }

    /**
     * Return Default options
     *
     * @return array<string, string|int|null>
     */
    public function getDefaults(): array
    {
        return [];
    }
}
