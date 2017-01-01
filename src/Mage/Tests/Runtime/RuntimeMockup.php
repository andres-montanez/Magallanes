<?php
namespace Mage\Tests\Runtime;

use Mage\Runtime\Runtime;
use Symfony\Component\Process\Process;

class RuntimeMockup extends Runtime
{
    protected $ranCommands = [];

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
}