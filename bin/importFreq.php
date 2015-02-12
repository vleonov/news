#!/usr/bin/php
<?php

require_once dirname(__FILE__) . '/../lib/fx/Bootstrap.php';

$configFilename = dirname(__FILE__) . '/../etc/config.php';
Bootstrap::run($configFilename);

$fd = fopen(ROOT_DIR . '/dict/unigram.freq.txt', 'r');
$db = Database::get();

$i = 0;
while (!feof($fd)) {
    $i++;
    $line = trim(fgets($fd));
    if (empty($line)) {
        continue;
    }

    $item = preg_split('/[\s]+/', $line);
    if (sizeof($item) != 3) {
        continue;
    }

    $words = new L_Words(array('word' => str_replace('ё', 'е', mb_strtolower($item[0], 'UTF-8'))));
    /** @var M_Word $word */
    foreach ($words as $word) {
        $word->freq = $item[2] ?: 0.1;
        $word->save();
    }

    if ($i % 1000 == 0) {
        echo $i." ".$item[0]."\n";
    }
}