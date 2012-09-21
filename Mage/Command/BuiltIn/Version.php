<?php
class Mage_Command_BuiltIn_Version
    extends Mage_Command_CommandAbstract
{
    public function run()
    {
        Mage_Console::output('Running <blue>Magallanes</blue> version <dark_gray>' . MAGALLANES_VERSION .'</dark_gray>', 0, 2);
    }

}