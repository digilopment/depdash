<?php

file_exists('../src/Config.php') ? require_once '../src/Config.php' : require_once '../src/ConfigDefault.php';
require_once '../src/Template.php';
(new Template())->getHtml()->render();
