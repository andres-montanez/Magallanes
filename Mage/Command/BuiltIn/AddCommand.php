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

use Mage\Command\AbstractCommand;
use Mage\Console;
use Exception;

/**
 * Command for Adding elements to the Configuration.
 * Currently elements allowed to add:
 *   - environments
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class AddCommand extends AbstractCommand
{
    /**
     * Adds new Configuration Elements
     * @see \Mage\Command\AbstractCommand::run()
     * @throws Exception
     */
    public function run()
    {
        $subCommand = $this->getConfig()->getArgument(1);

        try {
            switch ($subCommand) {
                case 'environment':
                    $this->addEnvironment();
                    break;

                default;
                    throw new Exception('The Type of Add is needed.');
                    break;
            }
        } catch (Exception $exception) {
            Console::output('<red>' . $exception->getMessage() . '</red>', 1, 2);
        }
    }

    /**
     * Adds an Environment
     *
     * @throws Exception
     */
    protected function addEnvironment()
    {
        $withReleases = $this->getConfig()->getParameter('enableReleases', false);
        $environmentName = strtolower($this->getConfig()->getParameter('name'));

        if ($environmentName == '') {
            throw new Exception('You must specify a name for the environment.');
        }

        $environmentConfigFile = getcwd() . '/.mage/config/environment/' . $environmentName . '.yml';

        if (file_exists($environmentConfigFile)) {
            throw new Exception('The environment already exists.');
        }

        Console::output('Adding new environment: <bold>' . $environmentName . '</bold>');

        $releasesConfig = 'releases:' . PHP_EOL
            . '  enabled: true' . PHP_EOL
            . '  max: 10' . PHP_EOL
            . '  symlink: current' . PHP_EOL
            . '  directory: releases' . PHP_EOL;

        $baseConfig = '#' . $environmentName . PHP_EOL
            . 'deployment:' . PHP_EOL
            . '  user: dummy' . PHP_EOL
            . '  from: ./' . PHP_EOL
            . '  to: /var/www/vhosts/example.com/www' . PHP_EOL
            . '  excludes:' . PHP_EOL
            . ($withReleases ? $releasesConfig : '')
            . 'hosts:' . PHP_EOL
            . 'tasks:' . PHP_EOL
            . '  pre-deploy:' . PHP_EOL
            . '  on-deploy:' . PHP_EOL
            . ($withReleases ? ('  post-release:' . PHP_EOL) : '')
            . '  post-deploy:' . PHP_EOL;

        $result = file_put_contents($environmentConfigFile, $baseConfig);

        if ($result) {
            Console::output('<light_green>Success!!</light_green> Environment config file for <bold>' . $environmentName . '</bold> created successfully at <blue>' . $environmentConfigFile . '</blue>');
            Console::output('<bold>So please! Review and adjust its configuration.</bold>', 2, 2);
        } else {
            Console::output('<light_red>Error!!</light_red> Unable to create config file for environment called <bold>' . $environmentName . '</bold>', 1, 2);
        }
    }
}
