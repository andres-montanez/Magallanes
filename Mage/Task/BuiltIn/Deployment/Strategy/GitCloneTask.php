<?php

namespace Mage\Task\BuiltIn\Deployment\Strategy;

use Mage\Task\Releases\IsReleaseAware;

class GitCloneTask extends ReleasesAbstractTask implements IsReleaseAware
{
    public function getName()
    {
        if ($this->getConfig()->release('enabled', false) == true) {
            if ($this->getConfig()->getParameter('overrideRelease', false) == true) {
                return 'Deploy via git clone (with Releases override) [built-in]';
            } else {
                return 'Deploy via git clone (with Releases) [built-in]';
            }
        } else {
            return 'Deploy via git clone [built-in]';
        }
    }

    public function deploy()
    {
        $branch = $this->getConfig()->getOption('environment.deployment.scm.branch');
        $remote = $this->getConfig()->getOption('environment.deployment.scm.url',
            $this->getConfig()->getOption('general.scm.url'));
        $releaseDirectory = $this->getConfig()->getDeployToDirectory();

        $command = "git clone -b $branch $remote $releaseDirectory";

        if (! $this->isLocalRelease()) {
            $this->runJobRemote($command);
        } else {
            $this->runJobLocal($command);
        }
    }
}