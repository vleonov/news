<?php

return array(

    '/((?<id>\d+)/)?' => array(
        'Main',
    ),

    '/feed/add/' => array(
        'Feed' => 'add',
    ),
    '/feed/(?<id>\d+)/' => array(
        'Feed',
    ),

    '/news/list/((?<feedId>\d+)/)?' => array(
        'News' => 'main',
    ),

    '/news/(?<id>\d+)/' => array(
        'News' => 'get',
    ),

    '/news/(?<id>\d+)/markRead/(?<sign>m|p)/' => array(
        'News' => 'markRead',
    ),

    '/news/(?<id>\d+)/go/' => array(
        'News' => 'goToUrl',
    ),

    '/test/' => array(
        'Test',
    ),
    '/test/cat/' => array(
        'Test' => 'categories',
    ),
    '/test/news/' => array(
        'Test' => 'news',
    ),
    '/test/news/(?<categoryId>\d+)/' => array(
        'Test' => 'newsCategory',
    ),
);