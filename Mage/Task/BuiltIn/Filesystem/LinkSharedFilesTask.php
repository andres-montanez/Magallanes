<?php
namespace Mage\Task\BuiltIn\Filesystem;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;
use Mage\Task\SkipException;

/**
 * Class LinkSharedFilesTask
 *
 * @package Mage\Task\BuiltIn\Filesystem
 * @author Andrey Kolchenko <andrey@kolchenko.me>
 */
class LinkSharedFilesTask extends AbstractTask implements IsReleaseAware
{

    /**
     * Returns the Title of the Task
     *
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
        if (empty($linkedFiles) && empty($linkedFolders)) {
            throw new SkipException('No files and folders configured for sym-linking.');
        }
        $remoteDirectory = rtrim($this->getConfig()->deployment('to'), '/') . '/';
        $sharedFolderName = $remoteDirectory . $this->getParameter('shared', 'shared');
        $releasesDirectory = $remoteDirectory . $this->getConfig()->release('directory', 'releases');
        $currentCopy = $releasesDirectory . '/' . $this->getConfig()->getReleaseId();
        foreach ($linkedFolders as $folder) {
            $target = escapeshellarg($sharedFolderName . '/' . $folder);
            $command = 'mkdir -p ' . $target;
            $this->runCommandRemote($command);
            $command = 'ln -s ' . $target . ' ' . escapeshellarg($currentCopy . '/' . $folder);
            $this->runCommandRemote($command);
        }

        foreach ($linkedFiles as $file) {
            $command = 'mkdir -p ' . escapeshellarg(dirname($sharedFolderName . '/' . $file));
            $this->runCommandRemote($command);
            $target = escapeshellarg($sharedFolderName . '/' . $file);
            $command = 'ln -s ' . $target . ' ' . escapeshellarg($currentCopy . '/' . $file);
            $this->runCommandRemote($command);
        }

        return true;
    }
}
