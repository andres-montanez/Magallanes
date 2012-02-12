<?php
class Mage_Config
{
    private $_environment = null;
    private $_scm = null;
    private $_general = null;
    private $_host = null;
    private $_releaseId = null;
    
    public function loadEnvironment($environment)
    {
        if (($environment != '') && file_exists('.mage/config/environment/' . $environment . '.yml')) {
            $this->_environment = @yaml_parse_file('.mage/config/environment/' . $environment . '.yml');            
        }
    }
    
    public function loadSCM()
    {
        if (file_exists('.mage/config/scm.yml')) {
            $this->_scm = @yaml_parse_file('.mage/config/scm.yml');            
        }
    }
    
    public function loadGeneral()
    {
        if (file_exists('.mage/config/general.yml')) {
            $this->_general = @yaml_parse_file('.mage/config/general.yml');
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

    public function getGlobal()
    {
        return $this->_global;
    }
    
    public function getHosts()
    {
        $config = $this->getEnvironment();
        $hosts = array();
        
        if (isset($config['hosts'])) {
            if (is_array($config['hosts'])) {
                $hosts = (array) $config['hosts'];                
            } else if (is_string($config['hosts'])) {
                $fileContent = fopen($config['hosts'], 'r');
                while (($host = fgets($fileContent)) == true) {
                    $host = trim($host);
                    if ($host != '') {
                        $hosts[] = $host;                        
                    }
                }
            }
        }
        
        return $hosts;
    }
    
    public function setHost($host)
    {
        $this->_host = $host;
        return $this;
    }
    
    public function getHost()
    {
        return $this->_host;
    }
    
    public function setReleaseId($id)
    {
        $this->_releaseId = $id;
        return $this;
    }
    
    public function getReleaseId()
    {
        return $this->_releaseId;
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

    public function setFrom($from)
    {
        $options['deployment']['from'] = $from;
        return $this;
    }
    
    public function deployment($option, $default = false)
    {
        $options = $this->getEnvironment();
        if (isset($options['deployment'][$option])) {
            return $options['deployment'][$option];
        } else {
            return $default;
        }
    }
    
    public function release($option, $default = false)
    {
        $options = $this->getEnvironment();
        if (isset($options['releases'][$option])) {
            return $options['releases'][$option];
        } else {
            return $default;
        }
    }
    
    public function scm($option, $default = false)
    {
        $options = $this->_scm;
        if (isset($options[$option])) {
            return $options[$option];
        } else {
            return $default;
        }
    }
    
    public function general($option, $default = false)
    {
        $options = $this->_general;
        if (isset($options[$option])) {
            return $options[$option];
        } else {
            return $default;
        }
    }
    
    public function mail($option, $default = false)
    {
        $options = $this->_general;
        if (isset($options['mail'][$option])) {
            return $options['mail'][$option];
        } else {
            return $default;
        }
    }
}