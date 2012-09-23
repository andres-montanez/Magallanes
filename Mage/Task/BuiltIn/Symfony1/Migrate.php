<?php
class Mage_Task_BuiltIn_Symfony1_Migrate
    extends Mage_Task_TaskAbstract
{
    public function getName()
    {
        return 'Symfony v1 - Run Migrations [built-in]';
    }

    public function run()
    {
        $command = 'symfony doctrine:migrate';
        $result = $this->_runLocalCommand($command);

        return $result;
    }
}
