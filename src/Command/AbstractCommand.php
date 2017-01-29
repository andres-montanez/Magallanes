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
    /**
     * @var int
     */
    protected $statusCode = 0;

    /**
     * @var Runtime Current Runtime instance
     */
    protected $runtime;

    /**
     * Set the Runtime configuration
     *
     * @param Runtime $runtime Runtime container
     * @return AbstractCommand
     */
    public function setRuntime(Runtime $runtime)
    {
        $this->runtime = $runtime;

        return $this;
    }

    /**
     * Logs a message
     *
     * @param string $message
     * @param string $level
     */
    public function log($message, $level = LogLevel::DEBUG)
    {
        $this->runtime->log($message, $level);
    }

    /**
     * Get the Human friendly Stage name
     *
     * @return string
     */
    protected function getStageName()
    {
        $utils = new Utils();
        return $utils->getStageName($this->runtime->getStage());
    }

    /**
     * Requires the configuration to be loaded
     */
    protected function requireConfig()
    {
        $app = $this->getApplication();
        if ($app instanceof MageApplication) {
            $app->configure();
        }
    }
}
