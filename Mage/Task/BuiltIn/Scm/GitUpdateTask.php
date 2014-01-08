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
use Mage\Task\ErrorWithMessageException;

/**
 * Task for Updating a Working Copy
 * Taken and updated from original Mage\Task\BuiltIn\Scm\UpdateTask
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class GitUpdateTask extends AbstractTask {
	/**
	 * Name of the Task
	 * @var string
	 */
	private $name = 'SCM Update (GIT) [built-in]';
	
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
		if($this->getConfig()->general('scm') !== 'git')
			throw new ErrorWithMessageException("This task should only be used for GIT, Please update your config");
	}
	
	/**
	 * Updates the Working Copy
	 * @see \Mage\Task\AbstractTask::run()
	 */
	public function run()
	{
		$command = 'git pull';
	
		$result = $this->runCommandLocal($command);
		$this->getConfig()->reload();
	
		return $result;
	}
}

?>