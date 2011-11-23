<?php
class Mage_Autoload
{
    public static function autoload($className)
    {
        $baseDir = dirname(dirname(__FILE__));
        $classFile = $baseDir . '/' . str_replace('_', '/', $className . '.php');
        require_once $classFile;
    }
}