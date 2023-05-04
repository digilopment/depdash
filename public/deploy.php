<?php
file_exists(__DIR__ . '/../src/Config.php') ? require_once __DIR__ . '/../src/Config.php' : require_once __DIR__ . '/../src/ConfigDefault.php';
require_once __DIR__ . '/../src/Deployer.php';

$repositories = [];
if (isset($argv[1])) {
    $repositories = explode(',', preg_replace('/\s+/', '', str_replace(['.'], [','], $argv[1])));
}
$branchOrTag = '';
if (isset($argv[2])) {
    $branchOrTag = preg_replace('/\s+/', '', $argv[2]);
}
(new Deployer($repositories))->deploy($branchOrTag);
