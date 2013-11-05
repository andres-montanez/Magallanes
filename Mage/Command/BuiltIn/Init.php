<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Mage_Command_BuiltIn_Init
    extends Mage_Command_CommandAbstract
{
	protected $generalTemplate = <<<'YML'
# global settings
name: %projectName%
email: %notificationEmail%
notifications: %notificationEnabled%
logging: %loggingEnabled%
maxlogs: %maxlogs%
YML;

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
            $results[] = file_put_contents($configDir . '/logs/.gitignore', "*\n!.gitignore");
            $results[] = mkdir($configDir . '/tasks');
            $results[] = touch($configDir . '/tasks/.gitignore');
            $results[] = mkdir($configDir . '/config');
            $results[] = mkdir($configDir . '/config/environment');
            $results[] = touch($configDir . '/config/environment/.gitignore');
            $results[] = file_put_contents($configDir . '/config/general.yml', $this->getGeneralConfig());

            if (!in_array(false, $results)) {
                Mage_Console::output('<light_green>Success!!</light_green> The configuration for <dark_gray>Magallanes</dark_gray> has been generated at <blue>.mage</blue> directory.');
                Mage_Console::output('<dark_gray>Please!! Review and adjust the configuration.</dark_gray>', 2, 2);
            } else {
                Mage_Console::output('<light_red>Error!!</light_red> Unable to generate the configuration.', 1, 2);
            }
        }
    }

    protected function getGeneralConfig()
    {
    	// Assamble Global Settings
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
			),
			array(
				$projectName,
				$notificationEmail,
				$notificationEnabled,
				'true',
				30
			),
			$this->generalTemplate
    	);

    	return $globalSettings;
    }
}