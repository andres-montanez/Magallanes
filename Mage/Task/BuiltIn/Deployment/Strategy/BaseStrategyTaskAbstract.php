<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Deployment\Strategy;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;

/**
 * Abstract Base task to concentrate common code for Deployment Tasks
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
abstract class BaseStrategyTaskAbstract extends AbstractTask implements IsReleaseAware
{
    /**
     * Checks if there is an override underway
     *
     * @return bool
     */
    protected function checkOverrideRelease()
    {
        $overrideRelease = $this->getParameter('overrideRelease', false);
        $symlink = $this->getConfig()->release('symlink', 'current');

        if ($overrideRelease === true) {
            $releaseToOverride = false;
            $resultFetch = $this->runCommandRemote('ls -ld ' . $symlink . ' | cut -d"/" -f2', $releaseToOverride);
            if ($resultFetch && is_numeric($releaseToOverride)) {
                $this->getConfig()->setReleaseId($releaseToOverride);
            }
        }

        return $overrideRelease;
    }

    /**
     * Gathers the files to exclude
     *
     * @return array
     */
    protected function getExcludes()
    {
        $excludes = array(
            '.git',
            '.svn',
            '.mage',
            '.gitignore',
            '.gitkeep',
            'nohup.out'
        );

        // Look for User Excludes
        $userExcludes = $this->getConfig()->deployment('excludes', array());

        return array_merge($excludes, $userExcludes);
    }
}
