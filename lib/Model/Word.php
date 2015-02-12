<?php

/**
 * @property int $id
 * @property int $parentId
 * @property string $word
 * @property float $freq
 * @property float $freqS
 * @property bool $isProcessed
 * @property bool $isKnown
 */
class M_Word extends Model
{
    protected $_tblName = 'news_words';

    public function lower()
    {
        return mb_strtolower($this->word, 'UTF-8');
    }
}
