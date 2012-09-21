<?php
class Mage_Task_BuiltIn_Deployment_Rsync
    extends Mage_Task_TaskAbstract
    implements Mage_Task_Releases_BuiltIn
{
    public function getName()
    {
        if ($this->getConfig()->release('enabled', false) == true) {
            if ($this->getConfig()->getParameter('overrideRelease', false) == true) {
                return 'Rsync (with Releases override) [built-in]';
            } else {
                return 'Rsync (with Releases) [built-in]';
            }
        } else {
                return 'Rsync [built-in]';
        }
    }

    public function run()
    {
        $overrideRelease = $this->getConfig()->getParameter('overrideRelease', false);

        if ($overrideRelease == true) {
            $releaseToOverride = false;
            $resultFetch = $this->_runRemoteCommand('ls -ld current | cut -d\"/\" -f2', $releaseToOverride);
            if (is_numeric($releaseToOverride)) {
                $this->getConfig()->setReleaseId($releaseToOverride);
            }
        }

        $excludes = array(
            '.git',
            '.svn',
            '.mage',
            '.gitignore'
        );

        // Look for User Excludes
        $userExcludes = $this->getConfig()->deployment('excludes', array());

        // If we are working with releases
        $deployToDirectory = $this->getConfig()->deployment('to');
        if ($this->getConfig()->release('enabled', false) == true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');

            $deployToDirectory = rtrim($this->getConfig()->deployment('to'), '/')
                               . '/' . $releasesDirectory
                               . '/' . $this->getConfig()->getReleaseId();
            $this->_runRemoteCommand('mkdir -p ' . $releasesDirectory . '/' . $this->getConfig()->getReleaseId());
        }

        $command = 'rsync -avz '
                 . '--rsh="ssh -p' . $this->getConfig()->getHostPort() . '" '
                 . $this->_excludes(array_merge($excludes, $userExcludes)) . ' '
                 . $this->getConfig()->deployment('from') . ' '
                 . $this->getConfig()->deployment('user') . '@' . $this->getConfig()->getHostName() . ':' . $deployToDirectory;

        $result = $this->_runLocalCommand($command);

        return $result;
    }

    private function _excludes(Array $excludes)
    {
        $excludesRsync = '';
        foreach ($excludes as $exclude) {
            $excludesRsync .= ' --exclude ' . $exclude . ' ';
        }

        $excludesRsync = trim($excludesRsync);
        return $excludesRsync;
    }
}