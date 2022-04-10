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
    protected $ranCommandTimeouts = [];
    protected $forceFail = [];

    public function getRanCommands()
    {
        return $this->ranCommands;
    }

    public function getRanCommandTimeoutFor($cmd)
    {
        return isset($this->ranCommandTimeouts[$cmd]) ? $this->ranCommandTimeouts[$cmd] : null;
    }

    /**
     * Generate the Release ID
     */
    public function generateReleaseId(): Runtime
    {
        $this->setReleaseId('1234567890');
        return $this;
    }

    /**
     * Execute a command locally
     */
    public function runLocalCommand(string $cmd, int $timeout = 120): Process
    {
        $this->ranCommands[] = $cmd;
        $this->ranCommandTimeouts[$cmd] = $timeout;

        $process = new ProcessMockup($cmd);
        $process->forceFail = $this->forceFail;
        $process->setTimeout($timeout);
        $process->run();

        return $process;
    }

    /**
     * Gets a Temporal File name
     */
    public function getTempFile(): string
    {
        return '/tmp/mageXYZ';
    }

    /**
     * Allows to set an invalid environments
     *
     * @param string $environment
     * @return Runtime
     */
    public function setInvalidEnvironment($environment): Runtime
    {
        $this->environment = $environment;
        return $this;
    }

    public function forceFail($cmd): void
    {
        $this->forceFail[] = $cmd;
    }
}
