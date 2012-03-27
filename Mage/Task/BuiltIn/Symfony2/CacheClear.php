<?php
class Mage_Task_BuiltIn_Symfony2_CacheClear
    extends Mage_Task_TaskAbstract
{
    public function getName()
    {
        return 'Symfony v2 - Cache Clear [built-in]';
    }
        
    public function run()
    {
        $command = 'app/console cache:clear';
        $result = $this->_runLocalCommand($command);
        
        return $result;
    }
}