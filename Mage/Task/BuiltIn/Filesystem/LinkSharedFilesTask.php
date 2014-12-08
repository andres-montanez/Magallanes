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

        foreach ($linkedEntities as $ePath) {
            list($entityPath, $strategy) = $this->getPath($ePath);
            if ($strategy === self::RELATIVE_LINKING) {
                $dirName = dirname($currentCopy . '/' . $entityPath);
                $target = $this->makePathRelative($sharedFolderPath, $dirName) . $entityPath;
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
     * Given an existing path, convert it to a path relative to a given starting path
     *
     * @param string $endPath Absolute path of target
     * @param string $startPath Absolute path where traversal begins
     *
     * @return string Path of target relative to starting path
     *
     * @author Fabien Potencier <fabien@symfony.com>
     * @see https://github.com/symfony/Filesystem/blob/v2.6.1/Filesystem.php#L332
     */
    private function makePathRelative($endPath, $startPath)
    {
        // Normalize separators on Windows
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $endPath = strtr($endPath, '\\', '/');
            $startPath = strtr($startPath, '\\', '/');
        }
        // Split the paths into arrays
        $startPathArr = explode('/', trim($startPath, '/'));
        $endPathArr = explode('/', trim($endPath, '/'));
        // Find for which directory the common path stops
        $index = 0;
        while (isset($startPathArr[$index]) && isset($endPathArr[$index]) && $startPathArr[$index] === $endPathArr[$index]) {
            $index++;
        }
        // Determine how deep the start path is relative to the common path (ie, "web/bundles" = 2 levels)
        $depth = count($startPathArr) - $index;
        // Repeated "../" for each level need to reach the common path
        $traverser = str_repeat('../', $depth);
        $endPathRemainder = implode('/', array_slice($endPathArr, $index));
        // Construct $endPath from traversing to the common path, then to the remaining $endPath
        $relativePath = $traverser . (strlen($endPathRemainder) > 0 ? $endPathRemainder . '/' : '');

        return (strlen($relativePath) === 0) ? './' : $relativePath;
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
