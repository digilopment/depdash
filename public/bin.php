<?php

file_exists('../src/Config.php') ? require_once '../src/Config.php' : require_once '../src/ConfigDefault.php';
require_once '../src/DepDash.php';
(new DepDash())->getResponse()->withJson()->writeToFile();
