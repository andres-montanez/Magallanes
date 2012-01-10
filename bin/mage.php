<?php
# sudo mage install
# mage version
# mage upgrade
# mage config add host s05.example.com to:[production]
# mage config git git://github.com/andres-montanez/Zend-Framework-Twig-example-app.git
# mage config svn svn://example.com/repo
# mage task:deployment/rsync to:production

# mage releases list to:production
# mage releases rollback to:production
# mage releases rollback -1 to:production
# mage releases rollback -2 to:production
# mage releases rollback -3 to:production
# mage releases rollback 0 to:production
# mage releases rollback 20120101172148 to:production
# mage add environment production --width-releases

# mage init
# mage add environment production
# mage deploy to:production

date_default_timezone_set('UTC');

$baseDir = dirname(dirname(__FILE__));

define('MAGALLANES_VERSION', '0.9.1');

require_once $baseDir . '/Mage/Autoload.php';
spl_autoload_register(array('Mage_Autoload', 'autoload'));

$console = new Mage_Console;
$console->setArgs($argv);
$console->parse();

$console->run();
