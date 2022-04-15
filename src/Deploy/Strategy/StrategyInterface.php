<?php

/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Deploy\Strategy;

use Mage\Runtime\Runtime;

/**
 * Interface for Deploy Strategies
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
interface StrategyInterface
{
    public function getName(): string;

    public function setRuntime(Runtime $runtime): void;

    /**
     * @return string[]
     */
    public function getPreDeployTasks(): array;

    /**
     * @return string[]
     */
    public function getOnDeployTasks(): array;

    /**
     * @return string[]
     */
    public function getOnReleaseTasks(): array;

    /**
     * @return string[]
     */
    public function getPostReleaseTasks(): array;

    /**
     * @return string[]
     */
    public function getPostDeployTasks(): array;
}
