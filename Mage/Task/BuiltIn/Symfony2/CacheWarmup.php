<?php
class Mage_Task_BuiltIn_Symfony2_CacheWarmup
    extends Mage_Task_TaskAbstract
{
    public function getName()
    {
        return 'Symfony v2 - Cache Warmup [built-in]';
    }

    public function run()
    {
        $command = 'app/console cache:warmup';
        $result = $this->_runLocalCommand($command);

        return $result;
    }
}
