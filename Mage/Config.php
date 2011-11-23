<?php
class Mage_Config
{
    private $_environment = null;
    private $_scm = null;
    
    public function loadEnvironment($environment)
    {
        $this->_environment = yaml_parse_file('.mage/config/environment/' . $environment . '.yaml');
    }
    
    public function loadSCM()
    {
        $this->_scm = yaml_parse_file('.mage/config/scm.yaml');
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
        return $config['hosts'];
    }
    
    public function getTasks()
    {
        $config = $this->getEnvironment();
        return $config['tasks'];
    }
    
    public function getConfig($host)
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