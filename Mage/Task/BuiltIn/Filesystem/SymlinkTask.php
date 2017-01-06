<?php
namespace Mage\Task\BuiltIn\Filesystem;

use Mage\Task\AbstractTask;
use Mage\Config\RequiredConfigNotFoundException;

/**
 * Task for creating a symbolic link. Change will be done on local or
 * remote host depending on the stage of the deployment.
 *
 * Usage :
 *   pre-deploy:
 *     - filesystem/symlink: {link:/path/to/symlink, target: /path/to/target}
 *   on-deploy:
 *     - filesystem/symlink: {link:/path/to/symlink, target: /path/to/target}
 *
 * @author Jérémy Huet <jeremy.huet@gmail.com>
 */
class SymlinkTask extends AbstractTask
{
    /**
     * The name of the symlink including full path to it.
     *
     * If the stage is on local host you should give full paths. If on remote
     * you may give full or relative to the current release directory paths.
     *
     * @var string
     */
    private $link;

    /**
     * The target to wich the symlink should point to including full path to it.
     *
     * If the stage is on local host you should give full paths. If on remote
     * you may give full or relative to the current release directory paths.
     *
     * @var string
     */
    private $target;

    /**
     * Initialize parameters.
     *
     * @throws RequiredConfigNotFoundException
     */
    public function init()
    {
        parent::init();

        if (! $this->getParameter('target')) {
            throw new RequiredConfigNotFoundException('Missing required target link.');
        }

        $this->setTarget($this->getParameter('target'));
        if (!is_null($this->getParameter('link'))) {
            $this->setLink($this->getParameter('link'));
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "Creating symbolic link [built-in]";
    }

    /**
     * @return boolean
     */
    public function run()
    {
        $command = 'ln -fs ' . $this->getAbsolutPath($this->getTarget());
        if ($this->getLink()) {
            $command .= ' ' . $this->getAbsolutPath($this->getLink());
        }

        $result = $this->runCommand($command);

        return $result;
    }

    /**
     * @param string $path
     * @return string
     */
    public function getAbsolutPath($path)
    {
        // For release
        if ($this->getStage() != 'pre-deploy' && $path[0] != '/' && $this->getConfig()->deployment('to')) {
            $releasesDirectory = trim($this->getConfig()->release('directory', 'releases'), '/') . '/' . $this->getConfig()->getReleaseId();
            return rtrim($this->getConfig()->deployment('to'), '/') . '/' . $releasesDirectory . '/' . ltrim($path, '/');
        }

        return $path;
    }

    /**
     * Set link.
     *
     * @param string $link
     * @return SymlinkTask
     */
    protected function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return string
     */
    protected function getLink()
    {
        return $this->link;
    }

    /**
     * Set target.
     *
     * @param string $target
     * @return SymlinkTask
     */
    protected function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return string
     */
    protected function getTarget()
    {
        return $this->target;
    }
}
