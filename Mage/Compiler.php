<?php

/**
 * Class Mage_Compiler
 *
 * Compiles the library into a .phar file
 *
 * @author Ismael Ambrosi<ismaambrosi@gmail.com>
 */
class Mage_Compiler
{

    /**
     * Compiles the library
     *
     * @param string $file
     */
    public function compile($file = 'mage.phar')
    {

        if (file_exists($file)) {
            unlink($file);
        }

        $phar = new Phar($file, 0, 'mage.phar');
        $phar->setSignatureAlgorithm(Phar::SHA1);

        $phar->startBuffering();

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__), RecursiveIteratorIterator::CHILD_FIRST);
        /** @var $path SplFileInfo */
        foreach ($iterator as $path) {
            if ($path->isFile()) {
                $phar->addFromString(str_replace(dirname(__DIR__).'/', '', $path->getPathname()), file_get_contents($path));
            }
        }

        $phar->addFromString('mage', str_replace(
            '$baseDir = dirname(dirname(__FILE__));',
            '$baseDir = __DIR__;',
            file_get_contents(__DIR__.'/../bin/mage.php')
        ));

        $phar->setStub("#!/usr/bin/env php\n<?php Phar::mapPhar('mage.phar'); require 'phar://mage.phar/mage'; __HALT_COMPILER();");

        $phar->stopBuffering();

        unset($phar);
    }
}
