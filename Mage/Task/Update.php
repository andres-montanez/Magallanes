<?php
class Mage_Task_Update
{
    private $_config = null;
    
    public function run(Mage_Config $config)
    {
        $this->_config = $config;

        $task = Mage_Task_Factory::get('scm/update', $config);
        $task->init();
        
        Mage_Console::output('Updating application via ' . $task->getName() . ' ... ', 1, 0);
        $result = $task->run();
        
        if ($result == true) {
            Mage_Console::output('OK' . PHP_EOL, 0);
        } else {
            Mage_Console::output( 'FAIL' . PHP_EOL, 0);
        }
    }

}