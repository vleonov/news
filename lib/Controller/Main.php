<?php

class C_Main
{
    public function main()
    {
        $filters = array(
            'userId' => USER_ID,
        );

        if ($feedId = Request()->args('id')) {
            $feed = new M_Feed($feedId);
            if (!$feed) {
                return Response()->error404();
            }

            $filters['feedId'] = $feed->id;
        } else {
            $feed = null;
        }

        $unreadNews = new L_User_Unread(
            $filters,
            array(
                'publicatedAt desc'
            ),
            30
        );

        /**
         * @var M_Feed $feed
         */
        $feedsList = new L_Feeds(array(1));
        $feeds = array();
        foreach ($feedsList as $feedCurr) {
            $feeds[$feedCurr->id] = $feedCurr;
        }

        $r = array(
            'feed' => $feed,
            'news' => $unreadNews,
            'newsCounts' => L_User_Unread::getCounts(USER_ID),
            'feeds' => $feeds,
        );

        return Response()->assign($r)->fetch('index.tpl');
    }
}