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

use Mage\Task\AbstractTask;

/**
 * Abstract Task for Symfony2
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
abstract class SymfonyAbstractTask extends AbstractTask
{
    /**
     * @return string
     */
    protected function getAppPath()
    {
        if ($this->getConfig()->general('symfony_version', '2') == '3') {
            $defaultAppPath = 'bin/console';
        } else {
            $defaultAppPath = 'app/console';
        }
        
        $path = '';
        $niceness = intval($this->getParameter('niceness', 0));
        if (!empty($niceness)) {
            $path .= 'nice -n ' . $niceness . ' ';
        }
        $path .= $this->getConfig()->general('symfony_app_path', $defaultAppPath);
        return $path;
    }
}
