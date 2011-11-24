<?php
# sudo mage install
# mage init

# mage config add environment [production]
# mage config add host prod_example@s05.example.com:/var/www/vhosts/example.com/www to:[production]
# mage config git git://github.com/andres-montanez/Zend-Framework-Twig-example-app.git
# mage config svn svn://example.com/repo

# mage deploy to:production
# mage task:deployment/rsync to:production 


$baseDir = dirname(dirname(__FILE__));

require_once $baseDir . '/Mage/Autoload.php';
spl_autoload_register(array('Mage_Autoload', 'autoload'));

Mage_Console::output('Starting <blue>Magallanes</blue>', 0);
Mage_Console::output('');


$console = new Mage_Console;
$console->setArgs($argv);
$console->parse();

$console->run();


Mage_Console::output('Finished <blue>Magallanes</blue>', 0);
