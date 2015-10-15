<?php

/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\Newcraft\Filesystem;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;

/**
 * Class RemoveCurrentDirectoryTask
 * @package Mage\Task\BuiltIn\Filesystem
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
