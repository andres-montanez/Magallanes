<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage;

use Mage\Yaml\Yaml;
use Exception;

/**
 * Magallanes Configuration
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class Config
{
	/**
	 * Arguments loaded
	 * @var array
	 */
    private $arguments = array();

    /**
     * Parameters loaded
     * @var array
     */
    private $parameters = array();

    /**
     * Environment
     * @var string|boolean
     */
    private $environment = false;

    /**
     * The current Host
     * @var string
     */
    private $host = null;

    /**
     * Custom Configuration for the current Host
     * @var array
     */
    private $hostConfig = array();

    /**
     * The Relase ID
     * @var integer
     */
    private $releaseId = null;

    /**
     * Magallanes Global and Environment configuration
     * @var array
     */
    private $config = array(
        'general'     => array(),
        'environment' => array(),
    );

    /**
     * Parse the Command Line options
     * @return boolean
     */
    protected function parse($arguments)
    {
    	foreach ($arguments as $argument) {
    		if (preg_match('/to:[\w]+/i', $argument)) {
    			$this->environment = str_replace('to:', '', $argument);

    		} else if (preg_match('/--[\w]+/i', $argument)) {
    			$optionValue = explode('=', substr($argument, 2));
    			if (count($optionValue) == 1) {
    				$this->parameters[$optionValue[0]] = true;
    			} else if (count($optionValue) == 2) {
    				if (strtolower($optionValue[1]) == 'true') {
    					$this->parameters[$optionValue[0]] = true;
    				} else if (strtolower($optionValue[1]) == 'false') {
    					$this->parameters[$optionValue[0]] = false;
    				} else {
    					$this->parameters[$optionValue[0]] = $optionValue[1];
    				}
    			}
    		} else {
    			$this->arguments[] = $argument;
    		}
    	}
    }

    /**
     * Loads the General Configuration
     */
    protected function loadGeneral()
    {
    	if (file_exists('.mage/config/general.yml')) {
    		$this->config['general'] = Yaml::parse(file_get_contents('.mage/config/general.yml'));
    	}
    }

    /**
     * Loads the Environment configuration
     *
     * @throws Exception
     * @return boolean
     */
    protected function loadEnvironment()
    {
    	$environment = $this->getEnvironment();
    	if (($environment != false) && file_exists('.mage/config/environment/' . $environment . '.yml')) {
    		$this->config['environment'] = Yaml::parse(file_get_contents('.mage/config/environment/' . $environment . '.yml'));

    		// Create temporal directory for clone
    		if (isset($this->config['environment']['deployment']['source']) && is_array($this->config['environment']['deployment']['source'])) {
    			if (trim($this->config['environment']['deployment']['source']['temporal']) == '') {
    				$this->config['environment']['deployment']['source']['temporal'] = '/tmp';
    			}
    			$newTemporal = rtrim($this->config['environment']['deployment']['source']['temporal'], '/')
    			. '/' . md5(microtime()) . '/';
    			$this->config['environment']['deployment']['source']['temporal'] = $newTemporal;
    		}
    		return true;

    	} else if (($environment != '') && !file_exists('.mage/config/environment/' . $environment . '.yml')) {
    		throw new Exception('Environment does not exists.');
    	}

    	return false;
    }

    /**
     * Load the Configuration and parses the Arguments
     *
     * @param array $arguments
     */
    public function load($arguments)
    {
        $this->parse($arguments);
        $this->loadGeneral();
        $this->loadEnvironment();
    }

    /**
     * Reloads the configuration
     */
    public function reload()
    {
    	$this->loadGeneral();
    	$this->loadEnvironment();
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
        if (isset($this->arguments[$position])) {
            return $this->arguments[$position];
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
        return $this->arguments;
    }

    /**
     * Return the a parameter
     *
     * @param string $name
     * @param mixed $default
     * @param array $extraParameters
     * @return mixed
     */
    public function getParameter($name, $default = null, $extraParameters = array())
    {
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
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
        return $this->parameters;
    }

    /**
     * Adds (or replaces) a parameter
     * @param string $name
     * @param mixed $value
     */
    public function addParameter($name, $value = true)
    {
    	$this->parameters[$name] = $value;
    }

    /**
     * Returns the Current environment
     *
     * @return mixed
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Get the Tasks to execute
     *
     * @param string $stage
     * @return array
     */
    public function getTasks($stage = 'deploy')
    {
    	if ($stage == 'deploy') {
    		$configStage = 'on-deploy';
    	} else {
    		$configStage = $stage;
    	}

        $tasks = array();
        $config = $this->getEnvironmentOption('tasks', array());

        // Host Config
        if (is_array($this->hostConfig) && isset($this->hostConfig['tasks'])) {
        	if (isset($this->hostConfig['tasks'][$configStage])) {
        		$config[$configStage] = $this->hostConfig['tasks'][$configStage];
        	}
        }

        if (isset($config[$configStage])) {
            $tasksData = ($config[$configStage] ? (array) $config[$configStage] : array());
            foreach ($tasksData as $taskData) {
                if (is_array($taskData)) {
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

        if (isset($this->config['environment']['hosts'])) {
            if (is_array($this->config['environment']['hosts'])) {
                $hosts = (array) $this->config['environment']['hosts'];
            } else if (is_string($this->config['environment']['hosts']) && file_exists($this->config['environment']['hosts']) && is_readable($this->config['environment']['hosts'])) {
                $fileContent = fopen($this->config['environment']['hosts'], 'r');
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
     * @return \Mage\Config
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set the host specific configuration
     *
     * @param array $hostConfig
     * @return \Mage\Config
     */
    public function setHostConfig($hostConfig = null)
    {
    	$this->hostConfig = $hostConfig;
    	return $this;
    }

    /**
     * Get the current host name
     *
     * @return string
     */
    public function getHostName()
    {
        $info = explode(':', $this->host);
        return $info[0];
    }

    /**
     * Get the current Host Port
     *
     * @return integer
     */
    public function getHostPort()
    {
        $info = explode(':', $this->host);
        $info[] = $this->deployment('port', '22');
        return $info[1];
    }

    /**
     * Get the general Host Identity File Option
     *
     * @return string
     */
    public function getHostIdentityFileOption()
    {
        return $this->deployment('identity-file') ? ('-i ' . $this->deployment('identity-file') . ' ') : '';
    }
    
    /**
     * Get the current Host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
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
        $config = $this->config['general'];
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
     * Gets Environments Full Configuration
     *
     * @param string $option
     * @param string $default
     * @return mixed
     */
    public function environmentConfig($option, $default = false)
    {
    	return $this->getEnvironmentOption($option, $default);
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
    	// Host Config
    	if (is_array($this->hostConfig) && isset($this->hostConfig['deployment'])) {
    		if (isset($this->hostConfig['deployment'][$option])) {
    			return $this->hostConfig['deployment'][$option];
    		}
    	}

    	// Global Config
        $config = $this->getEnvironmentOption('deployment', array());
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
     * Returns Releasing Options
     *
     * @param string $option
     * @param string $default
     * @return mixed
     */
    public function release($option, $default = false)
    {
    	// Host Config
    	if (is_array($this->hostConfig) && isset($this->hostConfig['releases'])) {
    		if (isset($this->hostConfig['releases'][$option])) {
    			return $this->hostConfig['releases'][$option];
    		}
    	}

        $config = $this->getEnvironmentOption('releases', array());
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
     * @return \Mage\Config
     */
    public function setFrom($from)
    {
        $this->config['environment']['deployment']['from'] = $from;
        return $this;
    }

    /**
     * Sets the Current Release ID
     *
     * @param integer $id
     * @return \Mage\Config
     */
    public function setReleaseId($id)
    {
        $this->releaseId = $id;
        return $this;
    }

    /**
     * Gets the Current Release ID
     *
     * @return integer
     */
    public function getReleaseId()
    {
        return $this->releaseId;
    }

    /**
     * Get Environment root option
     *
     * @param string $option
     * @param mixed $default
     * @return mixed
     */
    protected function getEnvironmentOption($option, $default = array())
    {
        $config = $this->config['environment'];
        if (isset($config[$option])) {
            return $config[$option];
        } else {
            return $default;
        }
    }

}
