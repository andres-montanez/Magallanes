<?php
namespace Mage\Task\BuiltIn\Filesystem;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;
use Mage\Task\SkipException;

class LinkSharedFilesTask extends AbstractTask implements IsReleaseAware
{

    const ABSOLUTE_LINKING = 'absolute';
    const RELATIVE_LINKING = 'relative';
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
        $linkedFiles    = $this->getParameter('linked_files', []);
        $linkedFolders  = $this->getParameter('linked_folders', []);
        $linkingStrategy = $this->getParameter('linking_stategy', self::ABSOLUTE_LINKING);

        $linkedEntities = array_merge($linkedFiles,$linkedFolders);

        if (sizeof($linkedFiles) == 0 && sizeof($linkedFolders) == 0) {
            throw new SkipException('No files and folders configured for sym-linking.');
        }

        $sharedFolderName = $this->getParameter('shared', 'shared');
        $sharedFolderPath = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $sharedFolderName;
        $releasesDirectory = $this->getConfig()->release('directory', 'releases');
        $releasesDirectoryPath = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $releasesDirectory;

        $currentCopy = $releasesDirectoryPath . '/' . $this->getConfig()->getReleaseId();
        if($linkingStrategy==self::RELATIVE_LINKING)
            $relativeDiffPath = str_replace($this->getConfig()->deployment('to'),'',$currentCopy) . '/';

        foreach ($linkedEntities as $entityPath) {
            $sharedEntityLinkedPath = "$sharedFolderPath/$entityPath";
            if($linkingStrategy==self::RELATIVE_LINKING) {
                $parentFolderPath = dirname($entityPath);
                $relativePath = empty($parentFolderPath)?$relativeDiffPath:$relativeDiffPath.$parentFolderPath.'/';
                $sharedEntityLinkedPath = ltrim(preg_replace('/(\w+\/)/', '../', $relativePath),'/').$sharedFolderName .'/'. $entityPath;
            }
            $command = "ln -nfs $sharedEntityLinkedPath $currentCopy/$entityPath";
            $this->runCommandRemote($command);
        }

        return true;
    }
}