<?php
class Mage_Task_Factory
{
    /**
     * 
     * 
     * @param string $taskName
     * @param boolean $inRollback
     * @return Mage_Task_TaskAbstract
     */
    public static function get($taskName, Mage_Config $taskConfig, $inRollback = false)
    {
        $instance = null;
        $taskName = ucwords(str_replace('-', ' ', $taskName));
        $taskName = str_replace(' ', '', $taskName);
                
        if (strpos($taskName, '/') === false) {
            Mage_Autoload::loadUserTask($taskName);
            $className = 'Task_' . ucfirst($taskName);
            $instance = new $className($taskConfig, $inRollback);

        } else {
            $taskName = str_replace(' ', '_', ucwords(str_replace('/', ' ', $taskName)));
            $className = 'Mage_Task_BuiltIn_' . $taskName;
            $instance = new $className($taskConfig, $inRollback);
        }

        assert($instance instanceOf Mage_Task_TaskAbstract);
        return $instance;
    }
}