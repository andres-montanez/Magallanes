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
	 */
    public static function autoload($className)
    {
        $baseDir = dirname(dirname(__FILE__));
        $classFile = $baseDir . '/' . str_replace(array('_', '\\'), '/', $className . '.php');
        require_once $classFile;
    }

    /**
     * Checks if a Class can be loaded.
     * @param string $className
     * @return boolean
     */
    public static function isLoadable($className)
    {
        $baseDir = dirname(dirname(__FILE__));
        $classFile = $baseDir . '/' . str_replace(array('_', '\\'), '/', $className . '.php');
        return (file_exists($classFile) && is_readable($classFile));
    }

    /**
     * Loads a User's Tasks
     * @param string $taskName
     */
    public static function loadUserTask($taskName)
    {
        $classFile = '.mage/tasks/' . ucfirst($taskName) . '.php';
        require_once $classFile;
    }

}