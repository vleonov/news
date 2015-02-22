#!/usr/local/bin/php
<?php

require_once dirname(__FILE__) . '/../lib/fx/Bootstrap.php';

$configFilename = dirname(__FILE__) . '/../etc/config.php';
Bootstrap::run($configFilename);

$sql = 'select id, parentId from news_categories_links where level=1 order by id';
$db = Database::get();

$res = $db->query($sql);
$links = array();
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $links[$row['id']][] = $row['parentId'];
}

$sql = 'select distinct id from news_categories_links where level >= 2';
$res = $db->query($sql);
$processed = array();
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $processed[$row['id']] = true;
}

$sql = 'insert into news_categories_links (id, parentId, level) values %s on duplicate key update level=greatest(level, VALUES(level))';

$i = 0;
foreach ($links as $categoryId => $parents) {
    if (isset($processed[$categoryId])) {
        continue;
    }

    $level = 2;
    $values = array();
    $ancestorIds = array();

    do {
        $ancestors = array();
        foreach ($parents as $id) {
            if (isset($links[$id])) {
                $ancestors = array_merge($ancestors, $links[$id]);
            }
        }

        foreach ($ancestors as $id) {
            $values[] = sprintf(
                '(%d, %d, %d)',
                $categoryId,
                $id,
                $level
            );
            $ancestorIds[] = $id;
        }

        $level ++;
        $parents = $ancestors;
    } while ($parents && $level<10);

    if ($values) {
        $db->exec(
            sprintf(
                $sql,
                implode(', ', $values)
            )
        );
    }

    if (++$i % 100 == 0) {
        echo "   ".$i." ".sizeof($values)."\n";
    }
}
