<?php
namespace Mage\Task\BuiltIn\Filesystem;

use Mage\Task\AbstractTask;
use Mage\Task\SkipException;

/**
 * Task for setting permissions on given paths.
 *
 * @author Jérémy Huet <jeremy.huet@gmail.com>
 */
class PermissionsTask extends AbstractTask
{
    /**
     * Paths to change of permissions separated by PATH_SEPARATOR.
     *
     * @var string
     */
    private $paths;

    /**
     * If set to true, will check existance of given paths on remote host and
     * throw SkipException if at least one does not exist.
     *
     * @var boolean
     */
    private $checkPathsExist = true;

    /**
     * Owner to set for the given paths (ex : "www-data")
     *
     * @var string
     */
    private $owner;

    /**
     * Group to set for the given paths (ex : "www-data")
     *
     * @var string
     */
    private $group;

    /**
     * Rights to set for the given paths (ex: "755")
     *
     * @var string
     */
    private $rights;

    /**
     * Initialize parameters.
     *
     * @throws SkipException
     */
    public function init()
    {
        parent::init();

        if (! is_null($this->getParameter('checkPathsExist'))) {
            $this->setCheckPathsExist($this->getParameter('checkPathsExist'));
        }

        if (! $this->getParameter('paths')) {
            throw new SkipException('Param paths is mandatory');
        }
        $this->setPaths(explode(PATH_SEPARATOR, $this->getParameter('paths', '')));

        if (! is_null($this->getParameter('owner'))) {
            $this->setOwner($this->getParameter('owner'));
        }

        if (! is_null($this->getParameter('group'))) {
            $this->setGroup($this->getParameter('group'));
        }

        if (! is_null($this->getParameter('rights'))) {
            $this->setRights($this->getParameter('rights'));
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "Change rights / owner / group for paths : " . $this->getPathsForCmd() . " [built-in]";
    }

    /**
     * @return boolean
     */
    public function run()
    {
        $command = '';

        if ($this->paths && $this->owner) {
            $command .= 'chown -R ' . $this->owner . ' ' . $this->getPathsForCmd() . ';';
        }

        if ($this->paths && $this->group) {
            $command .= 'chgrp -R ' . $this->group . ' ' . $this->getPathsForCmd() . ';';
        }

        if ($this->paths && $this->rights) {
            $command .= 'chmod -R ' . $this->rights . ' ' . $this->getPathsForCmd() . ';';
        }

        $result = $this->runCommandRemote($command);

        return $result;
    }

    /**
     * Transforms paths array to a string separated by 1 space in order to use
     * it in a command line.
     *
     * @return string
     */
    protected function getPathsForCmd($paths = null)
    {
        if (is_null($paths)) {
            $paths = $this->paths;
        }

        return implode(' ', $paths);
    }

    /**
     * Set paths. Will check if they exist on remote host depending on
     * checkPathsExist flag.
     *
     * @param array $paths
     * @return PermissionsTask
     * @throws SkipException
     */
    protected function setPaths(array $paths)
    {
        if ($this->checkPathsExist == true) {
            $commands = array();
            foreach ($paths as $path) {
                $commands[] = '(([ -f ' . $path . ' ]) || ([ -d ' . $path . ' ]))';
            }

            $command = implode(' && ', $commands);
            if (! $this->runCommandRemote($command)) {
                throw new SkipException('Make sure all paths given exist on remote host : ' . $this->getPathsForCmd($paths));
            }
        }

        $this->paths = $paths;

        return $this;
    }

    /**
     * @return string
     */
    protected function getPaths()
    {
        return $this->paths;
    }

    /**
     * Set checkPathsExist.
     *
     * @param boolean $checkPathsExist
     * @return PermissionsTask
     */
    protected function setCheckPathsExist($checkPathsExist)
    {
        $this->checkPathsExist = $checkPathsExist;

        return $this;
    }

    /**
     * @return boolean
     */
    protected function getCheckPathsExist()
    {
        return $this->checkPathsExist;
    }

    /**
     * Set owner.
     *
     * @todo check existance of $owner on remote, might be different ways depending on OS.
     *
     * @param string $owner
     * @return PermissionsTask
     */
    protected function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return string
     */
    protected function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set group.
     *
     * @todo check existance of $group on remote, might be different ways depending on OS.
     *
     * @param string $group
     * @return PermissionsTask
     */
    protected function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return string
     */
    protected function getGroup()
    {
        return $this->group;
    }

    /**
     * Set rights.
     *
     * @todo better way to check if $rights is in a correct format.
     *
     * @param string $rights
     * @return PermissionsTask
     */
    protected function setRights($rights)
    {
        if (strlen($rights) != 3 || !is_numeric($rights) || $rights > 777) {
            throw new SkipException('Make sure the rights "' . $rights . '" are in a correct format.');
        }

        $this->rights = $rights;

        return $this;
    }

    /**
     * @return string
     */
    protected function getRights()
    {
        return $this->rights;
    }
}