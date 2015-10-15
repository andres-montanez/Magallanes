<?php

namespace Mage\Task\Newcraft\Bms;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;

/**
 * Class RemoveCurrentDirectoryTask
 * @package Mage\Task\BuiltIn\Filesystem
 */
class CreateLogsDirectoryTask extends AbstractTask implements IsReleaseAware
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'Creating logs folder if needed [newcraft]';
    }

    /**
     * Removes any directory named current so it can be replaced with a symlink
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $directoryName = $this->getParameter('name', 'logs');

        $users = $this->getParameter('users', []);
        $usersCommandString = implode(' -m ',array_map(function($v){ return 'u:'.$v.':rwX'; }, $users));

        $this->runCommandRemote('test ! -d '.$directoryName.' && rm -f '.$directoryName.' && mkdir '.$directoryName);
        $this->runCommandRemote('setfacl -Rnm '.$usersCommandString.' '.$directoryName);
        $this->runCommandRemote('setfacl -dRnm '.$usersCommandString.' '.$directoryName);
        return true;
    }
}
