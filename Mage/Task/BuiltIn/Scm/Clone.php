<?php
class Mage_Task_BuiltIn_Scm_Clone
    extends Mage_Task_TaskAbstract
{
    private $_name = 'SCM Clone [built-in]';
    private $_source = null;
    
    public function getName()
    {
        return $this->_name;
    }
    
    public function init()
    {
        $this->_source = $this->_config->deployment('source');
        switch ($this->_source['type']) {
            case 'git':
                $this->_name = 'SCM Clone (GIT) [built-in]';
                break;

            case 'svn':
                $this->_name = 'SCM Clone (Subversion) [built-in]';
                break;
        }
    }
    
    public function run()
    {
        $this->_runLocalCommand('mkdir -p ' . $this->_source['temporal']);
        switch ($this->_source['type']) {
            case 'git':
                $command = 'cd ' . $this->_source['temporal']
                         . ' && '
                         . 'git clone ' . $this->_source['repository'] . ' . '
                         . ' && '
                         . 'git checkout ' . $this->_source['from'];
                $this->_config->setFrom($this->_source['temporal']);
                break;

            case 'svn':
                return false;
                break;
        }

        $result = $this->_runLocalCommand($command);
        
        return $result;
    }
}