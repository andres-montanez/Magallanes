<?php
class Mage_Task_BuiltIn_Scm_ChangeBranchBack
    extends Mage_Task_TaskAbstract
{
    private $_name = 'SCM Changing branch Back [built-in]';

    public function getName()
    {
        return $this->_name;
    }

    public function init()
    {
        switch ($this->getConfig()->scm('type')) {
            case 'git':
                $this->_name = 'SCM Changing branch Back (GIT) [built-in]';
                break;

            case 'svn':
                $this->_name = 'SCM Changing branch Back (Subversion) [built-in]';
                break;
        }
    }

    public function run()
    {
        switch ($this->getConfig()->scm('type')) {
            case 'git':
            	$oldBranchFile = '.mage/' . $this->getConfig()->getEnvironment() . '.oldBranch';
            	$currentBranch = trim(file_get_contents($oldBranchFile));

            	$command = 'git checkout ' . $currentBranch;
            	$result = $this->_runLocalCommand($command);
            	@unlink($oldBranchFile);
                break;

            default:
                return false;
                break;
        }


        $this->getConfig()->reload();

        return $result;
    }
}