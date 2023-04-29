<?php

file_exists(__DIR__ . '/../src/Config.php') ? require_once __DIR__ . '/../src/Config.php' : require_once __DIR__ . '/../src/ConfigDefault.php';
require_once _DIR__ . '/../src/Template.php';
(new Template())->getHtml()->render();
