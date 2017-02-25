<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\Composer;

use Mage\Task\Exception\SkipException;
use Symfony\Component\Process\Process;
use Mage\Task\AbstractTask;
use DateTime;

/**
 * Composer Task - Self update
 *
 * @author Yanick Witschi <https://github.com/Toflar>
 */
class SelfUpdateTask extends AbstractTask
{
    public function getName()
    {
        return 'composer/self-update';
    }

    public function getDescription()
    {
        return '[Composer] Self Update';
    }

    public function execute()
    {
        $options = $this->getOptions();
        $cmdVersion = sprintf('%s --version', $options['path']);
        /** @var Process $process */
        $process = $this->runtime->runCommand(trim($cmdVersion));
        if (!$process->isSuccessful()) {
            return false;
        }

        $buildDate = $this->getBuildDate($process->getOutput());
        if (!$buildDate instanceof DateTime) {
            return false;
        }

        $compareDate = $this->getCompareDate();
        if ($buildDate >= $compareDate) {
            throw new SkipException();
        }

        $cmdUpdate = sprintf('%s self-update', $options['path']);
        /** @var Process $process */
        $process = $this->runtime->runCommand(trim($cmdUpdate));

        return $process->isSuccessful();
    }

    protected function getBuildDate($output)
    {
        $buildDate = null;
        $output = explode(PHP_EOL, $output);
        foreach ($output as $row) {
            if (strpos($row, 'Composer version ') === 0) {
                $buildDate = DateTime::createFromFormat('Y-m-d H:i:s', substr(trim($row), -19));
            }
        }

        return $buildDate;
    }

    protected function getCompareDate()
    {
        $options = $this->getOptions();
        $compareDate = new DateTime();
        $compareDate->modify(sprintf('now -%d days', $options['days']));
        return $compareDate;
    }

    protected function getOptions()
    {
        $options = array_merge(
            ['path' => 'composer', 'days' => 60],
            $this->runtime->getMergedOption('composer'),
            $this->options
        );

        return $options;
    }
}
