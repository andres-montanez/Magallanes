<?php
/**
 * Class Mage_Command_BuiltIn_Compile
 *
 * @author Ismael Ambrosi<ismaambrosi@gmail.com>
 */
class Mage_Command_BuiltIn_Compile
    extends Mage_Command_CommandAbstract
{
    /**
     * @see Mage_Compile::compile()
     */
    public function run ()
    {
        Mage_Console::output('Compiling <dark_gray>Magallanes</dark_gray>... ', 1, 0);

        $compiler = new Mage_Compiler();
        $compiler->compile();

        Mage_Console::output('Mage compiled successfully');
    }
}
