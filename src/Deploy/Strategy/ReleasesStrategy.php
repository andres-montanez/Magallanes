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
 * Strategy for Deployment with Releases, using Tar and SCP
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class ReleasesStrategy implements StrategyInterface
{
    /**
     * @var Runtime
     */
    protected $runtime;

    public function getName()
    {
        return 'Releases';
    }

    public function setRuntime(Runtime $runtime)
    {
        $this->runtime = $runtime;
    }

    public function getPreDeployTasks()
    {
        $this->checkStage(Runtime::PRE_DEPLOY);
        $tasks = $this->runtime->getTasks();

        if ($this->runtime->getBranch() && !$this->runtime->inRollback() && !in_array('git/change-branch', $tasks)) {
            array_unshift($tasks, 'git/change-branch');
        }

        if (!$this->runtime->inRollback() && !in_array('deploy/tar/prepare', $tasks)) {
            array_push($tasks, 'deploy/tar/prepare');
        }

        return $tasks;
    }

    public function getOnDeployTasks()
    {
        $this->checkStage(Runtime::ON_DEPLOY);
        $tasks = $this->runtime->getTasks();

        if (!$this->runtime->inRollback() && !in_array('deploy/tar/copy', $tasks)) {
            array_unshift($tasks, 'deploy/tar/copy');
        }

        if (!$this->runtime->inRollback() && !in_array('deploy/release/prepare', $tasks)) {
            array_unshift($tasks, 'deploy/release/prepare');
        }

        return $tasks;
    }

    public function getOnReleaseTasks()
    {
        $this->checkStage(Runtime::ON_RELEASE);
        $tasks = $this->runtime->getTasks();

        if (!in_array('deploy/release', $tasks)) {
            array_unshift($tasks, 'deploy/release');
        }

        return $tasks;
    }

    public function getPostReleaseTasks()
    {
        $this->checkStage(Runtime::POST_RELEASE);
        $tasks = $this->runtime->getTasks();

        if (!in_array('deploy/release/cleanup', $tasks)) {
            array_unshift($tasks, 'deploy/release/cleanup');
        }

        return $tasks;
    }

    public function getPostDeployTasks()
    {
        $this->checkStage(Runtime::POST_DEPLOY);
        $tasks = $this->runtime->getTasks();

        if (!$this->runtime->inRollback() && !in_array('deploy/tar/cleanup', $tasks)) {
            array_unshift($tasks, 'deploy/tar/cleanup');
        }

        if ($this->runtime->getBranch() && !$this->runtime->inRollback() && !in_array('git/change-branch', $tasks)) {
            array_push($tasks, 'git/change-branch');
        }

        return $tasks;
    }

    /**
     * Check the runtime stage is correct
     *
     * @param $stage
     * @throws RuntimeException
     */
    private function checkStage($stage)
    {
        if ($this->runtime->getStage() !== $stage) {
            throw new RuntimeException(sprintf('Invalid stage, got "%s" but expected "%"', $this->runtime->getStage(), $stage));
        }
    }
}
