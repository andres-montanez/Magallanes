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

use Mage\Task\ErrorWithMessageException;

use Mage\Task\AbstractTask;
use Mage\Task\SkipException;

/**
 * Task for Updating a Working Copy
 * This class has been updated to support fallback of original task usage
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class UpdateTask extends AbstractTask
{
	/**
	 * Name of the Task
	 * @var string
	 */
    public $name = 'SCM Update [built-in]';
    
    /**
     * The repo specific class for updating
     * @var object
     */
    private $repoClass;

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
    	//Initialize the Defined Repo Type
    	$this->scm = $this->getConfig()->general('scm');
    	switch ($this->scm){
    		case 'git':
    			$this->repoClass =  new GitUpdateTask($this->getConfig(),$this->inRollback(),$this->getStage(),$this->parameters);
    			break;
    		case 'svn':
    			$this->repoClass = new SvnUpdateTask($this->getConfig(),$this->inRollback(),$this->getStage(),$this->parameters);
    			break;
    		default:
    			throw new ErrorWithMessageException("Unsupported Built-in Repository type: " . $this->scm);
    			break;
    	}
    	$this->repoClass->init();
    }

    /**
     * Updates the Working Copy
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        return $this->repoClass->init();
    }
}