<?php

file_exists(__DIR__ . '/../src/Config.php') ? require_once __DIR__ . '/../src/Config.php' : require_once __DIR__ . '/../src/ConfigDefault.php';
require_once __DIR__ . '/../src/DepDash.php';
(new DepDash())->getResponse()->withJson()->writeToFile();
