<?php

namespace Mage\Task\BuiltIn\Deployment\Strategy;

use Mage\Task\Releases\IsReleaseAware;

/**
 * The git clone deployment task.
 *
 * This task use a git clone strategy for getting files from remote scm
 * You need to define git-repository and git-branch in deployment section
 *
 * @package Mage\Task\BuiltIn\Deployment\Strategy
 * @author Stepan Yamilov <yamilovs@gmail.com>
 */
class GitCloneTask extends BaseStrategyTaskAbstract implements IsReleaseAware
{
    /**
     * Returns the Title of the Task
     * @return string
     */
    public function getName()
    {
        return 'Deploy via remote git clone [built-in]';
    }

    public function run()
    {
        $repository = $this->getConfig()->deployment('repository');
        $branch = $this->getConfig()->deployment('branch', 'master');
        $deployToDirectory = $this->getConfig()->deployment('to');

        if (!$repository or !$branch or !$deployToDirectory) return false;

        $this->checkOverrideRelease();

        if ($this->getConfig()->release('enabled', false) === true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');

            $deployToDirectory = rtrim($this->getConfig()->deployment('to'), '/')
                . '/' . $releasesDirectory
                . '/' . $this->getConfig()->getReleaseId();
            $this->runCommandRemote('mkdir -p ' . $releasesDirectory . '/' . $this->getConfig()->getReleaseId());
        }

        $command = "/usr/bin/env git clone -b $branch $repository $deployToDirectory";
        $result = $this->runCommandRemote($command);

        return $result;
    }
}