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
 * The task interface
 *
 * @author Kamil Kuzminski <https://github.com/qzminski>
 */
interface TaskInterface
{
    /**
     * Get the Name/Code of the Task
     *
     * @return string
     */
    public function getName();

    /**
     * Get a short Description of the Task
     *
     * @return string
     */
    public function getDescription();

    /**
     * Executes the Command
     *
     * @return bool
     */
    public function execute();

    /**
     * Set additional Options for the Task
     *
     * @param array $options Options
     *
     * @return TaskInterface
     */
    public function setOptions($options = []);

    /**
     * Set the Runtime instance
     *
     * @param Runtime $runtime
     *
     * @return TaskInterface
     */
    public function setRuntime(Runtime $runtime);
}
