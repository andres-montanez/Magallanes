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
use Symfony\Component\Console\Helper\Table;
use Mage\Command\AbstractCommand;

/**
 * Command for listing all the Environments
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class EnvironmentsCommand extends AbstractCommand
{
    /**
     * Configure the Command
     */
    protected function configure(): void
    {
        $this
            ->setName('config:environments')
            ->setDescription('List all Magallanes configured Environments');
    }

    /**
     * Execute the Command
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->requireConfig();

        $output->writeln('Starting <fg=blue>Magallanes</>');
        $output->writeln('');

        $table = new Table($output);
        $table->setHeaders(['Environment', 'User', 'Branch', 'Hosts']);

        $configuration = $this->runtime->getConfigOption('environments');
        foreach ($configuration as $environment => $config) {
            $row = [$environment];

            $row[] = (isset($config['user']) ? $config['user'] : '-');
            $row[] = (isset($config['branch']) ? $config['branch'] : '-');
            $row[] = (isset($config['hosts']) ? implode(PHP_EOL, $config['hosts']) : '-');

            $table->addRow($row);
        }

        $table->render();

        $output->writeln('');
        $output->writeln('Finished <fg=blue>Magallanes</>');

        return self::SUCCESS;
    }
}
