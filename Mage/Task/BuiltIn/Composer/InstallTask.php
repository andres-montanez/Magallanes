<?php
namespace Mage\Task\BuiltIn\Composer;

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
        $dev = $this->getParameter('dev', true);
        $optional = $this->getParameter('optional', '');

        $command = $this->getComposerCmd() . ' install' . ($dev ? ' --dev' : ' --no-dev') . ' ' . $optional;

        return $this->runCommand($command);
    }
}
