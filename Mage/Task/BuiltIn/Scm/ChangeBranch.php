<?php
class Mage_Task_BuiltIn_Scm_ChangeBranch
    extends Mage_Task_TaskAbstract
{
    private $_name = 'SCM Changing branch [built-in]';

    public function getName()
    {
        return $this->_name;
    }

    public function init()
    {
        switch ($this->getConfig()->scm('type')) {
            case 'git':
                $this->_name = 'SCM Changing branch (GIT) [built-in]';
                break;

            case 'svn':
                $this->_name = 'SCM Changing branch (Subversion) [built-in]';
                break;
        }
    }

    public function run()
    {
        switch ($this->getConfig()->scm('type')) {
            case 'git':
            	$command = 'git branch | grep \'*\' | cut -d\' \' -f 2';
            	$currentBranch = 'master';
            	$result = $this->_runLocalCommand($command, $currentBranch);

            	$scmData = $this->getConfig()->deployment('scm', false);
                if ($result && is_array($scmData) && isset($scmData['branch'])) {
                	$branch = $this->getParameter('branch', $scmData['branch']);
                	$command = 'git checkout ' . $branch;
                	$result = $this->_runLocalCommand($command);

                	$oldBranchFile = '.mage/' . $this->getConfig()->getEnvironment() . '.oldBranch';
                	file_put_contents($oldBranchFile, $currentBranch);

                } else {
                    throw new Mage_Task_SkipException;
                }

                break;

            default:
                return false;
                break;
        }


        $this->getConfig()->reload();

        return $result;
    }
}