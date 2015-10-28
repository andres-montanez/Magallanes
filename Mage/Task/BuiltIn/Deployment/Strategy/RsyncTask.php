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
use Mage\Task\BuiltIn\Releases\ListTask;

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
        if ($this->getConfig()->getParameter('dry-run', false) === true) {
            return 'Dry-run via Rsync [built-in]';
        }
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
     * @param bool|FALSE $dryRun
     * @return string
     */
    protected function getDeployCommand($dryRun = false)
    {
        $deployToDirectory = $this->getDeployToDirectory($dryRun);

        $excludes = $this->getExcludes();
        $excludesListFilePath = $this->getConfig()->deployment('excludes_file', '');

        $strategyFlags = $this->getConfig()->deployment('strategy_flags', $this->getConfig()->general('strategy_flags', array()));
        if (isset($strategyFlags['rsync'])) {
            $strategyFlags = $strategyFlags['rsync'];
        } else {
            $strategyFlags = '';
        }

        // Add two flags if we are in Dry run.
        if ($dryRun === true) {
            $dryRunFlags = array(
              '--dry-run',
              '--itemize-changes',
              '--omit-dir-times'
            );
            $strategyFlags = str_replace($dryRunFlags, '', $strategyFlags);
            $strategyFlags .= implode(' ', $dryRunFlags);
        }

        $command = 'rsync -avz '
          . $strategyFlags . ' '
          . '--rsh="ssh ' . $this->getConfig()->getHostIdentityFileOption() . '-p' . $this->getConfig()->getHostPort() . '" '
          . $this->excludes($excludes) . ' '
          . $this->excludesListFile($excludesListFilePath) . ' '
          . $this->getConfig()->deployment('from') . ' '
          . ($this->getConfig()->deployment('user') ? $this->getConfig()->deployment('user') . '@' : '')
          . $this->getConfig()->getHostName() . ':' . $deployToDirectory;

        return $command;
    }

    /**
     * @param bool|FALSE $dryRun
     * @return string
     */
    protected function getDeployToDirectory($dryRun = false)
    {
        $deployTo = rtrim($this->getConfig()->deployment('to'), '/');

        // If releases aren't enabled, deploy dir is simply deploy to config.
        if ($this->getConfig()->release('enabled', false) !== true) {
            return $deployTo;
        }

        // If dryrun, check from the latest release.
        if ($dryRun === true) {
            $release = ListTask::getCurrentRelease();
        }
        else {
            $release = $this->getConfig()->getReleaseId();
        }
        $releasesDirectory = $this->getConfig()->release('directory', 'releases');
        $deployToDirectory = $deployTo . '/' . $releasesDirectory . '/' . $release;

        return $deployToDirectory;
    }

    /**
     * @return bool
     */
    public function dryRun()
    {
        Console::output('');
        if ($this->getConfig()->release('enabled', false) === true) {
            $currentRelease = ListTask::getCurrentRelease();
            Console::output('Checking files on the latest release <purple>'.$currentRelease.'</purple> on host <purple>'.$this->getConfig()->getHostName().'</purple>...', 2, 1);
        }
        else {
            Console::output('Checking files on host <purple>'.$this->getConfig()->getHostName().'</purple>...', 2, 1);
        }

        // Get deploy command with dryRun option (true).
        $command = $this->getDeployCommand(true);
        $result = $this->runCommandLocal($command, $output);

        // Put each line in an array (CHR(10) = Carriage return).
        $lines = explode(CHR(10), $output);
        if (count($lines)) {
            Console::output('');
            Console::output('<yellow>---------- Dry run result ------------</yellow>', 2, 1);
            foreach ($lines as $key => $line) {
                Console::output($line, 2, 1);
            }
            Console::output('<yellow>---------- End Dry run ---------------</yellow></yellow>', 2, 1);
        }
        return $result;

    }

    /**
     * Syncs the Local Code to the Remote Host
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $excludes = $this->getExcludes();

        // Launch dry run mode
        if ($this->getConfig()->getParameter('dry-run') === true) {
            return $this->dryRun();
        }

        $this->checkOverrideRelease();

        // If we are working with releases
        if ($this->getConfig()->release('enabled', false) === true) {

            $releasesDirectory = $this->getConfig()->release('directory', 'releases');
            $symlink = $this->getConfig()->release('symlink', 'current');

            $currentRelease = false;

            $deployToDirectory = $this->getDeployToDirectory();

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

        $command = $this->getDeployCommand();
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
