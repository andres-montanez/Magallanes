<?php

namespace Mage\Task\Newcraft\Deployment\Strategy;

use Mage\Task\BuiltIn\Deployment\Strategy\BaseStrategyTaskAbstract;
use Mage\Task\Releases\IsReleaseAware;
use Mage\Console;
use Exception;

/**
 * Task for downloading the current commit from Github as the Deployed Code
 *
 * @author Bart Swaalf <bart.swaalf@newcraftgroup.com>
 */
class GithubDownloadTask extends BaseStrategyTaskAbstract implements IsReleaseAware
{
    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        return 'Deploy via Github Download [newcraft]';
    }

    /**
     * Rebases the Git Working Copy as the Deployed Code
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $this->checkOverrideRelease();

        if ($this->getConfig()->release('enabled', false) === true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');

            $deployToDirectory = rtrim($this->getConfig()->deployment('to'), '/')
                . '/' . $releasesDirectory
                . '/' . $this->getConfig()->getReleaseId();
            $this->runCommandRemote('mkdir -p ' . $deployToDirectory);
        }

        //read username from git config or ask user
        $gitUsername = exec('git config user.email');

        Console::output('');
        Console::output('<white>GitHub username</white>: ', 3, 0);
        if(empty($gitUsername)){
            $gitUsername = Console::readInput();
        } else {
            Console::output($gitUsername, 0, 0);
        }
        Console::output('', 0);

        //ask user for password
        Console::output('<white>GitHub password</white>: ', 3, 0);
        $gitPassword = Console::readInputSilent();
        Console::output('[hidden]',0,1);

        $gitRemoteUrl = exec('git config --get remote.origin.url');
        if(0 === strpos($gitRemoteUrl,'git@github.com')){
            $projectName = substr($gitRemoteUrl,strpos($gitRemoteUrl,':')+1,-4);
        } elseif(0 === strpos($gitRemoteUrl,'https://github.com')){
            $projectName = substr($gitRemoteUrl,strpos($gitRemoteUrl,'/',8)+1,-4);
        } elseif(empty($gitRemoteUrl)) {
            throw new Exception('cannot determine remote url.');
        } elseif(false === strpos($gitRemoteUrl,'github.com' === 0)) {
            throw new Exception('repository not hosted on github, cannot use this strategy.');
        } else {
            throw new Exception('cannot parse remote url.');
        }

        $downloadUrl = 'https://github.com/'.$projectName.'/archive';
        $revision = exec('git rev-parse HEAD');

        Console::output('Cont... <purple>download from Github [newcraft]</purple>.... ', 2, 0);

        Console::disableLogging();
        $downloadCommand = $this->getReleasesAwareCommand('curl --silent --location --user \''.$gitUsername.':'.$gitPassword.'\' \''.$downloadUrl.'/'.$revision.'.tar.gz\' | gunzip | tar xf - --strip 1');
        $this->runCommandRemote($downloadCommand.' && ls -Al | wc -l',$fileCount);
        Console::enableLogging();

        return (int) $fileCount > 0;
    }
}
