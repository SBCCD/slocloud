<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use SLOCloud\Config;

require_once 'src/bootstrap.php';

$config = new Config("config");
$config->loadIni("cli.ini");

$extraConfigs = getenv('config');
if ($extraConfigs !== false) {
    foreach (explode(';', $extraConfigs) as $configFile) {
        $config->loadIni($configFile);
    }
}

$debug = getenv('debug-config');
if ($debug !== false) {
    foreach ($config->db() as $name => $value) {
        echo "            $name => $value".PHP_EOL;
    }
    echo PHP_EOL;
    die();
}

$entityManager = \SLOCloud\Application::createEntityManager($config->value('db'), null);

return ConsoleRunner::createHelperSet($entityManager);
