<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Mage_Task_Factory
{
    /**
     *
     *
     * @param string|array $taskData
     * @param boolean $inRollback
     * @return Mage_Task_TaskAbstract
     */
    public static function get($taskData, Mage_Config $taskConfig, $inRollback = false, $stage = null)
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
            Mage_Autoload::loadUserTask($taskName);
            $className = 'Task_' . ucfirst($taskName);
            $instance = new $className($taskConfig, $inRollback, $stage, $taskParameters);

        } else {
            $taskName = str_replace(' ', '_', ucwords(str_replace('/', ' ', $taskName)));
            $className = 'Mage_Task_BuiltIn_' . $taskName;
            $instance = new $className($taskConfig, $inRollback, $stage, $taskParameters);
        }

        assert($instance instanceOf Mage_Task_TaskAbstract);
        return $instance;
    }
}