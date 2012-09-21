<?php
class Mage_Task_BuiltIn_Deployment_Releases
    extends Mage_Task_TaskAbstract
    implements Mage_Task_Releases_BuiltIn
{
    public function getName()
    {
        return 'Releasing [built-in]';
    }

    public function run()
    {
        if ($this->getConfig()->release('enabled', false) == true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');
            $symlink = $this->getConfig()->release('symlink', 'current');

            if (substr($symlink, 0, 1) == '/') {
                $releasesDirectory = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $releasesDirectory;
            }

            $currentCopy = $releasesDirectory . '/' . $this->getConfig()->getReleaseId();

            // Fetch the user and group from base directory
            $userGroup = '33:33';
            $resultFetch = $this->_runRemoteCommand('ls -ld . | awk \'{print \$3\":\"\$4}\'', $userGroup);

            // Remove symlink if exists; create new symlink and change owners
            $command = 'rm -f ' . $symlink
                     . ' ; '
                     . 'ln -sf ' . $currentCopy . ' ' . $symlink
                     . ' && '
                     . 'chown -h ' . $userGroup . ' ' . $symlink
                     . ' && '
                     . 'chown -R ' . $userGroup . ' ' . $currentCopy;
            $result = $this->_runRemoteCommand($command);

            // Count Releases
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

            return $result;

        } else {
            return false;
        }
    }

}