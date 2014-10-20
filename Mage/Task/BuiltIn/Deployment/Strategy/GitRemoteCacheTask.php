<?php
namespace Mage\Task\BuiltIn\Deployment\Strategy;

use Exception;
use Mage\Task\AbstractTask;
use Mage\Task\ErrorWithMessageException;
use Mage\Task\Releases\IsReleaseAware;
use Mage\Task\SkipException;

/**
 * The git remote cache deployment task.
 *
 * This tasks uses a remote checkout on the server to provide the release.
 * In our use case this remote cache resides in $to/$shared/git-remote-cache,
 * $shared is substituted with "shared" by default. At this time, the remote cache
 * is not build automatically, you need to provide a clean checkout before you can
 * start using it.
 *
 * @package Mage\Task\BuiltIn\Deployment\Strategy
 * @author Mario Mueller <mueller@freshcells.de>
 */
class GitRemoteCacheTask extends AbstractTask implements IsReleaseAware
{
    /**
     * Returns the Title of the Task
     * @return string
     */
    public function getName()
    {
        return 'Deploy via remote cached git repository [built-in]';
    }

    /**
     * Runs the task
     *
     * @return boolean
     * @throws Exception
     * @throws ErrorWithMessageException
     * @throws SkipException
     */
    public function run()
    {
        $overrideRelease = $this->getParameter('overrideRelease', false);

        if ($overrideRelease === true) {
            $releaseToOverride = false;
            $resultFetch = $this->runCommandRemote('ls -ld current | cut -d"/" -f2', $releaseToOverride);
            if ($resultFetch && is_numeric($releaseToOverride)) {
                $this->getConfig()->setReleaseId($releaseToOverride);
            }
        }

        $excludes = array(
            '.git',
            '.svn',
            '.mage',
            '.gitignore',
            '.gitkeep',
            'nohup.out'
        );

        // Look for User Excludes
        $userExcludes = $this->getConfig()->deployment('excludes', array());

        $deployToDirectory = $this->getConfig()->deployment('to');
        if ($this->getConfig()->release('enabled', false) === true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');

            $deployToDirectory = rtrim($this->getConfig()->deployment('to'), '/')
                . '/' . $releasesDirectory
                . '/' . $this->getConfig()->getReleaseId();
            $this->runCommandRemote('mkdir -p ' . $releasesDirectory . '/' . $this->getConfig()->getReleaseId());
        }

        $branch = $this->getParameter('branch');
        $remote = $this->getParameter('remote', 'origin');

        $remoteCacheParam = $this->getParameter('remote_cache', 'shared/git-remote-cache');
        $remoteCacheFolder = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $remoteCacheParam;

        // Don't use -C as git 1.7 does not support it
        $command = 'cd ' . $remoteCacheFolder . ' && /usr/bin/env git fetch ' . $remote;
        $result = $this->runCommandRemote($command);

        $command = 'cd ' . $remoteCacheFolder . ' && /usr/bin/env git checkout ' . $branch;
        $result = $this->runCommandRemote($command) && $result;

        $command = 'cd ' . $remoteCacheFolder . ' && /usr/bin/env git pull --rebase ' . $branch;
        $result = $this->runCommandRemote($command) && $result;

        $excludes = array_merge($excludes, $userExcludes);
        $excludeCmd = '';
        foreach ($excludes as $excludeFile) {
            $excludeCmd .= ' --exclude=' . $excludeFile;
        }

        $command = 'cd ' . $remoteCacheFolder . ' && /usr/bin/env git archive ' . $branch . ' | tar -x -C ' . $deployToDirectory . ' ' . $excludeCmd;
        $result = $this->runCommandRemote($command) && $result;

        if ($result) {
            $this->cleanUpReleases();
        }

        return $result;
    }
}
