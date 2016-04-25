<?php

namespace Mage\Task\BuiltIn\Composer;

use Mage\Task\ErrorWithMessageException;

class DownloadTask extends ComposerAbstractTask
{
    /**
     * Returns the Title of the Task
     * @return string
     */
    public function getName()
    {
        return 'Download current version of composer [built-in]';
    }

    /**
     * Runs the task
     *
     * @return boolean
     * @throws ErrorWithMessageException
     */
    public function run()
    {
        $command = "wget https://getcomposer.org/composer.phar";
        $this->runCommandRemote($command);

        return true;
    }
}
