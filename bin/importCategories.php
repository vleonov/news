#!/usr/bin/php
<?php

require_once dirname(__FILE__) . '/../lib/fx/Bootstrap.php';

$configFilename = dirname(__FILE__) . '/../etc/config.php';
Bootstrap::run($configFilename);

$db = Database::get();

$time = microtime(true);
$categoriesList = new L_Categories(array(1), array(), 1e6);
$categories = array();
$ancestors = array();
foreach ($categoriesList as $category) {
    $categories[$category->title] = $category->id;
    $ancestors[$category->id] = array_filter(explode(',', $category->path));
}

echo 'Categories: ' . (microtime(true) - $time)."\n";

do {
    $time = microtime(true);
    $words = new L_Words(
        array(
            'isProcessed' => false,
            'parentId=id',
            'id in (select wordId from news_words_grammas where grammaId=2)'
        ),
        array(),
        1000
    );

    echo 'Words: ' . (microtime(true) - $time)."\n";

    $i = 0;

    foreach ($words as $word) {
        $wCategories = getCategories($word->word);

        echo $word->word."\n";
        echo implode(', ', $wCategories)."\n";

        $values = array();
        $categoryIds = array();
        foreach ($wCategories as $categoryPageName) {
            $title = getTitle($categoryPageName);
            if (isset($categories[$title])) {
                $categoryId = $categories[$title];
            } else {
                list($categoryId, ) = getCategory($categoryPageName, $categories, $ancestors);
                if ($categoryId == null) {
                    continue;
                }
            }

            $values[] = sprintf(
                '(%d, %d)',
                $word->id,
                $categoryId
            );
            $categoryIds[] = $categoryId;
        }

        $values = array_filter($values);
        if ($values) {
            $sql = sprintf(
                'insert into news_words_categories(id, categoryId) values %s on duplicate key update id=id',
                implode(',', $values)
            );
            $db->exec($sql);

            $sql = sprintf(
                'select categoryId, count(id) as cnt from news_words_categories where categoryId in (%s) group by categoryId',
                implode(',', $categoryIds)
            );
            $res = $db->query($sql);

            $values = array();
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                $values[] = sprintf(
                    '(%d, %d, %d)',
                    $row['categoryId'],
                    $row['cnt'],
                    $row['cnt']
                );
            }

            $sql = sprintf(
                'insert into news_categories (id, freq, freqS) values %s on duplicate key update freq=VALUES(freq), freqS=VALUES(freqS)',
                implode(',', $values)
            );
            $db->exec($sql);

            // parents' freqS's
        }

        $sql = sprintf(
            'update news_words set isProcessed = 1 where parentId = %d',
            $word->id
        );
        $db->exec($sql);

        echo "\n\n";
    }
} while ($words->length == 1000);

exit();

function getCategories($title)
{
    $params = array(
        'redirects' => true,
        'action' => 'query',
        'format' => 'json',
        'prop' => 'categories',
        'cllimit' => 500,
        'clshow' => '!hidden',
        'titles' => $title,
    );

    $url = sprintf(
        'https://ru.wikipedia.org/w/api.php?%s',
        http_build_query($params)
    );

    $curl = curl_init($url);
    curl_setopt_array(
        $curl,
        array(
            CURLOPT_TIMEOUT => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_RETURNTRANSFER => true
        )
    );

    $response = curl_exec($curl);
    $responseInfo = curl_getinfo($curl);

    if ($responseInfo['http_code'] != 200) {
        echo "Sleep ...\n";
        sleep(rand(20, 40));
        $response = curl_exec($curl);
        $responseInfo = curl_getinfo($curl);
    }

    if ($responseInfo['http_code'] != 200) {
        return null;
    } elseif (!($data = @json_decode($response, true))) {
        trigger_error('Cannot parse response: ' . $response);
        exit();
    }

    if (empty($data['query']['pages'])) {
        return array();
    }

    reset($data['query']['pages']);
    list($id, $page) = each($data['query']['pages']);
    if ($id < 0 || empty($page['categories'])) {
        return array();
    }

    $result = array();
    foreach ($page['categories'] as $category) {
        $result[] = $category['title'];
    }

    return $result;
}

function getCategory($pageName, &$categories, &$ancestors, $proceeded = array(), $i = 0)
{
    if (in_array($pageName, $proceeded)) {
        return array(null, array());
    }
    $proceeded[] = $pageName;

    echo str_repeat(' ', $i) . $pageName."\n";

    $parents = getCategories($pageName);
    $parentIds = array();
    $ancestorIds = array();
    foreach ($parents as $parentPageName) {
        $parentTitle = getTitle($parentPageName);
        if (!isset($categories[$parentTitle])) {
            list($parentId, $grandParentIds) = getCategory($parentPageName, $categories, $ancestors, $proceeded, $i+1);
            $ancestorIds = array_merge($ancestorIds, $grandParentIds);
        } else {
            $parentId = $categories[$parentTitle];
            $ancestorIds = array_merge($ancestorIds, $ancestors[$parentId]);
        }
        $ancestorIds[] = $parentId;
        $parentIds[] = $parentId;
    }


    $ancestorIds = array_filter($ancestorIds);
    $parentIds = array_filter($parentIds);

    $ancestorIds = array_unique($ancestorIds);

    $category = new M_Category();
    $category->title = getTitle($pageName);
    $category->path = $ancestorIds ? ',' . implode(',', $ancestorIds) . ',' : ',';
    $category->freq = 1;
    $category->freqS = 1;

    $category->save();

    $values = array();
    foreach ($parentIds as $parentId) {
        $values[] = sprintf(
            '(%d, %d, %d)',
            $category->id,
            $parentId,
            1
        );
    }
    $values[] = sprintf(
        '(%d, %d, %d)',
        $category->id,
        $category->id,
        0
    );

    if ($values) {
        $sql = sprintf(
            'insert into news_categories_links (id, parentId, level) values %s',
            implode(',', $values)
        );
        try {
            Database::get()->exec($sql);
        } catch (Exception $e) {
            echo $e->getMessage()."\n";
            echo $sql;
            exit();
        }
    }

    $categories[$category->title] = $category->id;
    $ancestors[$category->id] = $ancestorIds;

    return array($category->id, $ancestorIds);
}

function getTitle($pageName)
{
    return preg_replace('/^категория\s*\:\s*/iu', '', $pageName);
}