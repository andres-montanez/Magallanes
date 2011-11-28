<?php
class Mage_Task_Add
{
    public function environment($environmentName)
    {
        $environmentName = strtolower($environmentName);
        $environmentConfigFile = '.mage/config/environment/' . $environmentName . '.yaml';
        
        Mage_Console::output('Adding new environment: <dark_gray>' . $environmentName . '</dark_gray>');
        
        // Check if there is already an environment with the same name
        if (file_exists($environmentConfigFile)) {
            Mage_Console::output('<light_red>Error!!</light_red> Already exists an environment called <dark_gray>' . $environmentName . '</dark_gray>', 1, 2);
        } else {
            $baseConfig = '#' . $environmentName . PHP_EOL
                        . 'deployment:'
                        . '  user: dummy' . PHP_EOL
                        . '  from: ./' . PHP_EOL
                        . '  to: /var/www/vhosts/example.com/www' . PHP_EOL
                        . '  excludes:' . PHP_EOL
                        . 'hosts:' . PHP_EOL
                        . 'tasks:'
                        . '  pre-deploy:' . PHP_EOL
                        . '  on-deploy:' . PHP_EOL
                        . '    - deployment/rsync' . PHP_EOL
                        . '  post-deploy:' . PHP_EOL;
            $result = file_put_contents($environmentConfigFile, $baseConfig);
            
            if ($result) {
                Mage_Console::output('<light_green>Success!!</light_green> Environment config file for <dark_gray>' . $environmentName . '</dark_gray> created successfully at <blue>' . $environmentConfigFile . '</blue>');
                Mage_Console::output('<dark_gray>So please! Review and adjust its configuration.</dark_gray>', 2, 2);
            } else {
                Mage_Console::output('<light_red>Error!!</light_red> Unable to create config file for environment called <dark_gray>' . $environmentName . '</dark_gray>', 1, 2);
            }
        }
    }
}