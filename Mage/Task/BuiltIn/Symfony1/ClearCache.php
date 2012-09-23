<?php
class Mage_Task_BuiltIn_Symfony_ClearCache
    extends Mage_Task_TaskAbstract
{
    public function getName()
    {
        return 'Symfony v1 - Clear Cache [built-in]';
    }
        
    public function run()
    {
        $command = 'symfony cc';
        $result = $this->_runLocalCommand($command);
        
        return $result;
    }
}