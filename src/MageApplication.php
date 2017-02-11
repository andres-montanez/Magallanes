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
use Mage\Runtime\Runtime;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use ReflectionClass;
use Mage\Runtime\Exception\RuntimeException;

/**
 * The Console Application for launching the Mage command in a standalone instance
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class MageApplication extends Application
{
    protected $runtime;
    protected $file;

    /**
     * @param string $file The YAML file from which to read the configuration
     */
    public function __construct($file)
    {
        parent::__construct('Magallanes', Mage::VERSION);

        $this->file = $file;
        $dispatcher = new EventDispatcher();
        $this->setDispatcher($dispatcher);

        $dispatcher->addListener(ConsoleEvents::EXCEPTION, function (ConsoleExceptionEvent $event) {
            $output = $event->getOutput();
            $command = $event->getCommand();
            $output->writeln(sprintf('Oops, exception thrown while running command <info>%s</info>', $command->getName()));
            $exitCode = $event->getExitCode();
            $event->setException(new \LogicException('Caught exception', $exitCode, $event->getException()));
        });

        $this->runtime = $this->instantiateRuntime();
        $this->loadBuiltInCommands();
    }

    /**
     * Configure the Magallanes Application
     *
     * @throws RuntimeException
     */
    public function configure()
    {
        if (!file_exists($this->file) || !is_readable($this->file)) {
            throw new RuntimeException(sprintf('The file "%s" does not exists or is not readable.', $this->file));
        }

        try {
            $parser = new Parser();
            $config = $parser->parse(file_get_contents($this->file));
        } catch (ParseException $exception) {
            throw new RuntimeException(sprintf('Error parsing the file "%s".', $this->file));
        }

        if (array_key_exists('magephp', $config) && is_array($config['magephp'])) {
            $logger = null;
            if (array_key_exists('log_dir', $config['magephp']) && file_exists($config['magephp']['log_dir']) && is_dir($config['magephp']['log_dir'])) {
                $logfile = sprintf('%s/%s.log', $config['magephp']['log_dir'], date('Ymd_His'));
                $config['magephp']['log_file'] = $logfile;

                $logger = new Logger('magephp');
                $logger->pushHandler(new StreamHandler($logfile));
            }

            $this->runtime->setConfiguration($config['magephp']);
            $this->runtime->setLogger($logger);
            return;
        }

        throw new RuntimeException(sprintf('The file "%s" does not have a valid Magallanes configuration.', $this->file));
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
                $reflex = new ReflectionClass($class);
                if ($reflex->isInstantiable()) {
                    $command = new $class();
                    if ($command instanceof AbstractCommand) {
                        $command->setRuntime($this->runtime);
                        $this->add($command);
                    }
                }
            }
        }
    }

    /**
     * Gets the Runtime instance to use
     *
     * @return Runtime
     */
    protected function instantiateRuntime()
    {
        return new Runtime();
    }

    /**
     * Get the Runtime instance
     *
     * @return Runtime
     */
    public function getRuntime()
    {
        return $this->runtime;
    }
}
