<?php
class Mage_Task_Factory
{
    /**
     * 
     * 
     * @param string $taskName
     * @return Mage_Task_TaskAbstract
     */
    public static function get($taskName)
    {
        $taskName = str_replace(' ', '_', ucwords(str_replace('/', ' ', $taskName)));
        $className = 'Mage_Task_BuiltIn_' . $taskName;
        return new $className;
    }
}