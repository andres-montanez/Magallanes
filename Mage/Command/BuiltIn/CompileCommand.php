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
use Mage\Compiler;

use Exception;

/**
 * Command for Compile Magallanes into a PHAR executable
 *
 * @author Ismael Ambrosi<ismaambrosi@gmail.com>
 */
class CompileCommand extends AbstractCommand
{
    /**
     * @see \Mage\Compile::compile()
     */
    public function run ()
    {
        Console::output('Compiling <dark_gray>Magallanes</dark_gray>... ', 1, 0);

        $compiler = new Compiler;
        $compiler->compile();

        Console::output('Mage compiled successfully');
    }
}
