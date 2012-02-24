<?php
class Mage_Task_Init
{
    public function run()
    {
        $configDir = '.mage';
        
        Mage_Console::output('Initiating managing process for application with <dark_gray>Magallanes</dark_gray>');
        
        // Check if there is already a config dir
        if (file_exists($configDir)) {
            Mage_Console::output('<light_red>Error!!</light_red> Already exists <dark_gray>.mage</dark_gray> directory.', 1, 2);
        } else {
            $results = array();
            $results[] = mkdir($configDir);
            $results[] = mkdir($configDir . '/logs');
            $results[] = mkdir($configDir . '/tasks');
            $results[] = mkdir($configDir . '/config');
            $results[] = mkdir($configDir . '/config/environment');
            $results[] = file_put_contents($configDir . '/config/general.yml', '#global settings' . PHP_EOL . PHP_EOL);
            $results[] = file_put_contents($configDir . '/config/scm.yml', '#scm settings' . PHP_EOL . PHP_EOL);
            
            if (!in_array(false, $results)) {
                Mage_Console::output('<light_green>Success!!</light_green> The configuration for <dark_gray>Magallanes</dark_gray> has been generated at <blue>.mage</blue> directory.');
                Mage_Console::output('<dark_gray>Please!! Review and adjust the configuration.</dark_gray>', 2, 2);
            } else {
                Mage_Console::output('<light_red>Error!!</light_red> Unable to generate the configuration.', 1, 2);
            }
        }
    }
}