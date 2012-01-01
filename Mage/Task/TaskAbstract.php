<?php
abstract class Mage_Task_TaskAbstract
{
    protected $_config = null;
    
    public abstract function getName();
    
    public abstract function run();
    
    public final function __construct(Mage_Config $config)
    {
        $this->_config = $config;
    }
    
    public function init()
    {
    }
    
    protected final function _runLocalCommand($command, &$output = null)
    {
        return Mage_Console::executeCommand($command, $output);
    }
    
    protected final function _runRemoteCommand($command, &$output = null)
    {
        $localCommand = 'ssh '
                      . $this->_config->deployment('user') . '@' . $this->_config->getHost() . ' '
                      . '"cd ' . $this->_config->deployment('to') . ' && '
                      . $command . '"';

        return $this->_runLocalCommand($localCommand, $output);
    }
}