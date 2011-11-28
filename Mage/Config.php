<?php
class Mage_Config
{
    private $_environment = null;
    private $_scm = null;
    
    public function loadEnvironment($environment)
    {
        if (($environment != '') && file_exists('.mage/config/environment/' . $environment . '.yaml')) {
            $this->_environment = @yaml_parse_file('.mage/config/environment/' . $environment . '.yaml');            
        }
    }
    
    public function loadSCM()
    {
        if (file_exists('.mage/config/scm.yaml')) {
            $this->_scm = @yaml_parse_file('.mage/config/scm.yaml');            
        }
    }
    
    public function getEnvironment()
    {
        return $this->_environment;
    }
    
    public function getSCM()
    {
        return $this->_scm;
    }

    public function getHosts()
    {
        $config = $this->getEnvironment();
        $hosts = array();
        
        if (isset($config['hosts'])) {
            $hosts = (array) $config['hosts'];
        }
        
        return $hosts;
    }
    
    public function getTasks($stage = 'on-deploy')
    {
        switch ($stage) {
            case 'pre-deploy':
                $type = 'tasks';
                $stage = 'pre-deploy';
                break;
                
            case 'post-deploy':
                $type = 'tasks';
                $stage = 'post-deploy';
                break;
                
            case 'post-release':
                $type = 'releases';
                $stage = 'post-release';
                break;
                
            case 'on-deploy':
            default:
                $type = 'tasks';
                $stage = 'on-deploy';
                break;
        }
        
        $tasks = array();
        $config = $this->getEnvironment();

        if (isset($config[$type]) && isset($config[$type][$stage])) {
            $tasks = (array) $config[$type][$stage];
        }

        return $tasks;
    }
    
    public function getConfig($host = false)
    {
        $taskConfig = array();
        $taskConfig['deploy'] = $this->getEnvironment();
        $taskConfig['deploy']['host'] = $host;
        $taskConfig['scm'] = $this->getSCM();
        
        unset($taskConfig['deploy']['tasks']);
        unset($taskConfig['deploy']['hosts']);
        
        return $taskConfig;
    }
}