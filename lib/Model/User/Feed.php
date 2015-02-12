<?php

/**
 * @property int $userId
 * @property int $feedId
 */
class M_User_Feed extends Model
{
    protected $_tblName = 'news_users_feeds';

    public function save()
    {
        $sql = 'INSERT INTO %s VALUES (%d, %d) ON DUPLICATE KEY UPDATE feedId=VALUES(feedId)';
        $sql = sprintf(
            $sql,
            $this->_tblName,
            $this->userId,
            $this->feedId
        );

        $this->_oDb->query($sql);

        return $this->userId;
    }
}
