<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Command\BuiltIn;

use Mage\Mage;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Mage\Command\AbstractCommand;

/**
 * Version Command, return the current version of Magallanes
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class VersionCommand extends AbstractCommand
{
    /**
     * Configure the Command
     */
    protected function configure()
    {
        $this
            ->setName('version')
            ->setDescription('Get the version of Magallanes')
        ;
    }

    /**
     * Executes the Command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('Magallanes v%s [%s]', Mage::VERSION, Mage::CODENAME));

        return 0;
    }
}
