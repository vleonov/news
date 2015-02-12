#!/usr/bin/php
<?php

require_once dirname(__FILE__) . '/../lib/fx/Bootstrap.php';

$configFilename = dirname(__FILE__) . '/../etc/config.php';
Bootstrap::run($configFilename);

$uWord = U_Word::i('социальных');

var_dump($uWord->getMWords());
