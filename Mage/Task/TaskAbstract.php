<?php
abstract class Mage_Task_TaskAbstract
{
    protected $_config = null;
    
    public abstract function getName();
    
    public abstract function run();
    
    public final function __construct($config)
    {
        $this->_config = $config;
    }
    
    public function init()
    {
    }
    
    protected final function _runLocalCommand($command)
    {
        return Mage_Console::executeCommand($command);
    }
    
    protected final function _runRemoteCommand($command)
    {
        $localCommand = 'ssh '
                      . $this->_config['deploy']['deployment']['user'] . '@' . $this->_config['deploy']['host'] . ' '
                      . '"cd ' . $this->_config['deploy']['deployment']['to'] . ' && '
                      . $command . '"';

        return $this->_runLocalCommand($localCommand);
    }
}