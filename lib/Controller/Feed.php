<?php

class C_Feed
{
    public function main()
    {
        $feed = new M_Feed(Request()->args('id'));
        if (!$feed->id) {
            return Response()->error404();
        }

        $news = new L_News(
            array('feedId' => $feed->id),
            array('publicatedAt desc'),
            30
        );

        $r = array(
            'feed' => $feed,
            'news' => $news,
        );

        return Response()->assign($r)->fetch('feed/index.tpl');
    }

    public function add()
    {
        if (!Request()->isPost()) {
            return Response()->error404();
        }

        $feedUrl = Request()->post('feedUrl');
        $feedCrc32 = crc32($feedUrl);

        $existed = new L_Feeds(array('url_crc32' => $feedCrc32));

        if ($existed->length) {
            $feed = $existed->current();
        } else {
            $feed = new M_Feed();
            $feed->url = $feedUrl;
            $feed->url_crc32 = $feedCrc32;

            $rss = U_Rss::i($feed->url);
            $feed->title = $rss->getTitle();
            $feed->description = $rss->getDescription();

            $feed->save();
        }

        $userFeed = new M_User_Feed();
        $userFeed->userId = USER_ID;
        $userFeed->feedId = $feed->id;
        $userFeed->save();

        return Response()->redirect('/feed/' . $feed->id);
    }
}