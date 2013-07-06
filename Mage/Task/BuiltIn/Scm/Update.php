<?php
class Mage_Task_BuiltIn_Scm_Update
    extends Mage_Task_TaskAbstract
{
    private $_name = 'SCM Update [built-in]';

    public function getName()
    {
        return $this->_name;
    }

    public function init()
    {
        switch ($this->getConfig()->general('scm')) {
            case 'git':
                $this->_name = 'SCM Update (GIT) [built-in]';
                break;

            case 'svn':
                $this->_name = 'SCM Update (Subversion) [built-in]';
                break;
        }
    }

    public function run()
    {
        switch ($this->getConfig()->general('scm')) {
            case 'git':
                $command = 'git pull';
                break;

            case 'svn':
                $command = 'svn update';
                break;

            default:
                throw new Mage_Task_SkipException;
                break;
        }

        $result = $this->_runLocalCommand($command);
        $this->getConfig()->reload();

        return $result;
    }
}