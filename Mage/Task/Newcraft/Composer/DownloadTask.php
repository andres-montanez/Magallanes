<?php

namespace Mage\Task\Newcraft\Composer;

use Mage\Task\BuiltIn\Composer\ComposerAbstractTask;

/**
 * Class ACLPermissions
 * @package Task
 */
class DownloadTask extends ComposerAbstractTask
{
  /**
   * @return string
   */
  public function getName()
  {
    return 'Download composer [newcraft]';
  }

  /**
   * @return bool
   */
  public function run()
  {
    return $this->runCommandRemote('curl -sS https://getcomposer.org/installer | php');
  }
}