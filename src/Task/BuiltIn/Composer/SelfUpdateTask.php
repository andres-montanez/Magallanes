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

use Symfony\Component\Process\Process;
use Mage\Task\AbstractTask;

/**
 * Composer Task - Self update
 *
 * @author Yanick Witschi <https://github.com/Toflar>
 */
class SelfUpdateTask extends AbstractTask
{
    /**
     * Only used for unit tests.
     *
     * @var \DateTime
     */
    private $dateToCompare;

    /**
     * @return string
     */
    public function getName()
    {
        return 'composer/selfupdate';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return '[Composer] Selfupdate';
    }

    /**
     * @return bool
     */
    public function execute()
    {
        $options = $this->getOptions();
        $days = $options['days'];
        $versionCommand = sprintf('%s --version', $options['path']);

        /** @var Process $process */
        $process = $this->runtime->runCommand(trim($versionCommand));

        if (!$process->isSuccessful()) {
            return false;
        }

        $dt = $this->extractDate($process->getOutput());

        // Date could not be extracted, always run update
        if (false === $dt) {
            return $this->selfUpdate($options);
        }

        // Check age
        if (!$this->isOlderThan($dt, $days)) {
            return true;
        }

        return $this->selfUpdate($options);
    }

    /**
     * This tasks obviously always takes the current date to compare the age
     * of the composer.phar. This method is used for unit test purposes
     * only.
     *
     * @param \DateTime $dateToCompare
     */
    public function setDateToCompare(\DateTime $dateToCompare)
    {
        $this->dateToCompare = $dateToCompare;
    }

    /**
     * @param \DateTime $dt
     * @param int       $days
     *
     * @return bool
     */
    protected function isOlderThan(\DateTime $dt, $days)
    {
        $dtComp = new \DateTime($days . ' days ago');

        if (null !== $this->dateToCompare) {
            $dtComp = $this->dateToCompare;
        }

        return $dt < $dtComp;
    }

    /**
     * @param array $options
     *
     * @return bool
     */
    protected function selfUpdate(array $options)
    {
        $selfupdateCommand = sprintf('%s selfupdate %s', $options['path'], $options['release']);

        /** @var Process $process */
        $process = $this->runtime->runCommand(trim($selfupdateCommand));

        return $process->isSuccessful();
    }

    /**
     * @param string $output
     *
     * @return \DateTime|false
     */
    protected function extractDate($output)
    {
        $date = substr($output, -19);

        return \DateTime::createFromFormat('Y-m-d H:i:s', $date);
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        $options = array_merge(
            ['path' => 'composer', 'release' => '', 'days' => 30],
            $this->runtime->getMergedOption('composer'),
            $this->options
        );

        return $options;
    }
}
