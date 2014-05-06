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

use Exception;

/**
 * Adds elements to the Configuration.
 * Currently elements allowed to add:
 *   - environments
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class ListCommand extends AbstractCommand
{
    /**
     * Command for Listing Configuration Elements
     * @see \Mage\Command\AbstractCommand::run()
     * @throws Exception
     */
    public function run()
    {
        $subCommand = $this->getConfig()->getArgument(1);

        try {
            switch ($subCommand) {
                case 'environments':
                    $this->listEnvironments();
                    break;

                default;
                    throw new Exception('The Type of Elements to List is needed.');
                    break;
            }
        } catch (Exception $e) {
            Console::output('<red>' . $e->getMessage() . '</red>', 1, 2);
        }
    }

    /**
     * Lists the Environments
     */
    protected function listEnvironments()
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
            Console::output('<dark_gray>These are your configured environments:</dark_gray>', 1, 1);
            foreach ($environments as $environment) {
                Console::output('* <light_red>' . $environment . '</light_red>', 2, 1);
            }
            Console::output('', 1, 1);

        } else {
            Console::output('<dark_gray>You don\'t have any environment configured.</dark_gray>', 1, 2);
        }
    }
}
