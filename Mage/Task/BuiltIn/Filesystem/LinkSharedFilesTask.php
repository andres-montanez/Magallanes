<?php
namespace Mage\Task\BuiltIn\Filesystem;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;
use Mage\Task\SkipException;

/**
 * Class LinkSharedFilesTask
 *
 * @package Mage\Task\BuiltIn\Filesystem
 * @author Andrey Kolchenko <a.j.kolchenko@baltsoftservice.ru>
 */
class LinkSharedFilesTask extends AbstractTask implements IsReleaseAware
{

    /**
     * Linked folders parameter name
     */
    const LINKED_FOLDERS = 'linked_folders';
    /**
     * Linking strategy parameter name
     */
    const LINKED_STRATEGY = 'linking_strategy';

    /**
     * Absolute linked strategy
     */
    const ABSOLUTE_LINKING = 'absolute';
    /**
     * Relative linked strategy
     */
    const RELATIVE_LINKING = 'relative';

    /**
     * @var array
     */
    private static $linkingStrategies = array(
        self::ABSOLUTE_LINKING,
        self::RELATIVE_LINKING
    );

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
        $linkedFiles = $this->getParameter('linked_files', array());
        $linkedFolders = $this->getParameter(self::LINKED_FOLDERS, array());
        $linkingStrategy = $this->getParameter(self::LINKED_STRATEGY, self::ABSOLUTE_LINKING);

        $linkedEntities = array_merge($linkedFiles, $linkedFolders);

        if (empty($linkedFiles) && empty($linkedFolders)) {
            throw new SkipException('No files and folders configured for sym-linking.');
        }

        $sharedFolderName = $this->getParameter('shared', 'shared');
        $sharedFolderPath = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $sharedFolderName;
        $releasesDirectory = $this->getConfig()->release('directory', 'releases');
        $releasesDirectoryPath = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $releasesDirectory;

        $currentCopy = $releasesDirectoryPath . '/' . $this->getConfig()->getReleaseId();
        $relativeDiffPath = str_replace($this->getConfig()->deployment('to'), '', $currentCopy) . '/';

        foreach ($linkedEntities as $ePath) {
            list($entityPath, $strategy) = $this->getPath($ePath);
            $sharedEntityLinkedPath = "$sharedFolderPath/$entityPath";
            if ($strategy == self::RELATIVE_LINKING) {
                $parentFolderPath = dirname($entityPath);
                $relativePath = $parentFolderPath == '.' ? $relativeDiffPath : $relativeDiffPath . $parentFolderPath . '/';
                $sharedEntityLinkedPath = ltrim(
                        preg_replace('/(\w+\/)/', '../', $relativePath),
                        '/'
                    ) . $sharedFolderName . '/' . $entityPath;
            }
            $command = "ln -nfs $sharedEntityLinkedPath $currentCopy/$entityPath";
            $this->runCommandRemote($command);
        }

        return true;
    }

    /**
     * @param array|string $linkedEntity
     *
     * @return array [$path, $strategy]
     */
    private function getPath($linkedEntity)
    {
        $linkingStrategy = $this->getParameter(self::LINKED_STRATEGY, self::ABSOLUTE_LINKING);
        if (is_array($linkedEntity)) {
            list($path, $strategy) = each($linkedEntity);
            if (!in_array($strategy, self::$linkingStrategies)) {
                $strategy = $linkingStrategy;
            }
        } else {
            $strategy = $linkingStrategy;
            $path = $linkedEntity;
        }

        return [$path, $strategy];
    }
}
