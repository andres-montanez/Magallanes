<?php
class Mage_Task_Releases
{
    private $_config = null;
    private $_action = null;
    
    public function setAction($action)
    {
        $this->_action = $action;
        return $this;
    }
    
    public function getAction()
    {
        return $this->_action;
    }
    
    public function run(Mage_Config $config)
    {
        $this->_config = $config;
        
        // Run Tasks for Deployment
        $hosts = $config->getHosts();
        
        if (count($hosts) == 0) {
            Mage_Console::output('<light_purple>Warning!</light_purple> <dark_gray>No hosts defined, unable to get releases.</dark_gray>', 1, 3);
            
        } else {
            foreach ($hosts as $host) {
                $taskConfig = $config->getConfig($host);

                switch ($this->getAction()) {
                    case 'list':
                        $task = Mage_Task_Factory::get('releases/list', $taskConfig);
                        $task->init();
                        $result = $task->run();
                        break;
                }
            }
        }

    }
    
    private function _listReleases()
    {
        
    }
    
}