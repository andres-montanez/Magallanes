<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage;

/**
 * Magallanes custom Autoload for BuiltIn and Userspace Commands and Tasks.
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class Autoload
{
    /**
     * Autoload a Class by it's Class Name
     * @param string $className
     * @return boolean
     */
    public function autoLoad($className)
    {
        $className = ltrim($className, '/');
        $postfix = '/' . str_replace(array('_', '\\'), '/', $className . '.php');

        // Change BaseDir according to Namespace
        if (strpos($className, 'Task\\') === 0) {
            $baseDir = getcwd() . '/.mage/tasks';
            $postfix = substr($postfix, 5);
        } elseif (strpos($className, 'Command\\') === 0) {
            $baseDir = getcwd() . '/.mage/commands';
            $postfix = substr($postfix, 8);
        } else {
            $baseDir = dirname(dirname(__FILE__));
        }

        //Try to load a normal Mage class (or Task). Think that Mage component is compiled to .phar
        $classFileWithinPhar = $baseDir . $postfix;
        if ($this->isReadable($classFileWithinPhar)) {
            /** @noinspection PhpIncludeInspection */
            require_once $classFileWithinPhar;
            return true;
        }

        //Try to load a custom Task or Class. Notice that the path is absolute to CWD
        $classFileOutsidePhar = getcwd() . '/.mage/tasks' . $postfix;
        if ($this->isReadable($classFileOutsidePhar)) {
            /** @noinspection PhpIncludeInspection */
            require_once $classFileOutsidePhar;
            return true;
        }

        return false;
    }

    /**
     * Checks if a file can be read.
     * @param string $filePath
     * @return boolean
     */
    public function isReadable($filePath)
    {
        return is_readable($filePath);
    }
}
