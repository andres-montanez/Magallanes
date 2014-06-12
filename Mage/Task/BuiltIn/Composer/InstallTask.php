<?php
namespace Mage\Task\BuiltIn\Composer;

use Mage\Task\AbstractTask;
use Mage\Task\ErrorWithMessageException;

class InstallTask extends AbstractTask
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
        $composerPath = $this->getConfig()->general('composer_path', 'php composer.phar');

        return $this->runCommand($composerPath . ' install');
    }
}
