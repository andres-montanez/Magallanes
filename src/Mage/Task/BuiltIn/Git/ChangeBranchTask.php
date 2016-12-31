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

use Mage\Task\SkipException;
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
        $branch = $options['branch'];

        if (!$this->runtime->getVar('git_revert_branch', false)) {
            $cmdGetCurrent = sprintf('%s branch | grep "*"', $options['path']);

            /** @var Process $process */
            $process = $this->runtime->runLocalCommand($cmdGetCurrent);
            if ($process->isSuccessful()) {
                $initialBranch = str_replace('* ', '', trim($process->getOutput()));

                if ($initialBranch == $branch) {
                    throw new SkipException();
                } else {
                    $this->runtime->setVar('git_revert_branch', $initialBranch);
                }
            } else {
                return false;
            }
        } else {
            $branch = $this->runtime->getVar('git_revert_branch');
        }

        $cmdChange = sprintf('%s checkout %s', $options['path'], $branch);

        /** @var Process $process */
        $process = $this->runtime->runLocalCommand($cmdChange);
        return $process->isSuccessful();
    }

    protected function getOptions()
    {
        $config = $this->runtime->getEnvironmentConfig();
        $branch = 'master';
        if (array_key_exists('branch', $config)) {
            $branch = $config['branch'];
        }

        $options = array_merge(
            ['path' => 'git', 'branch' => $branch],
            $this->options
        );

        return $options;
    }
}
