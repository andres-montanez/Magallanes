<?php
abstract class Mage_Task_TaskAbstract
{
    public abstract function getName();
    
    public abstract function run($config);
    
    protected function _runLocalCommand($command)
    {
        return Mage_Console::executeCommand($command);
    }
    
    protected function _runRemoteCommand($command)
    {
        
    }
}