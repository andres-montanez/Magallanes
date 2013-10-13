<?php
class Mage_Task_BuiltIn_Symfony2_AsseticDump
    extends Mage_Task_TaskAbstract
{
    public function getName()
    {
        return 'Symfony v2 - Assetic Dump [built-in]';
    }

    public function run()
    {
    	// Options
    	$env = $this->getParameter('env', 'dev');

        $command = 'app/console assetic:dump --env=' . $env;
        $result = $this->_runLocalCommand($command);

        return $result;
    }
}