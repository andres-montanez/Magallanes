<?php

/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Command;

use Mage\MageApplication;
use Mage\Utils;
use Mage\Runtime\Runtime;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;

/**
 * Abstract base class for Magallanes Commands
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
abstract class AbstractCommand extends Command
{
    protected int $statusCode = 0;
    protected Runtime $runtime;

    /**
     * Set the Runtime configuration
     */
    public function setRuntime(Runtime $runtime): self
    {
        $this->runtime = $runtime;
        return $this;
    }

    /**
     * Logs a message
     */
    public function log(string $message, string $level = LogLevel::DEBUG): void
    {
        $this->runtime->log($message, $level);
    }

    /**
     * Get the Human friendly Stage name
     */
    protected function getStageName(): string
    {
        $utils = new Utils();
        return $utils->getStageName($this->runtime->getStage());
    }

    /**
     * Requires the configuration to be loaded
     */
    protected function requireConfig(): void
    {
        $app = $this->getApplication();
        if ($app instanceof MageApplication) {
            $app->configure();
        }
    }
}
