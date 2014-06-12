<?php
namespace Mage\Task\BuiltIn\Composer;

use Mage\Task\AbstractTask;
use Mage\Task\ErrorWithMessageException;

class GenerateAutoloadTask extends AbstractTask
{
    /**
     * Returns the Title of the Task
     * @return string
     */
    public function getName()
    {
        return 'Generate autoload via Composer [built-in]';
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

        return $this->runCommand($composerPath . ' dumpautoload --optimize');
    }
}
