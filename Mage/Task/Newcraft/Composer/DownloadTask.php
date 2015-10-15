<?php

namespace Mage\Task\Newcraft\Composer;

use Mage\Task\BuiltIn\Composer\ComposerAbstractTask;
use Mage\Task\Releases\IsReleaseAware;

/**
 * Class ACLPermissions
 * @package Task
 */
class DownloadTask extends ComposerAbstractTask implements IsReleaseAware
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
    $downloadCommand = $this->getReleasesAwareCommand('curl -sS https://getcomposer.org/installer | php');
    return $this->runCommandRemote($downloadCommand);
  }
}