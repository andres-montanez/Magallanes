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

/**
 * Initializes a Magallanes Configuration into a Proyect
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class InitCommand extends AbstractCommand
{

    /**
     * Command for Initalize a new Configuration Proyect
     * @see \Mage\Command\AbstractCommand::run()
     */
    public function run()
    {
        $configDir = '.mage';

        Console::output('Initiating managing process for application with <dark_gray>Magallanes</dark_gray>');

        // Check if there is already a config dir
        if (file_exists($configDir)) {
            Console::output('<light_red>Error!!</light_red> Already exists <dark_gray>.mage</dark_gray> directory.', 1, 2);
        } else {
            $results = array();
            $results[] = mkdir($configDir);
            $results[] = mkdir($configDir . '/logs');
            $results[] = file_put_contents($configDir . '/logs/.gitignore', "*\n!.gitignore");
            $results[] = mkdir($configDir . '/tasks');
            $results[] = touch($configDir . '/tasks/.gitignore');
            $results[] = mkdir($configDir . '/config');
            $results[] = mkdir($configDir . '/config/environment');
            $results[] = touch($configDir . '/config/environment/.gitignore');
            $results[] = file_put_contents($configDir . '/config/general.yml', $this->getGeneralConfig());

            if (!in_array(false, $results)) {
                Console::output('<light_green>Success!!</light_green> The configuration for <dark_gray>Magallanes</dark_gray> has been generated at <blue>.mage</blue> directory.');
                Console::output('<dark_gray>Please!! Review and adjust the configuration.</dark_gray>', 2, 2);
            } else {
                Console::output('<light_red>Error!!</light_red> Unable to generate the configuration.', 1, 2);
            }
        }
    }

    /**
     * Returns the Global Configuration
     * @return string
     */
    protected function getGeneralConfig()
    {
        // Assemble Global Settings
        $projectName = $this->getConfig()->getParameter('name', '');
        $notificationEmail = $this->getConfig()->getParameter('email', '');
        $notificationEnabled = ($notificationEmail != '') ? 'true' : 'false';

        $globalSettings = str_replace(
            array(
                '%projectName%',
                '%notificationEmail%',
                '%notificationEnabled%',
                '%loggingEnabled%',
                '%maxlogs%',
                '%ssh_needs_tty%',
            ),
            array(
                $projectName,
                $notificationEmail,
                $notificationEnabled,
                'true',
                30,
                'false'
            ),
            $this->getGeneralConfigTemplate()
        );

        return $globalSettings;
    }

    /**
     * Returns the YAML Template for the Global Configuration
     * @return string
     */
    protected function getGeneralConfigTemplate()
    {
        $template = '# global settings' . PHP_EOL
                  . 'name: %projectName%' . PHP_EOL
                  . 'email: %notificationEmail%' . PHP_EOL
                  . 'notifications: %notificationEnabled%' . PHP_EOL
                  . 'logging: %loggingEnabled%' . PHP_EOL
                  . 'maxlogs: %maxlogs%' . PHP_EOL
                  . 'ssh_needs_tty: %ssh_needs_tty%' . PHP_EOL;

        return $template;
    }
}
