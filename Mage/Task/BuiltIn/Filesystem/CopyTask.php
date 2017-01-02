<?php
namespace Mage\Task\BuiltIn\Filesystem;

use Mage\Task\AbstractTask;
use Mage\Config\RequiredConfigNotFoundException;

/**
 * Task for copying files. Change will be done on local or
 * remote host depending on the stage of the deployment.
 *
 * Usage :
 *   pre-deploy:
 *     - filesystem/copy: {source:/path/to/file, destination:/path/to/target}
 *   on-deploy:
 *     - filesystem/copy: {source:/path/to/file, destination:/path/to/target}
 *
 * @author Noah-Jerome Lotzer <noah.lotzer@gmail.com>
 */
class CopyTask extends AbstractTask
{
    /**
     * The source of the file/folder including full path to it.
     *
     * If the stage is on local host you should give full paths. If on remote
     * you may give full or relative to the current release directory paths.
     *
     * @var string
     */
    private $source;

    /**
     * The destination to which the file/folder should copied to including full path to it.
     *
     * If the stage is on local host you should give full paths. If on remote
     * you may give full or relative to the current release directory paths.
     *
     * @var string
     */
    private $destination;

    /**
     * Initialize parameters.
     *
     * @throws RequiredConfigNotFoundException
     */
    public function init()
    {
        parent::init();

        if (!$this->getParameter('destination')) {
            throw new RequiredConfigNotFoundException('Missing required source.');
        }

        $this->setDestination($this->getParameter('destination'));

        if (!$this->getParameter('source')) {
            throw new RequiredConfigNotFoundException('Missing required destination.');
        }

        $this->setSource($this->getParameter('source'));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "Copying files [built-in]";
    }

    /**
     * @return boolean
     */
    public function run()
    {
        $command = 'cp -r ' . $this->getAbsolutPath($this->getSource()) .
            ' ' . $this->getAbsolutPath($this->getDestination());

        $result = $this->runCommand($command);

        return $result;
    }

    /**
     * @param string $path
     * @return string
     */
    public function getAbsolutPath($path)
    {
        if ($this->getStage() != 'pre-deploy' && $path[0] != '/' && $this->getConfig()->deployment('to')) {
            $releasesDirectory = trim($this->getConfig()->release('directory', 'releases'), '/') . '/' . $this->getConfig()->getReleaseId();
            return rtrim($this->getConfig()->deployment('to'), '/') . '/' . $releasesDirectory . '/' . ltrim($path, '/');
        }

        return $path;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param string $destination
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

}
