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

/**
 * Class RemoveCurrentDirectoryTask
 * @package Mage\Task\BuiltIn\Filesystem
 */
class RemoveCurrentDirectoryTask extends AbstractTask
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'Removes existing current folder [newcraft]';
    }

    /**
     * Removes any directory named current and replaces it with a symlink
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $isDir = $this->runCommandRemote('cd ../../ && test -d current && test -h current && echo "y" || echo "n"', $output);
        if( $isDir && $output == 'n') {
            if($this->runCommandRemote('cd ../../ && rm -rf current', $output)) {
                if($this->runCommandRemote('cd ../../ && test -d current && echo "y" || echo "n"', $output) && $output = 'n') {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}
