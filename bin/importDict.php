#!/usr/bin/php
<?php

require_once dirname(__FILE__) . '/../lib/fx/Bootstrap.php';

$configFilename = dirname(__FILE__) . '/../etc/config.php';
Bootstrap::run($configFilename);

$grammaList = new L_Grammas(array(1));
$grammas = array();
foreach ($grammaList as $gramma) {
    $grammas[$gramma->key] = $gramma->id;
}

$fd = fopen(ROOT_DIR . '/dict/dict.opcorpora.txt', 'r');

$db = Database::get();
$sqlLinks = "insert into news_words_grammas (wordId, grammaId) values %s";

$i = 0;
$parentId = null;
while (!feof($fd)) {
    $i++;
    $line = trim(fgets($fd));
    if (empty($line) || is_numeric($line)) {
        $parentId = null;
        continue;
    }

    $item = array_filter(preg_split('/[\s\,]+/', $line));

    $word = new M_Word();
    $word->parentId = $parentId;
    $word->word = str_replace('ё', 'е', mb_strtolower(array_shift($item), 'UTF-8'));
    $word->freq = 0;
    $word->freqS = 0;

    $word->save();

    $values = array();
    foreach ($item as $gramma) {
        if (isset($grammas[$gramma])) {
            $values[] = '(' . $word->id . ', ' . $grammas[$gramma] . ')';
        }
    }

    if ($values) {
        $db->exec(sprintf($sqlLinks, implode(',', $values)));
    }

    if ($parentId === null) {
        $parentId = $word->id;
    }

    if ($i % 1000 == 0) {
        echo $i." ".$word->word."\n";
    }
}