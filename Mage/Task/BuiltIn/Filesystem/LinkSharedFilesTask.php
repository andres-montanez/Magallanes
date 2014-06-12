<?php
namespace Mage\Task\BuiltIn\Filesystem;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;
use Mage\Task\SkipException;

class LinkSharedFilesTask extends AbstractTask implements IsReleaseAware
{

    /**
     * Returns the Title of the Task
     * @return string
     */
    public function getName()
    {
        return 'Linking files/folders from the shared folder into the current release [built-in]';
    }

    /**
     * Runs the task
     *
     * @return boolean
     * @throws SkipException
     */
    public function run()
    {
        $linkedFiles = $this->getParameter('linked_files', []);
        $linkedFolders = $this->getParameter('linked_folders', []);
        if (sizeof($linkedFiles) == 0 && sizeof($linkedFolders) == 0) {
            throw new SkipException('No files and folders configured for sym-linking.');
        }

        $sharedFolderName = $this->getParameter('shared', 'shared');
        $sharedFolderName = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $sharedFolderName;
        $releasesDirectory = $this->getConfig()->release('directory', 'releases');
        $releasesDirectory = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $releasesDirectory;

        $currentCopy = $releasesDirectory . '/' . $this->getConfig()->getReleaseId();
        foreach ($linkedFolders as $folder) {
            $command = "ln -nfs $sharedFolderName/$folder $currentCopy/$folder";
            $this->runCommandRemote($command);
        }

        foreach ($linkedFiles as $folder) {
            $command = "ln -nfs $sharedFolderName/$folder $currentCopy/$folder";
            $this->runCommandRemote($command);
        }

        return true;
    }
}
