<?php
class Mage_Task_Deploy
{
    private $_config = null;
    
    public function run(Mage_Config $config)
    {
        $this->_config = $config;
        
        foreach ($config->getHosts() as $host)
        {
            $taskConfig = $config->getConfig($host);
            $tasks = 0;
            $completedTasks = 0;

            Mage_Console::output(PHP_TAB . 'Deploying to ' . $host . PHP_EOL);
            
            foreach ($config->getTasks() as $taskName) {
                $tasks++;
                $task = Mage_Task_Factory::get($taskName);
                
                Mage_Console::output(PHP_TAB . PHP_TAB . 'Running ' . $task->getName() . ' ... ');
                $result = $task->run($taskConfig);

                if ($result == true) {
                    Mage_Console::output(PHP_TAB . 'OK' . PHP_EOL);
                    $completedTasks++;
                } else {
                    Mage_Console::output(PHP_TAB . 'FAIL' . PHP_EOL);
                }
            }

            Mage_Console::output(PHP_TAB . 'Deployment to ' . $host . ' compted: ' . $completedTasks . '/' . $tasks . ' tasks done.' . PHP_EOL . PHP_EOL);
        }
    }

}