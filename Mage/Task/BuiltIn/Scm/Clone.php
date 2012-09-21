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
        $this->_source = $this->getConfig()->deployment('source');
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
                // Clone Repo
                $command = 'cd ' . $this->_source['temporal'] . ' ; '
                         . 'git clone ' . $this->_source['repository'] . ' . ';
                $result = $this->_runLocalCommand($command);

                // Checkout Branch
                $command = 'cd ' . $this->_source['temporal'] . ' ; '
                         . 'git checkout ' . $this->_source['from'];
                $result = $result && $this->_runLocalCommand($command);

                $this->getConfig()->setFrom($this->_source['temporal']);
                break;

            case 'svn':
                return false;
                break;
        }

        return $result;
    }
}