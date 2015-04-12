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
 *     - filesystem/permissions:
 *         paths:
 *             - /var/www/myapp/app/cache
 *             - /var/www/myapp/app/logs
 *         recursive: false
 *         checkPathsExist: true
 *         owner: www-data:www-data
 *         rights: 775
 *   on-deploy:
 *     - filesystem/permissions: {paths: app/cache:app/logs, recursive: false, checkPathsExist: true, owner: www-data, group: www-data, rights: 775}
 *
 * @author Jérémy Huet <jeremy.huet@gmail.com>
 */
class PermissionsTask extends AbstractTask
{
    /**
     * Paths to change of permissions in an array or a string separated by
     * PATH_SEPARATOR.
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
     * Owner to set for the given paths (ex : "www-data" or "www-data:www-data"
     * to set both owner and group at the same time)
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
     * Rights to set for the given paths (ex: "755" or "g+w")
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
        $this->setPaths(is_array($this->getParameter('paths')) ? $this->getParameter('paths') : explode(PATH_SEPARATOR, $this->getParameter('paths', '')));

        if (! is_null($owner = $this->getParameter('owner'))) {
            if (strpos($owner, ':') !== false) {
                $this->setOwner(array_shift(explode(':', $owner)));
                $this->setGroup(array_pop(explode(':', $owner)));
            } else {
                $this->setOwner($owner);
            }
        }

        if (! is_null($group = $this->getParameter('group'))) {
            $this->setGroup($group);
        }

        if (! is_null($rights = $this->getParameter('rights'))) {
            $this->setRights($rights);
        }

        if (! is_null($recursive = $this->getParameter('recursive'))) {
            $this->setRecursive($recursive);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "Changing rights / owner / group for given paths [built-in]";
    }

    /**
     * @return boolean
     */
    public function run()
    {
        $commands = array();

        if ($this->paths && $this->owner) {
            $commands []= 'chown '. $this->getOptionsForCmd() .' ' . $this->owner . ' ' . $this->getPathsForCmd();
        }

        if ($this->paths && $this->group) {
            $commands []= 'chgrp '. $this->getOptionsForCmd()  .' ' . $this->group . ' ' . $this->getPathsForCmd();
        }

        if ($this->paths && $this->rights) {
            $commands []= 'chmod '. $this->getOptionsForCmd()  .' ' . $this->rights . ' ' . $this->getPathsForCmd();
        }

        $result = $this->runCommand(implode(' && ', $commands));

        return $result;
    }

    /**
     * Returns the options for the commands to run. Only supports -R for now.
     *
     * @return string
     */
    protected function getOptionsForCmd()
    {
        $optionsForCmd = '';
        $options = array(
            'R' => $this->recursive
        );

        foreach ($options as $option => $apply) {
            if ($apply === true) {
                $optionsForCmd .= $option;
            }
        }

        return $optionsForCmd ? '-' . $optionsForCmd : '';
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
        if ($this->checkPathsExist === true) {
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
