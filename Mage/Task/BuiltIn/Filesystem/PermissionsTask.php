<?php
namespace Mage\Task\BuiltIn\Filesystem;

use Mage\Task\AbstractTask;
use Mage\Task\SkipException;

/**
 * Task for setting permissions on given paths. Change will be done on local or
 * remote host depending on the stage of the deployment.
 *
 * Usage :
 *   pre-deploy:
 *     - filesystem/permissions: {paths: /var/www/myapp/app/cache:/var/www/myapp/app/cache, recursive: false, checkPathsExist: true, owner: www-data, group: www-data, rights: 775}
 *   on-deploy:
 *     - filesystem/permissions: {paths: app/cache:app/logs, recursive: false, checkPathsExist: true, owner: www-data, group: www-data, rights: 775}
 *
 * @author Jérémy Huet <jeremy.huet@gmail.com>
 */
class PermissionsTask extends AbstractTask
{
    /**
     * Paths to change of permissions separated by PATH_SEPARATOR.
     *
     * If the stage is on local host you should give full paths. If on remote
     * you may give full or relative to the current release directory paths.
     *
     * @var string
     */
    private $paths;

    /**
     * If set to true, will check existance of given paths on the host and
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
     * If set to true, will recursively change permissions on given paths.
     *
     * @var string
     */
    private $recursive = false;

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

        if (! is_null($this->getParameter('recursive'))) {
            $this->setRecursive($this->getParameter('recursive'));
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
        $recursive = $this->recursive ? '-R' : '';

        if ($this->paths && $this->owner) {
            $command .= 'chown '. $recursive .' ' . $this->owner . ' ' . $this->getPathsForCmd() . ';';
        }

        if ($this->paths && $this->group) {
            $command .= 'chgrp '. $recursive .' ' . $this->group . ' ' . $this->getPathsForCmd() . ';';
        }

        if ($this->paths && $this->rights) {
            $command .= 'chmod '. $recursive .' ' . $this->rights . ' ' . $this->getPathsForCmd() . ';';
        }

        $result = $this->runCommand($command);

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
     * Set paths. Will check if they exist on the host depending on
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
            if (! $this->runCommand($command)) {
                throw new SkipException('Make sure all paths given exist on the host : ' . $this->getPathsForCmd($paths));
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
        $this->checkPathsExist = (bool) $checkPathsExist;

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
     * @todo check existance of $owner on host, might be different ways depending on OS.
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
     * @todo check existance of $group on host, might be different ways depending on OS.
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
     * @todo check if $rights is in a correct format.
     *
     * @param string $rights
     * @return PermissionsTask
     */
    protected function setRights($rights)
    {
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

    /**
     * Set recursive.
     *
     * @param boolean $recursive
     * @return PermissionsTask
     */
    protected function setRecursive($recursive)
    {
        $this->recursive = (bool) $recursive;

        return $this;
    }

    /**
     * @return boolean
     */
    protected function getRecursive()
    {
        return $this->recursive;
    }
}
