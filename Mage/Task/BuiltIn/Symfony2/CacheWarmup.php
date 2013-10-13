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
    	// Options
    	$env = $this->getParameter('env', 'dev');

        $command = 'app/console cache:warmup --env=' . $env;
        $result = $this->runCommand($command);

        return $result;
    }
}
