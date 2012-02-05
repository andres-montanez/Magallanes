<?php
class Mage_Task_BuiltIn_Scm_RemoveClone
    extends Mage_Task_TaskAbstract
{
    private $_name = 'SCM Remove Clone [built-in]';
    private $_source = null;
    
    public function getName()
    {
        return $this->_name;
    }

    public function init() 
    {
        $this->_source = $this->_config->deployment('source');
    }
    
    public function run()
    {
        return $this->_runLocalCommand('rm -rf ' . $this->_source['temporal']);
    }
}