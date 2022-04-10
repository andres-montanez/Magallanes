<?php

/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Command\BuiltIn\Config;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Mage\Command\AbstractCommand;

/**
 * Command for Dumping the Configuration
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class DumpCommand extends AbstractCommand
{
    /**
     * Configure the Command
     */
    protected function configure(): void
    {
        $this
            ->setName('config:dump')
            ->setDescription('Dumps the Magallanes configuration');
    }

    /**
     * Execute the Command
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->requireConfig();

        $output->writeln('Starting <fg=blue>Magallanes</>');
        $output->writeln('');

        $output->writeln(sprintf('<comment>%s</comment>', var_export($this->runtime->getConfiguration(), true)));

        $output->writeln('');
        $output->writeln('Finished <fg=blue>Magallanes</>');

        return self::SUCCESS;
    }
}
