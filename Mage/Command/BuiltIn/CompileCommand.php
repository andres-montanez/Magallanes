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

/**
 * Command for Compile Magallanes into a PHAR executable
 *
 * @author Ismael Ambrosi<ismaambrosi@gmail.com>
 */
class CompileCommand extends AbstractCommand
{
    /**
     * @var Compiler
     */
    private $compiler;

    public function __construct(Compiler $compiler = null)
    {
        if ($compiler === null) {
            $compiler = new Compiler();
        }

        $this->compiler = $compiler;
    }

    /**
     * @see \Mage\Compile::compile()
     */
    public function run()
    {
        if (ini_get('phar.readonly')) {
            Console::output(
                'The <purple>php.ini</purple> variable <light_red>phar.readonly</light_red>'
                . ' must be <yellow>Off</yellow>.',
                1,
                2
            );

            return 200;
        }

        $this->compiler->compile();

        Console::output(
            '<light_purple>mage.phar</light_purple> compiled <light_green>successfully</light_green>',
            0,
            2
        );

        return 0;
    }
}
