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
        $exitCode = 221;
        $subCommand = $this->getConfig()->getArgument(1);

        try {
            switch ($subCommand) {
                case 'environments':
                    $exitCode = $this->listEnvironments();
                    break;

                default:
                    throw new Exception('The Type of Elements to List is needed.');
                    break;
            }
        } catch (Exception $e) {
            Console::output('<red>' . $e->getMessage() . '</red>', 1, 2);
        }

        return $exitCode;
    }

    /**
     * Lists the Environments
     */
    protected function listEnvironments()
    {
        $exitCode = 220;
        $environments = array();
        $content = scandir(getcwd() . '/.mage/config/environment/');
        foreach ($content as $file) {
            if (strpos($file, '.yml') !== false) {
                $environments[] = str_replace('.yml', '', $file);
            }
        }
        sort($environments);

        if (count($environments) > 0) {
            Console::output('<bold>These are your configured environments:</bold>', 1, 1);
            foreach ($environments as $environment) {
                Console::output('* <light_red>' . $environment . '</light_red>', 2, 1);
            }
            Console::output('', 1, 1);
            $exitCode = 0;
        } else {
            Console::output('<bold>You don\'t have any environment configured.</bold>', 1, 2);
        }

        return $exitCode;
    }
}
