<?php

class C_News
{
    const NEWS_PER_PAGE = 5;

    public function main()
    {
        $isUnread = Request()->get('u', false);

        $filters = array(
            'userId' => USER_ID,
            "publicatedAt < '" . date(DATE_W3C, Request()->get('p')) . "'"
        );

        if ($feedId = Request()->args('feedId')) {
            $feed = new M_Feed($feedId);
            if (!$feed) {
                return Response()->error404();
            }

            $filters['feedId'] = $feed->id;
        }

        if ($isUnread) {
            $news = new L_User_Unread(
                $filters,
                array(
                    'publicatedAt desc'
                ),
                self::NEWS_PER_PAGE
            );
        } else {
            $news = new L_User_News(
                $filters,
                array(
                    'publicatedAt desc'
                ),
                self::NEWS_PER_PAGE
            );
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
            'news' => $news,
            'coeff' => $coeff,
            'isUnread' => $isUnread,
            'feeds' => $feeds,
        );

        return Response()->assign($r)->fetch('blocks/news.tpl');
    }

    public function get()
    {
        $news = new M_News(Request()->args('id'));
        if (!$news->id) {
            return Response()->ajax()->error404();
        }

        $r = array(
            'id' => $news->id,
            'text' => $news->content ? $news->content : $news->descr,
        );

        return Response()->assign($r)->ajax();
    }

    public function markRead()
    {
        $news = new M_News(Request()->args('id'));
        if (!$news->id) {
            return Response()->ajax()->error404();
        }

        $oDb = Database::get();

        $sql = sprintf(
            'delete from news_users_unread where userId=%d and newsId=%d',
            USER_ID,
            $news->id
        );
        $res = $oDb->exec($sql);

        if ($res) {
            $sql = sprintf(
                'insert into news_users_coeff (id, categoryId, coeff) (
                    select %d, nc.categoryId, %f * LEAST(1, nc.coeff) from news_news_coeff nc where nc.id=%d
                ) on duplicate key update coeff=news_users_coeff.coeff+VALUES(coeff)',
                USER_ID,
                Request()->args('sign') == 'm' ? -0.25 : 1,
                $news->id
            );
            $oDb->exec($sql);
        }

        return Response()->assign(array('success' => true))->ajax();
    }

    public function goToUrl()
    {
        $news = new M_News(Request()->args('id'));
        if (!$news->id) {
            return Response()->ajax()->error404();
        }

        return Response()->redirect($news->url);
    }
}