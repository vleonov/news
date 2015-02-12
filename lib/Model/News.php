<?php

/**
 * @property int $id
 * @property int $feedId
 * @property string $url
 * @property string $url_crc32
 * @property string $title
 * @property string $descr
 * @property string $content
 * @property array $tags
 * @property string $createdAt
 * @property string $publicatedAt
 * @property bool $isProcessed
 */
class M_News extends Model
{
    protected $_tblName = 'news_news';

    protected $_customTypes = array(
        'tags' => Database::TYPE_ARRAY,
        'createdAt' => Database::TYPE_TIMESTAMP,
        'publicatedAt' => Database::TYPE_TIMESTAMP,
        'isProcessed' => Database::TYPE_BOOLEAN,
    );
}
