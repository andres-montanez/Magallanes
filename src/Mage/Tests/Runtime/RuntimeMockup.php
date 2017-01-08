<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests\Runtime;

use Mage\Runtime\Runtime;
use Symfony\Component\Process\Process;

class RuntimeMockup extends Runtime
{
    protected $ranCommands = [];
    protected $forceFail = [];

    public function getRanCommands()
    {
        return $this->ranCommands;
    }

    /**
     * Generate the Release ID
     *
     * @return Runtime
     */
    public function generateReleaseId()
    {
        $this->setReleaseId('1234567890');
        return $this;
    }

    /**
     * Execute a command locally
     *
     * @param string $cmd Command to execute
     * @param int $timeout Seconds to wait
     * @return Process
     */
    public function runLocalCommand($cmd, $timeout = 120)
    {
        $this->ranCommands[] = $cmd;

        $process = new ProcessMockup($cmd);
        $process->forceFail = $this->forceFail;
        $process->setTimeout($timeout);
        $process->run();

        return $process;
    }

    /**
     * Gets a Temporal File name
     *
     * @return string
     */
    public function getTempFile()
    {
        return '/tmp/mageXYZ';
    }

    /**
     * Allows to set an invalid environments
     *
     * @param string $environment
     * @return Runtime
     */
    public function setInvalidEnvironment($environment)
    {
        $this->environment = $environment;
        return $this;
    }

    public function forceFail($cmd)
    {
        $this->forceFail[] = $cmd;
    }
}
