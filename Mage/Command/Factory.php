<?php
class Mage_Command_Factory
{
    /**
     *
     *
     * @param string $commandName
     * @param Mage_Config $config
     * @return Mage_Command_CommandAbstract
     */
    public static function get($commandName, Mage_Config $config)
    {
        $instance = null;
        $commandName = ucwords(str_replace('-', ' ', $commandName));
        $commandName = str_replace(' ', '', $commandName);

//        if (strpos($commandName, '/') === false) {
//            Mage_Autoload::loadUserTask($taskName);
//            $className = 'Task_' . ucfirst($taskName);
//            $instance = new $className($taskConfig, $inRollback, $stage);

//        } else {
            $commandName = str_replace(' ', '_', ucwords(str_replace('/', ' ', $commandName)));
            $className = 'Mage_Command_BuiltIn_' . $commandName;
            if (Mage_Autoload::isLoadable($className)) {
                $instance = new $className;
                $instance->setConfig($config);
            } else {
                throw new Exception('Command not found.');
            }

//        }

        assert($instance instanceOf Mage_Command_CommandAbstract);
        return $instance;
    }
}