<?php
# sudo mage install
# mage config add host s05.example.com to:[production]
# mage config git git://github.com/andres-montanez/Zend-Framework-Twig-example-app.git
# mage config svn svn://example.com/repo
# mage task:deployment/rsync to:production

# mage init
# mage add environment production
# mage deploy to:production


$baseDir = dirname(dirname(__FILE__));

require_once $baseDir . '/Mage/Autoload.php';
spl_autoload_register(array('Mage_Autoload', 'autoload'));

Mage_Console::output('Starting <blue>Magallanes</blue>', 0, 2);

$console = new Mage_Console;
$console->setArgs($argv);
$console->parse();

$console->run();

Mage_Console::output('Finished <blue>Magallanes</blue>', 0, 2);
