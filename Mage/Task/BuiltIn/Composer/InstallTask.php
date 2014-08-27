<?php
namespace Mage\Task\BuiltIn\Composer;

use Mage\Task\BuiltIn\Composer\ComposerAbstractTask;
use Mage\Task\ErrorWithMessageException;

class InstallTask extends ComposerAbstractTask
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
        // Options
        $dev = $this->getParameter('dev', true);
        $preCommand = $this->getParameter('preCommand', '');

        return $this->runCommand(' '.$preCommand.' '.$this->getComposerCmd() . ' install' . ($dev ? ' --dev' : ' --no-dev'));
    }
}
