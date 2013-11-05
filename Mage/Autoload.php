<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Mage_Autoload
{
    public static function autoload($className)
    {
        $baseDir = dirname(dirname(__FILE__));
        $classFile = $baseDir . '/' . str_replace('_', '/', $className . '.php');
        require_once $classFile;
    }

    public static function isLoadable($className)
    {
        $baseDir = dirname(dirname(__FILE__));
        $classFile = $baseDir . '/' . str_replace('_', '/', $className . '.php');
        return (file_exists($classFile) && is_readable($classFile));
    }

    public static function loadUserTask($taskName)
    {
        $classFile = '.mage/tasks/' . ucfirst($taskName) . '.php';
        require_once $classFile;
    }
}