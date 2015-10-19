<?php

namespace Mage\Task\Newcraft\Bms;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;

/**
 * Class RemoveCurrentDirectoryTask
 * @package Mage\Task\Newcraft\Bms
 */
class EnsureLogsDirectoryTask extends AbstractTask implements IsReleaseAware
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'Ensure logs directory is in place [newcraft]';
    }

    /**
     * Removes any directory named current so it can be replaced with a symlink
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $directoryName = $this->getParameter('name', 'logs');

        $sudo = (bool) $this->getParameter('sudo', false) ? 'sudo ' : '';

        //create logs folder if not exists
        $this->runCommandRemote('test ! -d '.$directoryName.' && rm -f '.$directoryName.' && mkdir '.$directoryName);

        //set correct acls, no problem if already done.
        $users = $this->getParameter('users', []);
        $usersCommandString = implode(' -m ',array_map(function($v){ return 'u:'.$v.':rwX'; }, $users));

        $this->runCommandRemote($sudo.'chmod -R 664 '.$directoryName);
        $this->runCommandRemote($sudo.'setfacl -Rnm '.$usersCommandString.' '.$directoryName);
        $this->runCommandRemote($sudo.'setfacl -dRnm '.$usersCommandString.' '.$directoryName);

        //create symlink from app/logs
        $symLinkCommand = $this->getReleasesAwareCommand('ln -snf ../../../'.$directoryName.' app/logs');
        $this->runCommandRemote($symLinkCommand);

        return true;
    }
}
