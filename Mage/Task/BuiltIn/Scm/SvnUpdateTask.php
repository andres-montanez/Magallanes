<?php
namespace Mage\Task\BuiltIn\Scm;

use Mage\Task\AbstractTask;
use Mage\Task\ErrorWithMessageException;


/**
 * Task for updating SVN Repositories
 * @author Lance Bailey
 *
 */
class SvnUpdateTask extends AbstractTask {
	/**
	 * Name of the Task
	 * @var string
	 */
	private $name = 'SCM Update (SVN) [built-in]';
	
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
		if($this->getConfig()->general('scm') !== 'svn')
			throw new ErrorWithMessageException("This task should only be used for SVN, Please update your config");
	}
	
	/**
	 * Updates the Working Copy
	 * @see \Mage\Task\AbstractTask::run()
	 */
	public function run()
	{
		$command = 'svn update';

		$result = $this->runCommandLocal($command);
		$this->getConfig()->reload();
	
		return $result;
	}
}

?>