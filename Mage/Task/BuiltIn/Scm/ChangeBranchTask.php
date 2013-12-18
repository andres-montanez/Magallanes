<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Scm;

use Mage\Task\AbstractTask;
use Mage\Task\SkipException;
use Mage\Task\ErrorWithMessageException;

use Exception;

/**
 * Task for Changing the Branch of the SCM
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class ChangeBranchTask extends AbstractTask
{
	/**
	 * Branch the executiong began with
	 * @var string
	 */
	protected static $startingBranch = 'master';

	/**
	 * Name of the Task
	 * @var string
	 */
    private $name = 'SCM Changing branch [built-in]';

    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::init()
     */
    public function init()
    {
    	$scmType = $this->getConfig()->general('scm');

        switch ($scmType) {
            case 'git':
                $this->name = 'SCM Changing branch (GIT) [built-in]';
                break;
        }
    }

    /**
     * Changes the Branch of the SCM
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
    	$scmConfig = $this->getConfig()->general('scm', array());
        $type = (isset($scmConfig['type']) && $this->getConfig()->deployment('strategy') != 'gitClone')  ? $scmConfig['type'] : null;
        switch ($type) {
            case 'git':
            	if ($this->getParameter('_changeBranchRevert', false)) {
            		$this->runJobLocal('git checkout ' . self::$startingBranch);
            	} else {
                    $j = $this->runJobLocal('git branch | grep \'*\' | cut -d\' \' -f 2');
                    $currentBranch = end($j->stdout);

                    $scmData = $this->getConfig()->deployment('scm', []);

            		if ($j->success() && isset($scmData['branch']) && $scmData['branch'] != $currentBranch) {
        				$command = 'git branch | grep \'' . $scmData['branch'] . '\' | tr -s \' \' | sed \'s/^[ ]//g\'';
        				$isBranchTracked = end($this->runJobLocal($command)->stdout);

        				if (empty($isBranchTracked)) {
        					throw new ErrorWithMessageException('The branch <purple>' . $scmData['branch'] . '</purple> must be tracked.');
        				}

        				$this->runJobLocal('git checkout ' . $scmData['branch']);

        				self::$startingBranch = $currentBranch;
            		} else {
            			throw new SkipException;
            		}
            	}
                break;

            default:
                throw new SkipException;
                break;
        }

        $this->getConfig()->reload();

        return $this->isAllOk();
    }
}