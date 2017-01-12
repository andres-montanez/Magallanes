<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\Git;

use Mage\Task\Exception\SkipException;
use Symfony\Component\Process\Process;
use Mage\Task\AbstractTask;

/**
 * Git Task - Checkout Branch
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class ChangeBranchTask extends AbstractTask
{
    public function getName()
    {
        return 'git/change-branch';
    }

    public function getDescription()
    {
        $options = $this->getOptions();
        $branch = $options['branch'];

        if ($this->runtime->getVar('git_revert_branch', false)) {
            $branch = $this->runtime->getVar('git_revert_branch');
        }

        return sprintf('[Git] Change Branch (%s)', $branch);
    }

    public function execute()
    {
        $options = $this->getOptions();
        $branch = $this->runtime->getVar('git_revert_branch', false);

        if ($branch === false) {
            $cmdGetCurrent = sprintf('%s branch | grep "*"', $options['path']);

            /** @var Process $process */
            $process = $this->runtime->runLocalCommand($cmdGetCurrent);
            if (!$process->isSuccessful()) {
                return false;
            }

            $currentBranch = str_replace('* ', '', trim($process->getOutput()));
            if ($currentBranch == $options['branch']) {
                throw new SkipException();
            }

            $branch = $options['branch'];
            $this->runtime->setVar('git_revert_branch', $currentBranch);
        }

        $cmdChange = sprintf('%s checkout %s', $options['path'], $branch);

        /** @var Process $process */
        $process = $this->runtime->runLocalCommand($cmdChange);
        return $process->isSuccessful();
    }

    protected function getOptions()
    {
        $branch = $this->runtime->getEnvOption('branch', 'master');
        $options = array_merge(
            ['path' => 'git', 'branch' => $branch],
            $this->options
        );

        return $options;
    }
}
