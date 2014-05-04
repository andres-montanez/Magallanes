<?php
namespace Mage\Task\BuiltIn\Composer;

use Exception;
use Mage\Task\AbstractTask;
use Mage\Task\ErrorWithMessageException;
use Mage\Task\SkipException;

class GenerateAutoload extends AbstractTask
{
    /**
     * Returns the Title of the Task
     * @return string
     */
    public function getName()
    {
        return 'Generating autoload files via composer [built-in]';
    }

    /**
     * Runs the task
     *
     * @return boolean
     * @throws Exception
     * @throws ErrorWithMessageException
     * @throws SkipException
     */
    public function run()
    {
        $releasesDirectory = $this->getConfig()->release('directory', 'releases');
        $releasesDirectory = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $releasesDirectory;
        $currentCopy = $releasesDirectory . '/' . $this->getConfig()->getReleaseId();

        $sharedFolderName = $this->getParameter('shared', 'shared');
        $sharedFolderName = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $sharedFolderName;

        $composerPath = $this->getParameter('composer', "$sharedFolderName/composer.phar");
        return $this->runCommandRemote("/usr/bin/env php $composerPath --working-dir=$currentCopy dumpautoload --optimize", $output);
    }
}
