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

use Phar;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Compiles the library into a .phar file
 *
 * @author Ismael Ambrosi<ismaambrosi@gmail.com>
 */
class Compiler
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
        /** @var \SplFileInfo $path */
        foreach ($iterator as $path) {
            if ($path->isFile()) {
                $phar->addFromString(str_replace(dirname(__DIR__) . '/', '', $path->getPathname()), file_get_contents($path));
            }
        }

        $binary = file(__DIR__ . '/../bin/mage');
        unset($binary[0]);
        $binary = implode(PHP_EOL, $binary);

        $phar->addFromString('mage', str_replace(
            '$baseDir = dirname(dirname(__FILE__));',
            '$baseDir = __DIR__;',
            $binary
        ));

        $phar->setStub("#!/usr/bin/env php\n<?php Phar::mapPhar('mage.phar'); require 'phar://mage.phar/mage'; __HALT_COMPILER();");

        $phar->stopBuffering();

        unset($phar);

        chmod($file, 0755);
    }
}
