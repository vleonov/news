#!/usr/local/bin/php
<?php

require_once dirname(__FILE__) . '/../../lib/fx/Bootstrap.php';

$configFilename = dirname(__FILE__) . '/../../etc/config.php';
Bootstrap::run($configFilename);

$db = Database::get();

$db->exec('update news_users_coeff set coeff=GREATEST(coeff-0.1, 0) where coeff>0');
$db->exec('update news_users_coeff set coeff=LEAST(coeff+0.1, 0) where coeff<0');