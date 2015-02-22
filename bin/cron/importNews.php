#!/usr/local/bin/php
<?php

require_once dirname(__FILE__) . '/../../lib/fx/Bootstrap.php';

$configFilename = dirname(__FILE__) . '/../../etc/config.php';
Bootstrap::run($configFilename);

$feeds = new L_Feeds(array('1'));

/**
 * @var M_Feed $feed
 */
foreach ($feeds as $feed) {

    echo $feed->id." ".$feed->title."\n\n";

    try {
        $rss = U_Rss::i($feed->url);
        $items = $rss->getItems();
    } catch (Exception $e) {
        echo "ERROR: " . $feed->url . "\n" . $e->getMessage() . "\n\n";
        continue;
    }

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
            $news->content = $item['content'] ? $item['content'] : $news->content;

            $existed = true;
        } else {
            $news = new M_News();
            $news->feedId = $feed->id;
            $news->url = $item['url'];
            $news->url_crc32 = $item['url_crc32'];
            $news->content = $item['content'];

            $existed = false;
        }

        if (!$news->content) {
            try {
                $parser = U_PageParser::i($news->url);
                $news->content = html_entity_decode($parser->getContent());
            } catch (Exception $e) {
                echo "ERROR: " . $news->url . "\n" . $e->getMessage() . "\n\n";
            }
        }

        $news->title = $item['title'];
        $news->descr = $item['descr'];
        $news->publicatedAt = $item['pubDate'];
        $news->tags = $item['tags'];

        $language = preg_match('/[а-яА-Я]/iu', $news->title . ' ' . $news->descr) ? 'ru' : 'hz';
        if (!$existed) {
            $news->isProcessed = $language != 'ru';
        }

        echo $language." ".$news->isProcessed."   ";

        try {
            $news->save();
        } catch (Exception $e) {
            echo "ERROR: " . $news->url . "\n" . $e->getMessage() . "\n\n";
            continue;
        }

        if (!$existed) {
            Database::get()->exec(
                sprintf(
                    'insert into news_users_unread (userId, newsId) (select userId, %d from news_users_feeds where feedId=%d) on duplicate key update newsId=VALUES(newsId)',
                    $news->id,
                    $news->feedId
                )
            );
        }
    }
}