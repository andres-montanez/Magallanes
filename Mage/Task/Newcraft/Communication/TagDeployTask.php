<?php

namespace Mage\Task\Newcraft\Communication;

use Mage\Task\AbstractTask;

class TagDeployTask extends AbstractTask
{
    /**
     * Returns the Title of the Task
     * @return string
     */
    public function getName()
    {
        return 'Tag commit as deployed [newcraft]';
    }

    /**
     * Runs the task
     *
     * @return boolean
     */
    public function run()
    {
        $tagName = 'on-'.$this->getConfig()->getEnvironment();

        $moveTagCommand = 'git tag '.escapeshellarg($tagName).' -f';
        $pushTagCommand = 'git push -f origin refs/tags/'.escapeshellarg($tagName);

        return $this->runCommandLocal($moveTagCommand.' && '.$pushTagCommand);
    }
}