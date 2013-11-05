<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Mage_Command_BuiltIn_List
    extends Mage_Command_CommandAbstract
{
    public function run()
    {
        $subCommand = $this->getConfig()->getArgument(1);

        try {
            switch ($subCommand) {
                case 'environments':
                    $this->_environment();
                    break;
            }
        } catch (Exception $e) {
            Mage_Console::output('<red>' . $e->getMessage() . '</red>', 1, 2);
        }
    }

    private function _environment()
    {
    	$environments = array();
        $content = scandir('.mage/config/environment/');
        foreach ($content as $file) {
            if (strpos($file, '.yml') !== false) {
            	$environments[] = str_replace('.yml', '', $file);
            }
        }
        sort($environments);

        if (count($environments) > 0) {
        	Mage_Console::output('<dark_gray>These are your configured environments:</dark_gray>', 1, 1);
        	foreach ($environments as $environment) {
        		Mage_Console::output('* <light_red>' . $environment . '</light_red>', 2, 1);
        	}
        	Mage_Console::output('', 1, 1);

        } else {
        	Mage_Console::output('<dark_gray>You don\'t have any environment configured.</dark_gray>', 1, 2);
        }
    }
}