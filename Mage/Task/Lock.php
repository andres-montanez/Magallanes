<?php
class Mage_Task_Lock
{
    private $_config = null;

    public function run(Mage_Config $config, $unlock = false)
    {
        $this->_config = $config;

        if ($config->getEnvironmentName() == '') {
            Mage_Console::output('<red>You must specify an environment</red>', 0, 2);
            return;
        }

        $lockFile = '.mage/' . $config->getEnvironmentName() . '.lock';
        if (file_exists($lockFile)) {
            @unlink($lockFile);
        }

        Mage_Console::output('Unlocked deployment to <light_purple>' . $config->getEnvironmentName() . '</light_purple> environment', 1, 2);
    }

}