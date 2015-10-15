<?php

namespace Mage\Task\Newcraft\Composer;

use Mage\Task\BuiltIn\Composer\ComposerAbstractTask;
use Mage\Task\ErrorWithMessageException;
use Mage\Task\Releases\IsReleaseAware;

class InstallTask extends ComposerAbstractTask implements IsReleaseAware
{
    /**
     * Returns the Title of the Task
     * @return string
     */
    public function getName()
    {
        return 'Install vendors via Composer [built-in]';
    }

    /**
     * Runs the task
     *
     * @return boolean
     * @throws ErrorWithMessageException
     */
    public function run()
    {
        $dev = $this->getParameter('dev', true);
        $installCommand = $this->getReleasesAwareCommand($this->getComposerCmd() . ' install' . ($dev ? ' --dev' : ' --no-dev'));
        return $this->runCommandRemote($installCommand);
    }
}
