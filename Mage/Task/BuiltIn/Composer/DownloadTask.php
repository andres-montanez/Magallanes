<?php

namespace Mage\Task\BuiltIn\Composer;

use Mage\Task\AbstractTask;

/**
 * Class ACLPermissions
 * @package Task
 */
class ComposerDownload extends AbstractTask
{
  /**
   * @return string
   */
  public function getName()
  {
    return 'Composer - Download composer';
  }

  /**
   * @return bool
   */
  public function run()
  {
    return $this->runCommandRemote('curl -sS https://getcomposer.org/installer | php');
  }
}