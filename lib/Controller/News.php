<?php

class C_News
{
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
                    select 1, nc.categoryId, 1 * LEAST(1, nc.coeff) from news_news_coeff nc where nc.id=6983
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