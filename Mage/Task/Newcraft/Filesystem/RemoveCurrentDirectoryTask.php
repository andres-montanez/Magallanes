<?php

namespace Mage\Task\Newcraft\Filesystem;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;

/**
 * Class RemoveCurrentDirectoryTask
 * @package Mage\Task\Newcraft\Filesystem
 */
class RemoveCurrentDirectoryTask extends AbstractTask implements IsReleaseAware
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'Remove existing current folder if needed [newcraft]';
    }

    /**
     * Removes any directory named current so it can be replaced with a symlink
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $this->runCommandRemote('test ! -h current && rm -rf current');
        return true;
    }
}
