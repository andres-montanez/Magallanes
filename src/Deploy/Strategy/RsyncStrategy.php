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

use Mage\Runtime\Exception\RuntimeException;
use Mage\Runtime\Runtime;

/**
 * Strategy for Deployment with Rsync
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class RsyncStrategy implements StrategyInterface
{
    protected Runtime $runtime;

    public function getName(): string
    {
        return 'Rsync';
    }

    public function setRuntime(Runtime $runtime): void
    {
        $this->runtime = $runtime;
    }

    public function getPreDeployTasks(): array
    {
        $this->checkStage(Runtime::PRE_DEPLOY);
        $tasks = $this->runtime->getTasks();

        if (
            ($this->runtime->getBranch() || $this->runtime->getTag()) &&
            !$this->runtime->inRollback() &&
            !in_array('git/change-branch', $tasks)
        ) {
            array_unshift($tasks, 'git/change-branch');
        }

        return $tasks;
    }

    public function getOnDeployTasks(): array
    {
        $this->checkStage(Runtime::ON_DEPLOY);
        $tasks = $this->runtime->getTasks();

        if (!$this->runtime->inRollback() && !in_array('deploy/rsync', $tasks)) {
            array_unshift($tasks, 'deploy/rsync');
        }

        return $tasks;
    }

    public function getOnReleaseTasks(): array
    {
        return [];
    }

    public function getPostReleaseTasks(): array
    {
        return [];
    }

    public function getPostDeployTasks(): array
    {
        $this->checkStage(Runtime::POST_DEPLOY);
        $tasks = $this->runtime->getTasks();

        if (
            ($this->runtime->getBranch() ||
            $this->runtime->getTag()) &&
            !$this->runtime->inRollback() &&
            !in_array('git/change-branch', $tasks)
        ) {
            array_push($tasks, 'git/change-branch');
        }

        return $tasks;
    }

    /**
     * Check the runtime stage is correct
     *
     * @throws RuntimeException
     */
    private function checkStage(string $stage): void
    {
        if ($this->runtime->getStage() !== $stage) {
            throw new RuntimeException(
                sprintf('Invalid stage, got "%s" but expected "%s"', $this->runtime->getStage(), $stage)
            );
        }
    }
}
