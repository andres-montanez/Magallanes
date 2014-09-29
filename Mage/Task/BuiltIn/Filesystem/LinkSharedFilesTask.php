<?php
namespace Mage\Task\BuiltIn\Filesystem;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;
use Mage\Task\SkipException;

class LinkSharedFilesTask extends AbstractTask implements IsReleaseAware
{

    const LINKED_FOLDERS   = 'linked_folders';
    const LINKED_STRATEGY  = 'linking_strategy';

    const ABSOLUTE_LINKING = 'absolute';
    const RELATIVE_LINKING = 'relative';

    public $linkingStrategies = array(
        self::ABSOLUTE_LINKING,
        self::RELATIVE_LINKING
    );
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
        $linkedFolders  = $this->getParameter(self::LINKED_FOLDERS, []);
        $linkingStrategy = $this->getParameter(self::LINKED_STRATEGY, self::ABSOLUTE_LINKING);

        $linkedEntities = array_merge($linkedFiles,$linkedFolders);

        if (sizeof($linkedFiles) == 0 && sizeof($linkedFolders) == 0) {
            throw new SkipException('No files and folders configured for sym-linking.');
        }

        $sharedFolderName = $this->getParameter('shared', 'shared');
        $sharedFolderPath = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $sharedFolderName;
        $releasesDirectory = $this->getConfig()->release('directory', 'releases');
        $releasesDirectoryPath = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $releasesDirectory;

        $currentCopy = $releasesDirectoryPath . '/' . $this->getConfig()->getReleaseId();
        $relativeDiffPath = str_replace($this->getConfig()->deployment('to'),'',$currentCopy) . '/';

        foreach ($linkedEntities as $ePath) {
            if(is_array($ePath) && in_array($strategy = reset($ePath), $this->linkingStrategies ) ) {
                $entityPath = key($ePath);
            } else {
                $strategy = $linkingStrategy;
                $entityPath = $ePath;
            }
            $sharedEntityLinkedPath = "$sharedFolderPath/$entityPath";
            if($strategy==self::RELATIVE_LINKING) {
                $parentFolderPath = dirname($entityPath);
                $relativePath = $parentFolderPath=='.'?$relativeDiffPath:$relativeDiffPath.$parentFolderPath.'/';
                $sharedEntityLinkedPath = ltrim(preg_replace('/(\w+\/)/', '../', $relativePath),'/').$sharedFolderName .'/'. $entityPath;
            }
            $command = "ln -nfs $sharedEntityLinkedPath $currentCopy/$entityPath";
            $this->runCommandRemote($command);
        }

        return true;
    }
}