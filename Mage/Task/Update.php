<?php
class Mage_Task_Update
{
    private $_config = null;
    
    public function run(Mage_Config $config)
    {
        $this->_config = $config;

        $taskConfig = $config->getConfig();
        $task = Mage_Task_Factory::get('scm/update', $taskConfig);
        $task->init();
        
        Mage_Console::output( PHP_TAB . 'Updating application via ' . $task->getName() . ' ... ');
        $result = $task->run();
        
        if ($result == true) {
            Mage_Console::output(PHP_TAB . 'OK' . PHP_EOL);
        } else {
            Mage_Console::output(PHP_TAB . 'FAIL' . PHP_EOL);
        }
    }

}