<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Deployment\Strategy;

use Mage\Console;
use Mage\Task\BuiltIn\Deployment\Strategy\BaseStrategyTaskAbstract;
use Mage\Task\Releases\IsReleaseAware;

/**
 * Task for Sync the Local Code to the Remote Hosts via RSYNC
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class RsyncTask extends BaseStrategyTaskAbstract implements IsReleaseAware
{
    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        if ($this->getConfig()->release('enabled', false) === true) {
            if ($this->getConfig()->getParameter('overrideRelease', false) === true) {
                return 'Deploy via Rsync (with Releases override) [built-in]';
            } else {
                $rsync_copy = $this->getConfig()->deployment("rsync");
                if ($rsync_copy && is_array($rsync_copy) && $rsync_copy['copy']) {
                    return 'Deploy via Rsync (with Releases) [built-in, incremental]';
                } else {
                    return 'Deploy via Rsync (with Releases) [built-in]';
                }
            }
        } else {
            return 'Deploy via Rsync [built-in]';
        }
    }

    /**
     * Syncs the Local Code to the Remote Host
     * @see \Mage\Task\AbstractTask::run()
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
            $deployToDirectory = rtrim($this->getConfig()->deployment('to'), '/')
                               . '/' . $releasesDirectory
                               . '/' . $this->getConfig()->getReleaseId();

            Console::log('Deploy to ' . $deployToDirectory);
            $resultFetch = $this->runCommandRemote('ls -ld ' . $symlink . ' | cut -d"/" -f2', $currentRelease);

            if ($resultFetch && $currentRelease) {
                // If deployment configuration is rsync, include a flag to simply sync the deltas between the prior release
                // rsync: { copy: yes }
                $rsync_copy = $this->getConfig()->deployment('rsync');
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

        // Strategy Flags
        $strategyFlags = $this->getConfig()->deployment('strategy_flags', $this->getConfig()->general('strategy_flags', array()));
        if (isset($strategyFlags['rsync'])) {
            $strategyFlags = $strategyFlags['rsync'];
        } else {
            $strategyFlags = '';
        }

        $command = 'rsync -avz '
                 . $strategyFlags . ' '
                 . '--rsh="ssh ' . $this->getConfig()->getHostIdentityFileOption() . '-p' . $this->getConfig()->getHostPort() . '" '
                 . $this->excludes($excludes) . ' '
                 . $this->excludesListFile($excludesListFilePath) . ' '
                 . $this->getConfig()->deployment('from') . ' '
                 . ($this->getConfig()->deployment('user') ? $this->getConfig()->deployment('user') . '@' : '')
                 . $this->getConfig()->getHostName() . ':' . $deployToDirectory;

        $result = $this->runCommandLocal($command);

        return $result;
    }

    /**
     * Generates the Excludes for rsync
     * @param array $excludes
     * @return string
     */
    protected function excludes(Array $excludes)
    {
        $excludesRsync = '';
        foreach ($excludes as $exclude) {
            $excludesRsync .= ' --exclude=' . escapeshellarg($exclude) . ' ';
        }

        $excludesRsync = trim($excludesRsync);
        return $excludesRsync;
    }

    /**
     * Generates the Exclude from file for rsync
     * @param string $excludesFile
     * @return string
     */
    protected function excludesListFile($excludesFile)
    {
        $excludesListFileRsync = '';
        if (!empty($excludesFile) && file_exists($excludesFile) && is_file($excludesFile) && is_readable($excludesFile)) {
            $excludesListFileRsync = ' --exclude-from=' . $excludesFile;
        }
        return $excludesListFileRsync;
    }
}
