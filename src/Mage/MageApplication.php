<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage;

use Mage\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Yaml;
use Mage\Runtime\Exception\RuntimeException;

/**
 * The Console Application for launching the Mage command in a standalone instance
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class MageApplication extends Application
{
    private $configuration;
    protected $logger;

    /**
     * Configure the Magallanes Application
     *
     * @param $file string The YAML file from which to read the configuration
     *
     * @throws RuntimeException
     */
    public function configure($file)
    {
        $config = Yaml::parse(file_get_contents($file));
        if (array_key_exists('magephp', $config)) {
            $this->configuration = $config['magephp'];

            if (array_key_exists('log_dir', $this->configuration)) {
                $logfile = sprintf('%s/%s.log', $this->configuration['log_dir'], date('Ymd_His'));
                $this->configuration['log_file'] = $logfile;

                $this->logger = new Logger('magephp');
                $this->logger->pushHandler(new StreamHandler($logfile));
            }
        } else {
            throw new RuntimeException(sprintf('The file "%s" does not have a valid Magallanes configuration.', $file));
        }
    }

    /**
     * Run the Application
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->loadBuiltInCommands();

        parent::run();
    }

    /**
     * Loads the BuiltIn Commands
     */
    protected function loadBuiltInCommands()
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/Command/BuiltIn')->name('*Command.php');

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $class = substr('\\Mage\\Command\\BuiltIn\\' . str_replace('/', '\\', $file->getRelativePathname()), 0, -4);
            if (class_exists($class)) {
                $command = new $class();

                if ($command instanceof AbstractCommand) {
                    $command->setLogger($this->logger);
                    $command->setConfiguration($this->configuration);
                    $this->add($command);
                }
            }
        }
    }
}
