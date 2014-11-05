<?php
namespace Mage\Task\BuiltIn\Filesystem;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;
use Mage\Task\SkipException;
use Symfony\Component\Filesystem\Filesystem;

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
    const LINKED_FILES = 'linked_files';
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
        $linkedEntities = array_merge(
            $this->getParameter(self::LINKED_FILES, array()),
            $this->getParameter(self::LINKED_FOLDERS, array())
        );

        if (empty($linkedEntities)) {
            throw new SkipException('No files and folders configured for sym-linking.');
        }

        $remoteDirectory = rtrim($this->getConfig()->deployment('to'), '/') . '/';
        $sharedFolderPath = $remoteDirectory . $this->getParameter('shared', 'shared');
        $releasesDirectoryPath = $remoteDirectory . $this->getConfig()->release('directory', 'releases');
        $currentCopy = $releasesDirectoryPath . '/' . $this->getConfig()->getReleaseId();
        $fileSystem = new Filesystem();

        foreach ($linkedEntities as $ePath) {
            list($entityPath, $strategy) = $this->getPath($ePath);
            if ($strategy === self::RELATIVE_LINKING) {
                $dirName = dirname($currentCopy . '/' . $entityPath);
                $target = $fileSystem->makePathRelative($sharedFolderPath, $dirName) . $entityPath;
            } else {
                $target = $sharedFolderPath . '/' . $entityPath;
            }
            $command = 'mkdir -p ' . escapeshellarg(dirname($target));
            $this->runCommandRemote($command);
            $command = 'ln -nfs ' . escapeshellarg($target) . ' ' . escapeshellarg($currentCopy . '/' . $entityPath);
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
