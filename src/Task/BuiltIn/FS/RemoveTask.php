<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\FS;

use Symfony\Component\Process\Process;
use Exception;

/**
 * File System Task - Remove a File
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class RemoveTask extends AbstractFileTask
{
    public function getName()
    {
        return 'fs/remove';
    }

    public function getDescription()
    {
        try {
            return sprintf('[FS] Remove %s"%s"', $this->getRecursive(), $this->getFile('file'));
        } catch (Exception $exception) {
            return '[FS] Remove [missing parameters]';
        }
    }

    public function execute()
    {
        $file = $this->getFile('file');

        $cmd = sprintf('rm %s%s', $this->getRecursive(), $file);

        /** @var Process $process */
        $process = $this->runtime->runCommand($cmd);

        return $process->isSuccessful();
    }

    protected function getParameters()
    {
        return ['file'];
    }

    protected function getRecursive()
    {
        $options = $this->getOptions();
        if (isset($options['recursive']) && boolval($options['recursive'])) {
            return '-fr ';
        }

        return '';
    }
}
