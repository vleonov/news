#!/usr/bin/php
<?php

require_once dirname(__FILE__) . '/../../lib/fx/Bootstrap.php';

$configFilename = dirname(__FILE__) . '/../../etc/config.php';
Bootstrap::run($configFilename);

$feeds = new L_Feeds(array('1'));

/**
 * @var M_Feed $feed
 */
foreach ($feeds as $feed) {
    $rss = U_Rss::i($feed->url);

    $items = $rss->getItems();
    $urlCrc32s = array();
    foreach ($items as $i => $item) {
        if (!$item['url']) {
            continue;
        }
        $item['url_crc32'] = crc32($item['url']);
        $urlCrc32s[] = $item['url_crc32'];

        $items[$i]['url_crc32'] = $item['url_crc32'];
    }

    if (!$urlCrc32s) {
        continue;
    }

    $existed = new L_News(
        array(
            'feedId' => $feed->id,
            'url_crc32' => $urlCrc32s,
        )
    );

    $urlToNews = array();

    /**
     * @var M_News $news
     */
    foreach ($existed as $news) {
        $urlToNews[$news->url_crc32] = $news;
    }

    foreach ($items as $item) {
        if (isset($urlToNews[$item['url_crc32']])) {
            $news = $urlToNews[$item['url_crc32']];
        } else {
            $news = new M_News();
            $news->feedId = $feed->id;
            $news->url = $item['url'];
            $news->url_crc32 = $item['url_crc32'];
        }

        $news->title = $item['title'];
        $news->descr = $item['descr'];
        $news->publicatedAt = $item['pubDate'];
        $news->tags = $item['tags'];

        $news->save();
    }
}