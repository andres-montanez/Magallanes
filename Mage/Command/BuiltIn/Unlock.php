<?php
class Mage_Command_BuiltIn_Unlock
    extends Mage_Command_CommandAbstract
    implements Mage_Command_RequiresEnvironment
{
    public function run()
    {
        $lockFile = '.mage/' . $this->getConfig()->getEnvironment() . '.lock';
        if (file_exists($lockFile)) {
            @unlink($lockFile);
        }

        Mage_Console::output('Unlocked deployment to <light_purple>' . $this->getConfig()->getEnvironment() . '</light_purple> environment', 1, 2);
    }

}