<?php
class Magallanes_Task_Update
{
    private $_config = null;
    
    public function run(Magallanes_Config $config)
    {
        $csmConfig = $config->getCSM();
        $csm = new Magallanes_Task_CSM_Git;
        $csm->run($csmConfig['url']);
    }
}