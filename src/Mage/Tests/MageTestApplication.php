<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests;

use Mage\Command\AbstractCommand;
use Mage\Runtime\Runtime;
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
class MageTestApplication extends Application
{
}
