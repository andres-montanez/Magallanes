<?php
class Mage_Task_Deploy
{
    private $_config = null;
    
    public function run(Mage_Config $config)
    {
        $this->_config = $config;
        
        // Run Pre-Deployment Tasks
        $this->_runNonDeploymentTasks('pre', $config);
        
        // Run Tasks for Deployment
        foreach ($config->getHosts() as $host) {
            $taskConfig = $config->getConfig($host);
            $tasks = 0;
            $completedTasks = 0;

            Mage_Console::output('Deploying to <dark_gray>' . $host . '</dark_gray>');
            
            foreach ($config->getTasks() as $taskName) {
                $tasks++;
                $task = Mage_Task_Factory::get($taskName, $taskConfig);
                $task->init();
                
                Mage_Console::output('Running <purple>' . $task->getName() . '</purple> ... ', 2, false);
                $result = $task->run();

                if ($result == true) {
                    Mage_Console::output('<green>OK</green>');
                    $completedTasks++;
                } else {
                    Mage_Console::output('<red>FAIL</red>');
                }
            }
            
            if ($completedTasks == $tasks) {
                $tasksColor = 'green';                
            } else {
                $tasksColor = 'red';                
            }

            Mage_Console::output('Deployment to <dark_gray>' . $host . '</dark_gray> compted: <' . $tasksColor . '>' . $completedTasks . '/' . $tasks . '</' . $tasksColor . '> tasks done.' . PHP_EOL . PHP_EOL);
        }
        
        // Run Post-Deployment Tasks
        $this->_runNonDeploymentTasks('post', $config);
    }

    private function _runNonDeploymentTasks($type, Mage_Config $config)
    {
        Mage_Console::output('Starting <dark_gray>' . ucfirst($type) . '-Deployment</dark_gray> tasks:');

        $taskConfig = $config->getConfig();
        $tasks = 0;
        $completedTasks = 0;

        foreach ($config->getTasks($type) as $taskName) {
            $tasks++;
            $task = Mage_Task_Factory::get($taskName, $taskConfig);
            $task->init();
                
            Mage_Console::output('Running <purple>' . $task->getName() . '</purple> ... ', 2, false);
            $result = $task->run();

            if ($result == true) {
                Mage_Console::output('<green>OK</green>');
                $completedTasks++;
            } else {
                Mage_Console::output('<red>FAIL</red>');
            }            
        }

        if ($completedTasks == $tasks) {
            $tasksColor = 'green';                
        } else {
            $tasksColor = 'red';                
        }

        Mage_Console::output('Finished <dark_gray>' . ucfirst($type) . '-Deployment</dark_gray> tasks: <' . $tasksColor . '>' . $completedTasks . '/' . $tasks . '</' . $tasksColor . '> tasks done.' . PHP_EOL . PHP_EOL);
        
    }
    
    
}