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
use Mage\Task\AbstractTask;
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
     * @param boolean $inRollback
     * @param string $stage
     * @return \Mage\Task\AbstractTask
     * @throws \Exception
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

        $patterns = [];

        if (is_array($taskConfig->general('taskPatterns'))) {
            $patterns = $taskConfig->general('taskPatterns');
        }

        $patterns[] = 'Task\\%s';
        $patterns[] = 'Mage\\Task\\BuiltIn\\%sTask';

        $className = null;

        $taskClass = trim($taskName, '/\\');
        $taskClass = str_replace(' ', '\\', ucwords(str_replace('/', ' ', $taskClass)));
        $taskClass = str_replace(' ', '', ucwords(str_replace('-', ' ', $taskClass)));

        foreach ($patterns as $pattern) {
            $possibleClass = sprintf($pattern, $taskClass);

            if (class_exists($possibleClass)) {
                $className = $possibleClass;
                break;
            }
        }

        if (!$className) {
            throw new Exception('Task "' . $taskName . '" not found.');
        }

        $instance = new $className($taskConfig, $inRollback, $stage, $taskParameters);

        if (!($instance instanceof AbstractTask)) {
            throw new Exception('The Task ' . $taskName . ' must be an instance of Mage\Task\AbstractTask.');
        }

        return $instance;
    }
}
