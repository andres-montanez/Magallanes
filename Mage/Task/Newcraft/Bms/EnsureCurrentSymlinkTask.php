<?php

namespace Mage\Task\Newcraft\Bms;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;

/**
 * Class RemoveCurrentDirectoryTask
 * @package Mage\Task\Newcraft\Filesystem
 */
class EnsureCurrentSymlinkTask extends AbstractTask implements IsReleaseAware
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'Ensure current symlink can be created [newcraft]';
    }

    /**
     * Removes any directory named current so it can be replaced with a symlink
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $sudo = (bool) $this->getParameter('sudo', false) ? 'sudo ' : '';
        $this->runCommandRemote('test -h current && test -w current || '.$sudo.'rm -rf current');
        return true;
    }
}
