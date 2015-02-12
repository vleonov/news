<?php

require_once dirname(__FILE__) . '/../lib/External/Smarty/Smarty.class.php';
require_once dirname(__FILE__) . '/../lib/fx/Bootstrap.php';

$configFilename = dirname(__FILE__) . '/../etc/config.php';
Bootstrap::run($configFilename);
