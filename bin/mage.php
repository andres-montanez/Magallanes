<?php
# mage deploy to:production
# mage update
# mage up
# mage task:init to:production 
# mage run:full-deployment to:production

# full-deployment = update, deploy to:production

$baseDir = dirname(dirname(__FILE__));

require_once $baseDir . '/Magallanes/Autoload.php';
spl_autoload_register(array('Magallanes_Autoload', 'autoload'));

Magallanes_Console::output('Begining Magallanes' . PHP_EOL . PHP_EOL);

$console = new Magallanes_Console;
$console->setArgs($argv);
$console->parse();

$console->run();
