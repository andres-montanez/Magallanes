<?php
class Mage_Config
{
    private $_arguments   = array();
    private $_parameters  = array();
    private $_environment = false;
    private $_host = null;
    private $_releaseId = null;
    private $_config = array(
        'general'     => array(),
        'scm'         => array(),
        'environment' => array(),
    );

    /**
     * Load the Configuration and parses the Arguments
     *
     * @param array $arguments
     */
    public function load($arguments)
    {
        $this->_parse($arguments);
        $this->_loadGeneral();
        $this->_loadSCM();
        $this->_loadEnvironment();
    }

    /**
     * Return the invocation argument based on a position
     * 0 = Invoked Command Name
     *
     * @param integer $position
     * @return mixed
     */
    public function getArgument($position = 0)
    {
        if (isset($this->_arguments[$position])) {
            return $this->_arguments[$position];
        } else {
            return false;
        }
    }

    /**
     * Returns all the invocation arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->_arguments;
    }

    /**
     * Return the a parameter
     *
     * @param string $name
     * @return mixed
     */
    public function getParameter($name, $default = null, $extraParameters = array())
    {
        if (isset($this->_parameters[$name])) {
            return $this->_parameters[$name];
        } else if (isset($extraParameters[$name])) {
            return $extraParameters[$name];
        } else {
            return $default;
        }
    }

    /**
     * Returns all the invocation arguments
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    /**
     * Returns the Current environment
     *
     * @return mixed
     */
    public function getEnvironment()
    {
        return $this->_environment;
    }

    /**
     * Reloads the configuration
     */
    public function reload()
    {
        $this->_loadGeneral();
        $this->_loadSCM();
        $this->_loadEnvironment();
    }

    /**
     * Get the Tasks to execute
     *
     * @param string $stage
     * @return array
     */
    public function getTasks($stage = 'on-deploy')
    {
        $tasks = array();
        $config = $this->_getEnvironmentOption('tasks', array());
        if (isset($config[$stage])) {
            $tasksData = ($config[$stage] ? (array) $config[$stage] : array());
            foreach ($tasksData as $taskName => $taskData) {
                if (is_array($taskData)) {
                    ;
                    $tasks[] = array(
                        'name' => key($taskData),
                        'parameters' => current($taskData),
                    );
                } else {
                    $tasks[] = $taskData;
                }
            }
        }

        return $tasks;
    }

    /**
     * Get the current Hosts to deploy
     *
     * @return array
     */
    public function getHosts()
    {
        $hosts = array();

        if (isset($this->_config['environment']['hosts'])) {
            if (is_array($this->_config['environment']['hosts'])) {
                $hosts = (array) $this->_config['environment']['hosts'];
            } else if (is_string($this->_config['environment']['hosts']) && file_exists($this->_config['environment']['hosts']) && is_readable($this->_config['environment']['hosts'])) {
                $fileContent = fopen($this->_config['environment']['hosts'], 'r');
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

    /**
     * Set the current host
     *
     * @param string $host
     * @return Mage_Config
     */
    public function setHost($host)
    {
        $this->_host = $host;
        return $this;
    }

    /**
     * Get the current host name
     *
     * @return string
     */
    public function getHostName()
    {
        $info = explode(':', $this->_host);
        return $info[0];
    }

    /**
     * Get the current Host Port
     *
     * @return unknown
     */
    public function getHostPort()
    {
        $info = explode(':', $this->_host);
        $info[] = $this->deployment('port', '22');
        return $info[1];
    }

    /**
     * Get the current Host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * Gets General Configuration
     *
     * @param string $option
     * @param string $default
     * @return mixed
     */
    public function general($option, $default = false)
    {
        $config = $this->_config['general'];
        if (isset($config[$option])) {
            if (is_array($default) && ($config[$option] == '')) {
                return $default;
            } else {
                return $config[$option];
            }
        } else {
            return $default;
        }
    }

    /**
     * Gets SCM Configuration
     *
     * @param string $option
     * @param string $default
     * @return mixed
     */
    public function scm($option, $default = false)
    {
        $config = $this->_config['scm'];
        if (isset($config[$option])) {
            if (is_array($default) && ($config[$option] == '')) {
                return $default;
            } else {
                return $config[$option];
            }
        } else {
            return $default;
        }
    }

    /**
     * Get deployment configuration
     *
     * @param string $option
     * @param string $default
     * @return string
     */
    public function deployment($option, $default = false)
    {
        $config = $this->_getEnvironmentOption('deployment', array());
        if (isset($config[$option])) {
            if (is_array($default) && ($config[$option] == '')) {
                return $default;
            } else {
                return $config[$option];
            }
        } else {
            return $default;
        }
    }

    /**
     * Returns Releaseing Options
     *
     * @param string $option
     * @param string $default
     * @return mixed
     */
    public function release($option, $default = false)
    {
        $config = $this->_getEnvironmentOption('releases', array());
        if (isset($config[$option])) {
            if (is_array($default) && ($config[$option] == '')) {
                return $default;
            } else {
                return $config[$option];
            }
        } else {
            return $default;
        }
    }

    /**
     * Set From Deployment Path
     *
     * @param string $from
     * @return Mage_Config
     */
    public function setFrom($from)
    {
        $this->_config['environment']['deployment']['from'] = $from;
        return $this;
    }

    /**
     * Sets the Current Release ID
     *
     * @param integer $id
     * @return Mage_Config
     */
    public function setReleaseId($id)
    {
        $this->_releaseId = $id;
        return $this;
    }

    /**
     * Gets the Current Release ID
     *
     * @return integer
     */
    public function getReleaseId()
    {
        return $this->_releaseId;
    }

    /**
     * Parse the Command Line options
     * @return boolean
     */
    private function _parse($arguments)
    {
        foreach ($arguments as $argument) {
            if (preg_match('/to:[\w]+/i', $argument)) {
                $this->_environment = str_replace('to:', '', $argument);

            } else if (preg_match('/--[\w]+/i', $argument)) {
                $optionValue = explode('=', substr($argument, 2));
                if (count($optionValue) == 1) {
                    $this->_parameters[$optionValue[0]] = true;
                } else if (count($optionValue) == 2) {
                    if (strtolower($optionValue[1]) == 'true') {
                        $this->_parameters[$optionValue[0]] = true;
                    } else if (strtolower($optionValue[1]) == 'false') {
                        $this->_parameters[$optionValue[0]] = false;
                    } else {
                        $this->_parameters[$optionValue[0]] = $optionValue[1];
                    }
                }
            } else {
                $this->_arguments[] = $argument;
            }
        }
    }

    /**
     * Loads the General Configuration
     */
    private function _loadGeneral()
    {
        if (file_exists('.mage/config/general.yml')) {
            $this->_config['general'] = spyc_load_file('.mage/config/general.yml');
        }
    }

    /**
     * Loads the SCM Configuration
     */
    private function _loadSCM()
    {
        if (file_exists('.mage/config/scm.yml')) {
            $this->_config['scm'] = spyc_load_file('.mage/config/scm.yml');
        }
    }

    /**
     * Loads the Environment configuration
     *
     * @throws Exception
     * @return boolean
     */
    private function _loadEnvironment()
    {
        $environment = $this->getEnvironment();
        if (($environment != false) && file_exists('.mage/config/environment/' . $environment . '.yml')) {
            $this->_config['environment'] = spyc_load_file('.mage/config/environment/' . $environment . '.yml');

            // Create temporal directory for clone
            if (isset($this->_config['environment']['deployment']['source']) && is_array($this->_config['environment']['deployment']['source'])) {
                if (trim($this->_config['environment']['deployment']['source']['temporal']) == '') {
                    $this->_config['environment']['deployment']['source']['temporal'] = '/tmp';
                }
                $newTemporal = rtrim($this->_config['environment']['deployment']['source']['temporal'], '/')
                . '/' . md5(microtime()) . '/';
                $this->_config['environment']['deployment']['source']['temporal'] = $newTemporal;
            }
            return true;

        } else if (($environment != '') && !file_exists('.mage/config/environment/' . $environment . '.yml')) {
            throw new Exception('Environment does not exists.');
        }

        return false;
    }

    /**
     * Get Environment root option
     *
     * @param string $option
     * @param mixed $default
     * @return mixed
     */
    private function _getEnvironmentOption($option, $default = array())
    {
        $config = $this->_config['environment'];
        if (isset($config[$option])) {
            return $config[$option];
        } else {
            return $default;
        }
    }

}