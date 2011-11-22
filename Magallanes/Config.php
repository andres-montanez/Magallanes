<?php
class Magallanes_Config
{
    private $_environment = null;
    private $_csm = null;
    
    public function loadEnvironment($environment)
    {
        $this->_environment = yaml_parse_file('.mage/environment/' . $environment . '.yaml');
    }
    
    public function loadCSM()
    {
        $this->_csm = yaml_parse_file('.mage/csm.yaml');
    }
    
    public function getEnvironment()
    {
        return $this->_environment;
    }
    
    public function getCSM()
    {
        return $this->_csm;
    }
}