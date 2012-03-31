<?php
class Mage_Config
{
    private $_environmentName = null;
    private $_environment = null;
    private $_scm = null;
    private $_general = null;
    private $_host = null;
    private $_releaseId = null;
    
    public function loadEnvironment($environment)
    {
        if (($environment != '') && file_exists('.mage/config/environment/' . $environment . '.yml')) {
            $this->_environment = spyc_load_file('.mage/config/environment/' . $environment . '.yml');
            $this->_environmentName = $environment;
            
            // Create temporal directory for clone
            if (is_array($this->_environment['deployment']['source'])) {
                if (trim($this->_environment['deployment']['source']['temporal']) == '') {
                    $this->_environment['deployment']['source']['temporal'] = '/tmp';
                }
                $newTemporal = rtrim($this->_environment['deployment']['source']['temporal'], '/')
                             . '/' . md5(microtime()) . '/';
                $this->_environment['deployment']['source']['temporal'] = $newTemporal;
            }            
        }
    }
    
    public function loadSCM()
    {
        if (file_exists('.mage/config/scm.yml')) {
            $this->_scm = spyc_load_file('.mage/config/scm.yml');            
        }
    }
    
    public function loadGeneral()
    {
        if (file_exists('.mage/config/general.yml')) {
            $this->_general = spyc_load_file('.mage/config/general.yml');
        }
    }
    
    public function getEnvironment()
    {
        return $this->_environment;
    }
    
    public function getEnvironmentName()
    {
        return $this->_environmentName;
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
    
    public function getHostName()
    {
        $info = explode(':', $this->_host);
        return $info[0];
    }
    
    public function getHostPort()
    {
        $info = explode(':', $this->_host);
        $info[] = $this->deployment('port', '22');
        return $info[1];
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
            $tasks = ($config[$type][$stage] ? (array) $config[$type][$stage] : array());
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
        $this->_environment['deployment']['from'] = $from;
        return $this;
    }
    
    public function deployment($option, $default = false)
    {
        $options = $this->getEnvironment();
        if (isset($options['deployment'][$option])) {
            if (is_array($default) && ($options['deployment'][$option] == '')) {
                return $default;
            } else {
                return $options['deployment'][$option];                
            }
        } else {
            return $default;
        }
    }
    
    public function release($option, $default = false)
    {
        $options = $this->getEnvironment();
        if (isset($options['releases'][$option])) {
            if (is_array($default) && ($options['releases'][$option] == '')) {
                return $default;
            } else {
                return $options['releases'][$option];
            }
        } else {
            return $default;
        }
    }
    
    public function scm($option, $default = false)
    {
        $options = $this->_scm;
        if (isset($options[$option])) {
            if (is_array($default) && ($options[$option] == '')) {
                return $default;
            } else {
                return $options[$option];
            }
            return $options[$option];
        } else {
            return $default;
        }
    }
    
    public function general($option, $default = false)
    {
        $options = $this->_general;
        if (isset($options[$option])) {
            if (is_array($default) && ($options[$option] == '')) {
                return $default;
            } else {
                return $options[$option];
            }
        } else {
            return $default;
        }
    }
    
    public function mail($option, $default = false)
    {
        $options = $this->_general;
        if (isset($options['mail'][$option])) {
            if (is_array($default) && ($options['mail'][$option] == '')) {
                return $default;
            } else {
                return $options['mail'][$option];
            }
        } else {
            return $default;
        }
    }
}