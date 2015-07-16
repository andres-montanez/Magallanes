<?php
namespace Mage\Task\BuiltIn\Deployment\Strategy;

use Mage\Console;
use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;

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
 * @author Mario Mueller <mueller@freshcells.de>, J.Moriarty <moriarty@codefelony.com>
 */
class GitRemoteCacheTask extends BaseStrategyTaskAbstract implements IsReleaseAware
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
        $this->checkOverrideRelease();

        $excludes = $this->getExcludes();
        $excludesListFilePath = $this->getConfig()->deployment('excludes_file', '');

        // If we are working with releases
        $deployToDirectory = $this->getConfig()->deployment('to');
        if ($this->getConfig()->release('enabled', false) === true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');
            $symlink = $this->getConfig()->release('symlink', 'current');

            $currentRelease = false;
            $deployToDirectory = rtrim($deployToDirectory, '/') . '/' . $releasesDirectory . '/' . $this->getConfig()->getReleaseId();
            Console::log('Deploy to ' . $deployToDirectory);
            $resultFetch = $this->runCommandRemote('ls -ld ' . $symlink . ' | cut -d"/" -f2', $currentRelease);

            if ($resultFetch && $currentRelease) {
                // If deployment configuration is rsync, include a flag to simply sync the deltas between the prior release
                // rsync: { copy: yes }
                $rsync_copy = $this->getConfig()->repository('rsync');
                // If copy_tool_rsync, use rsync rather than cp for finer control of what is copied
                if ($rsync_copy && is_array($rsync_copy) && $rsync_copy['copy'] && $this->runCommandRemote('test -d ' . $releasesDirectory . '/' . $currentRelease)) {
                    if (isset($rsync_copy['copy_tool_rsync'])) {
                        $this->runCommandRemote("rsync -a {$this->excludes(array_merge($excludes, $rsync_copy['rsync_excludes']))} "
                                          . "$releasesDirectory/$currentRelease/ $releasesDirectory/{$this->getConfig()->getReleaseId()}");
                    } else {
                        $this->runCommandRemote('cp -R ' . $releasesDirectory . '/' . $currentRelease . ' ' . $releasesDirectory . '/' . $this->getConfig()->getReleaseId());
                    }
                } else {
                    $this->runCommandRemote('mkdir -p ' . $releasesDirectory . '/' . $this->getConfig()->getReleaseId());
                }
            }
        }

        $branch = $this->getConfig()->repository('branch', 'master');
        $remote = $this->getConfig()->repository('remote', 'origin');

	$sharedDirectory = $this->getConfig()->repository('directory', 'shared');
	$cacheDirectory = $this->getConfig()->repository('gitcache', 'git-remote-cache');
        $remoteCacheFolder = rtrim($this->getConfig()->deployment('to'), '/')
            . '/' . $sharedDirectory
            . '/' . $cacheDirectory;
	$this->runCommandRemote('mkdir -p ' . $remoteCacheFolder);

	// Fetch Remote
	$command = $this->getCacheAwareCommand('git fetch ' . $remote);
	$result = $this->runCommandRemote($command);

        if ($result === false) {
            $repository = $this->getConfig()->repository('vcs');
            if ($repository) {
                $command = $this->getCacheAwareCommand('git clone --mirror ' . $repository . ' .');
                $result = $this->runCommandRemote($command);

                $command = $this->getCacheAwareCommand('git fetch ' . $remote);
                $result = $this->runCommandRemote($command);
            }
        }

	// Archive Remote
	$command = $this->getCacheAwareCommand('git archive ' . $branch . ' | tar -x -C ' . $deployToDirectory);
        $result = $this->runCommandRemote($command) && $result;

	return $result;
    }
}
