<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Command\BuiltIn;

use Mage\Command\AbstractCommand;
use Mage\Console;

use Phar;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

use Exception;

/**
 * Command for Compile Magallanes into a PHAR executable
 *
 * @author Ismael Ambrosi<ismaambrosi@gmail.com>
 */
class CompileCommand extends AbstractCommand
{
    /**
     * Compiles Magallanes into a PHAR executable
     */
    public function run ()
    {
    	if (ini_get('phar.readonly')) {
    		Console::output('The <purple>php.ini</purple> variable <light_red>phar.readonly</light_red> must be enabled.', 1, 2);
    		return;
    	}

        Console::output('Compiling <dark_gray>Magallanes</dark_gray>... ', 1, 0);
        $file = 'mage.phar';

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
            file_get_contents(__DIR__.'/../bin/mage')
        ));

        $phar->setStub("#!/usr/bin/env php\n<?php Phar::mapPhar('mage.phar'); require 'phar://mage.phar/mage'; __HALT_COMPILER();");

        $phar->stopBuffering();

        unset($phar);

        chmod($file, 0755);

        Console::output('Mage compiled successfully');
    }
}
