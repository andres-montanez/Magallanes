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

use Mage\Runtime\Runtime;
use Psr\Log\LoggerInterface;
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
     * @var Runtime Current Runtime instance
     */
    protected $runtime;

    /**
     * @var LoggerInterface|null The instance of the logger, it's optional
     */
    private $logger = null;

    /**
     * Configure the Command and create the Runtime configuration
     *
     * @param array $configuration Magallanes configuration
     * @return AbstractCommand
     */
    public function setConfiguration($configuration)
    {
        $this->runtime = new Runtime();
        $this->runtime->setConfiguration($configuration);
        $this->runtime->setLogger($this->logger);

        return $this;
    }

    /**
     * Sets the logger
     *
     * @param LoggerInterface $logger
     * @return AbstractCommand
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Logs a message, if logger is valid instance
     *
     * @param string $message
     * @param string $level
     * @return AbstractCommand
     */
    public function log($message, $level = LogLevel::DEBUG)
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->log($level, $message);
        }

        return $this;
    }
}
