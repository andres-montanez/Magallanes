<?php
class Mage_Task_BuiltIn_Symfony2_ClearCache
    extends Mage_Task_TaskAbstract
{
    public function getName()
    {
        return 'Symfony v2 - Run Migrations [built-in]';
    }
        
    public function run()
    {
        $command = 'app/console doctrine:migrations:migrate';
        $result = $this->_runLocalCommand($command);
        
        return $result;
    }
}