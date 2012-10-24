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
        
        // Count Releases
        if ($this->getConfig()->release('enabled', false) == true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');
            $symlink = $this->getConfig()->release('symlink', 'current');
            
            if (substr($symlink, 0, 1) == '/') {
                $releasesDirectory = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $releasesDirectory;
            }
            
            $maxReleases = $this->getConfig()->release('max', false);
            if (($maxReleases !== false) && ($maxReleases > 0)) {
                $releasesList = '';
                $countReleasesFetch = $this->_runRemoteCommand('ls -1 ' . $releasesDirectory, $releasesList);
                $releasesList = trim($releasesList);
            
                if ($releasesList != '') {
                    $releasesList = explode(PHP_EOL, $releasesList);
                    if (count($releasesList) > $maxReleases) {
                        $releasesToDelete = array_diff($releasesList, array($this->getConfig()->getReleaseId()));
                        sort($releasesToDelete);
                        $releasesToDeleteCount = count($releasesToDelete) - $maxReleases;
                        $releasesToDelete = array_slice($releasesToDelete, 0, $releasesToDeleteCount + 1);
            
                        foreach ($releasesToDelete as $releaseIdToDelete) {
                            $directoryToDelete = $releasesDirectory . '/' . $releaseIdToDelete;
                            if ($directoryToDelete != '/') {
                                $command = 'rm -rf ' . $directoryToDelete;
                                $result = $result && $this->_runRemoteCommand($command);
                            }
                        }
                    }
                }
            }
        }

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