<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task;

use Mage\Config;
use Mage\Autoload;

use Exception;

/**
 * Task Factory
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class Factory
{
    /**
     * Gets an instance of a Task.
     *
     * @param string|array $taskData
     * @param \Mage\Config $taskConfig
     * @param Config $taskConfig
     * @param boolean $inRollback
     * @param string $stage
     * @return \Mage\Task\AbstractTask
     * @throws \Exception|\Mage\Task\ErrorWithMessageException
     */
    public static function get($taskData, Config $taskConfig, $inRollback = false, $stage = null)
    {
        if (is_array($taskData)) {
            $taskName = $taskData['name'];
            $taskParameters = $taskData['parameters'];
        } else {
            $taskName = $taskData;
            $taskParameters = array();
        }

        $instance = null;
        $taskName = ucwords(str_replace('-', ' ', $taskName));
        $taskName = str_replace(' ', '', $taskName);

        if (strpos($taskName, '/') === false) {
            Autoload::loadUserTask($taskName);
            $className = 'Task\\' . ucfirst($taskName);

        } else {
            $taskName = str_replace(' ', '\\', ucwords(str_replace('/', ' ', $taskName)));
            $className = 'Mage\\Task\\BuiltIn\\' . $taskName . 'Task';
        }


        if (class_exists($className) || Autoload::isLoadable($className)) {
        	$instance = new $className($taskConfig, $inRollback, $stage, $taskParameters);
        } else {
        	throw new ErrorWithMessageException('The Task "' . $taskName . '" doesn\'t exists.');
        }

        if (!($instance instanceOf AbstractTask)) {
        	throw new Exception('The Task ' . $taskName . ' must be an instance of Mage\Task\AbstractTask.');
        }

        return $instance;
    }
}
