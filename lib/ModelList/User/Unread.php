<?php

class L_User_Unread extends ModelList
{
    protected $_tblName = 'news_v_unread';
    protected $_modelClass = 'News';

    public static function getCounts($userId)
    {
        $sql = 'SELECT count(id) as cnt, feedId FROM news_v_unread WHERE userId=%d GROUP BY feedId ORDER BY cnt desc';
        $sql = sprintf(
            $sql,
            $userId
        );

        $oDb = Database::get();

        $res = $oDb->query($sql);

        $result = array(
            'total' => 0,
        );
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $result['total'] += $row['cnt'];
            $result[$row['feedId']] = $row['cnt'];
        }

        return $result;
    }
}