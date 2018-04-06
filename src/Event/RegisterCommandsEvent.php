<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Event;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\EventDispatcher\Event;

class RegisterCommandsEvent extends Event
{
    const EVENT_NAME = 'mage.event.register_commands';

    /**
     * @var Command[]
     */
    private $commands = [];

    /**
     * @return Command[]
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * @param array $commands
     *
     * @return RegisterCommandsEvent
     */
    public function setCommands(array $commands)
    {
        $this->commands = [];

        foreach ($commands as $command) {
            $this->addCommand($command);
        }

        return $this;
    }

    /**
     * @param Command $command
     */
    public function addCommand(Command $command)
    {
        $this->commands[] = $command;
    }
}
