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

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;

/**
 * Task for using Git Working Copy as the Deployed Code
 *
 * @author Oscar Reales <oreales@gmail.com>
 */
class GitRebaseTask extends AbstractTask implements IsReleaseAware
{
	/**
	 * (non-PHPdoc)
	 * @see \Mage\Task\AbstractTask::getName()
	 */
    public function getName()
    {
        return 'Deploy via Git Rebase [built-in]';
    }

    /**
     * Rebases the Git Working Copy as the Deployed Code
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {        
    	$branch = $this->getParameter('branch', 'master');
    	$remote = $this->getParameter('remote', 'origin');
                
    	// Fetch Remote
        $command = 'git fetch ' . $remote;
        $result = $this->runCommandRemote($command);

        // Checkout
        $command = 'git checkout ' . $branch;
        $result = $this->runCommandRemote($command) && $result;

        // Check Working Copy status
        $stashed = false;
        $status = '';
        $command = 'git checkout ' . $branch;
        $result = $this->runCommandRemote($command) && $result;

        // Stash if Working Copy is not clean
        if(!$status) {
        	$stashResult = '';
        	$command = 'git stash';
        	$result = $this->runCommandRemote($command, $stashResult) && $result;
        	if($stashResult != "No local changes to save") {
                $stashed = true;
            }
        }

        // Rebase
        $command = 'git rebase ' . $remote . '/' . $branch;
        $result = $this->runCommandRemote($command) && $result;

        // If Stashed, restore.
        if ($stashed) {
        	$command = 'git stash pop';
        	$result = $this->runCommandRemote($command) && $result;
        }

        return $result;
    }
}