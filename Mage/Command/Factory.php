<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Command;

use Mage\Config;
use Mage\Autoload;

use Exception;

/**
 * Loads a Magallanes Command.
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class Factory
{
    /**
     * Gets an instance of a Command.
     *
     * @param string $commandName
     * @param Config $config
     * @return AbstractCommand
     * @throws Exception
     */
    public static function get($commandName, Config $config)
    {
        $instance = null;
        $commandName = ucwords(str_replace('-', ' ', $commandName));
        $commandName = str_replace(' ', '', $commandName);

        $commandName = str_replace(' ', '_', ucwords(str_replace('/', ' ', $commandName)));
        $className = 'Mage\\Command\\BuiltIn\\' . $commandName . 'Command';
        if (Autoload::isLoadable($className)) {
            $instance = new $className;
            assert($instance instanceOf AbstractCommand);
            $instance->setConfig($config);
        } else {
            throw new Exception('Command not found.');
        }

        if(!($instance instanceOf AbstractCommand)) {
            throw new Exception('The command ' . $commandName . ' must be an instance of Mage\Command\AbstractCommand.');
        }

        return $instance;
    }
}