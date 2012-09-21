<?php
abstract class Mage_Command_CommandAbstract
{
    protected $_config = null;

    public abstract function run();

    public function setConfig(Mage_Config $config)
    {
        $this->_config = $config;
    }

    /**
     *
     * @return Mage_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }
}