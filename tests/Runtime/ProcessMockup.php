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

use Symfony\Component\Process\Process;

class ProcessMockup extends Process
{
    public $forceFail = [];
    protected $commandline;
    protected $timeout;
    protected $success = true;

    public function __construct($commandline, string $cwd = null, array $env = null, $input = null, ?float $timeout = 60)
    {
        $this->commandline = $commandline;
    }

    public function setTimeout(?float $timeout): static
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function run(callable $callback = null, array $env = array()): int
    {
        if (in_array($this->commandline, $this->forceFail)) {
            $this->success = false;
        }

        if ($this->commandline == 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@host1 "readlink -f /var/www/test/current"') {
            $this->success = false;
        }

        if ($this->commandline == 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@host3 "ls -1 /var/www/test/releases"') {
            $this->success = false;
        }

        if ($this->commandline == 'scp -P 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /tmp/mageXYZ tester@host4:/var/www/test/releases/1234567890/mageXYZ') {
            $this->success = false;
        }

        if ($this->commandline == 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@hostdemo2 "ls -1 /var/www/test/releases"') {
            $this->success = false;
        }

        if (!$this->success) {
            return 10;
        }

        return 0;
    }

    public function isSuccessful(): bool
    {
        return $this->success;
    }

    public function getErrorOutput(): string
    {
        return '';
    }

    public function getOutput(): string
    {
        if ($this->commandline == 'git branch | grep "*"') {
            return '* master';
        }

        // Make composer build 20 days old
        if ($this->commandline == 'composer --version') {
            $date = date('Y-m-d H:i:s', strtotime('now -20 days'));
            return 'Composer version 1.3.0 ' . $date;
        }

        // Make ./composer build 20 days old
        if ($this->commandline == './composer --version') {
            $date = date('Y-m-d H:i:s', strtotime('now -20 days'));
            return 'Do not run Composer as root/super user! See https://getcomposer.org/root for details' . PHP_EOL . 'Composer version 1.3.0 ' . $date;
        }

        // Make composer.phar build 90 days old
        if ($this->commandline == 'composer.phar --version') {
            $date = date('Y-m-d H:i:s', strtotime('now -90 days'));
            return 'Composer version 1.3.0 ' . $date;
        }

        // Make php composer has wrong output
        if ($this->commandline == 'php composer --version') {
            return 'Composer version 1.3.0 no build';
        }

        if ($this->commandline == 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "ls -1 /var/www/test/releases"') {
            return implode("\n", ['20170101015110', '20170101015111', '20170101015112', '20170101015113', '20170101015114', '20170101015115', '20170101015116', '20170101015117']);
        }

        if ($this->commandline == 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "readlink -f /var/www/test/current"') {
            return '/var/www/test/releases/20170101015117';
        }

        if ($this->commandline == 'ssh -p 202 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "ls -1 /var/www/test/releases"') {
            return implode("\n", ['20170101015110', '20170101015111', '20170101015112', '20170101015113', '20170101015114', '20170101015115', '20170101015116', '20170101015117']);
        }

        if ($this->commandline == 'ssh -p 202 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "readlink -f /var/www/test/current"') {
            return '/var/www/test/releases/20170101015117';
        }

        if ($this->commandline == 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@host1 "ls -1 /var/www/test/releases"') {
            return implode("\n", ['20170101015110', '20170101015111', '20170101015112', '20170101015113', '20170101015114', '20170101015115', '20170101015116', '20170101015117']);
        }

        if ($this->commandline == 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@hostdemo1 "ls -1 /var/www/test/releases"') {
            return implode("\n", ['20170101015110', '20170101015111', '20170101015112', '20170101015113', '20170101015114', '20170101015115', '20170101015116', '20170101015117']);
        }

        if ($this->commandline == 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@hostdemo3 "ls -1 /var/www/test/releases"') {
            return implode("\n", ['20170101015110', '20170101015111', '20170101015112', '20170101015113', '20170101015114', '20170101015116', '20170101015117']);
        }

        if ($this->commandline == 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@host2 "ls -1 /var/www/test/releases"') {
            return '';
        }

        return '';
    }
}
