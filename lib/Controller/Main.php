<?php

class C_Main
{
    const NEWS_PER_PAGE = 30;

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
        }

        $news = new L_User_Unread(
            $filters,
            array(
                'publicatedAt desc'
            ),
            self::NEWS_PER_PAGE
        );
        $isUnread = true;

        if (!$news->length) {
            $news = new L_User_News(
                $filters,
                array(
                    'publicatedAt desc'
                ),
                self::NEWS_PER_PAGE
            );
            $isUnread = false;
        }

        $ids = array();
        foreach ($news as $new) {
            $ids[] = $new->id;
        }

        $coeff = array();
        if ($ids) {
            $sql = 'select nc.id, sum(nc.coeff * uc.coeff) as c
                from news_news_coeff nc
                join news_users_coeff uc on (nc.categoryId=uc.categoryId)
                where nc.id in (%s) and uc.id=%d
                group by nc.id';
            $res = Database::get()->query(
                sprintf(
                    $sql,
                    implode(',', $ids),
                    USER_ID
                )
            );

            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                $coeff[$row['id']] = round($row['c'], 2);
            }
        }

        /**
         * @var M_Feed $feed
         */
        $feedsList = new L_User_Feeds(array('userId' => USER_ID));
        $feeds = array();
        foreach ($feedsList as $feed) {
            $feeds[$feed->id] = $feed;
        }

        $r = array(
            'feedId' => $feedId,
            'news' => $news,
            'coeff' => $coeff,
            'isUnread' => $isUnread,
            'newsCounts' => L_User_Unread::getCounts(USER_ID),
            'feeds' => $feeds,
        );

        return Response()->assign($r)->fetch('index.tpl');
    }
}