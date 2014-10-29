<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Symfony2;

use Mage\Task\BuiltIn\Symfony2\SymfonyAbstractTask;

/**
 * Task for Installing Assets
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class AssetsInstallTask extends SymfonyAbstractTask
{
    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        return 'Symfony v2 - Assets Install [built-in]';
    }

    /**
     * Installs Assets
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        // Options
        $target = $this->getParameter('target', 'web');
        $symlink = $this->getParameter('symlink', false);
        $relative = $this->getParameter('relative', false);
        $env = $this->getParameter('env', 'dev');

        if ($relative) {
            $symlink = true;
        }

        $command = $this->getAppPath() . ' assets:install ' . ($symlink ? '--symlink' : '') . ' ' . ($relative ? '--relative' : '') . ' --env=' . $env . ' ' . $target;
        $result = $this->runCommand($command);

        return $result;
    }
}
