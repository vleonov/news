#!/usr/bin/php
<?php

require_once dirname(__FILE__) . '/../../lib/fx/Bootstrap.php';

$configFilename = dirname(__FILE__) . '/../../etc/config.php';
Bootstrap::run($configFilename);

$news = new L_News(
    array('isProcessed' => false),
    array('publicatedAt asc'),
    10000
);

/**
 * @var M_News $new
 */
foreach ($news as $new) {
    echo $new->id."\n\n";

    $texts = array(
        'title' => U_Text::i($new->title),
        'text' => U_Text::i($new->descr),
        'tags' => U_Text::i(implode(' ', $new->tags)),
    );

    /**
     * @var U_Word[] $uWords
     */
    $uWords = array();

    /**
     * @var U_Text[] $texts
     */
    foreach ($texts as $text) {
        $uWords = array_merge($uWords, $text->getWords());
    }

    $wordTexts = array();
    foreach ($uWords as $uWord) {
        $wordTexts[] = $uWord->lower();
    }

    /**
     * @var M_Word[] $mWords
     */
    $mWords = array();
    $tmpWords = new L_Words(
        array('word' => $wordTexts),
        array('freqS asc'),
        1e6
    );

    foreach ($tmpWords as $mWord) {
        $mWords[$mWord->lower()] = array($mWord);
    }
    unset($tmpWords);

    do {
        $unknown = array();
        $isUnknow = false;
        $isReknown = false;

        foreach ($uWords as $i => $uWord) {
            if (!isset($mWords[$uWord->lower()])) {
                if ($uWord->isNumeric() || !$uWord->isLetters()) {
                    echo "   " . $uWord . "\n";
                } elseif ($moreMWords = $uWord->getMWords()) {
                    foreach ($moreMWords as $mWord) {
                        $moreUWord = U_Word::i($mWord->word);
                        $mWords[$moreUWord->lower()][] = $mWord;
                        $mWords[$uWord->lower()][] = $mWord;
                        $uWords[] = $moreUWord;
                        unset($uWords[$i]);
                    }
                    $isReknown = true;
                    echo " ~ " . $uWord . "\n";
                } else {
                    $isUnknow = true;
                    $unknown[] = $uWord;
                    echo " - " . $uWord . "\n";
                }
            } else {
                $isUnknowThis = false;
                foreach ($mWords[$uWord->lower()] as $mWord) {
                    if (!$mWord->isProcessed) {
                        $isUnknowThis = true;
                        $isUnknow = true;
                        break;
                    }
                }
                if ($isUnknowThis) {
                    echo " ? " . $uWord . "\n";
                } else {
                    echo " + " . $uWord . "\n";
                }
            }
        }
        echo "\n\n---\n\n";
    } while($isReknown);

    if (sizeof($unknown)) {
        var_dump($unknown);
        echo 'createUnknown';
        exit();
        createUnknown($unknown);
    } elseif ($isUnknow) {
//        echo "waitUnknown\n\n";
    } else {
        calculateCoeff($mWords, $texts, $new);
    }
}

/**
 * @param M_Word[] $mWords
 * @param array $texts
 * @param M_News $new
 */
function calculateCoeff(array $mWords, array $texts, M_News $new)
{
    $searchIds = array();
    $wordIds = array();
    foreach ($mWords as $word => $mWordGroup) {
        foreach ($mWordGroup as $mWord) {
            $searchIds[] = $mWord->parentId;
            $wordIds[$word][] = $mWord->parentId;
        }
    }

    $db = Database::get();
    $sql = 'select
            w.id,
            w.freqS as freq,
            c.id as categoryId,
            c.freqS as categoryFreq
        from
            news_words w
            join news_words_categories wc
                on (wc.id = w.id)
            join news_categories_links cl
                on (cl.id = wc.categoryId and cl.level <= 5)
            join news_categories c
                on (c.id = cl.parentId)
        where
            w.parentId in (%s)
        group by
            w.id,
            c.id
        order by
            w.word,
            c.id';
    $sql = sprintf(
        $sql,
        implode(',', array_unique($searchIds))
    );

    $res = $db->query($sql);

    $wordToCategories = array();

    $freqCategories = array();
    $freqTotalCategories = array();
    $freqWords = array();
    $freqTotalWords = array();

    $sort = array();

    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $freqCategories[$row['categoryId']][$row['id']] = 1;
        $freqTotalCategories[$row['categoryId']] = $row['categoryFreq'];
        $freqWords[$row['id']] = 0;
        $freqTotalWords[$row['id']] = $row['freq'];
        $wordToCategories[$row['id']][] = $row['categoryId'];
        $sort[$row['categoryId']] = 0;
    }

    /**
     * @var U_Text $text
     */
    foreach ($texts as $text) {
        /**
         * @var U_Word $uWord
         */
        $uWords = $text->getWords();
        foreach ($uWords as $uWord) {
            if (!isset($wordIds[$uWord->lower()])) {
                continue;
            }

            $ids = $wordIds[$uWord->lower()];
            foreach ($ids as $id) {
                if (!isset($wordToCategories[$id])) {
                    continue;
                }
                $freqWords[$id]++;
            }
        }
    }

    foreach ($freqCategories as $categoryId=>$freq) {
        $freqCategories[$categoryId] = sizeof($freq);
    }

    foreach ($freqTotalWords as $id => $v) {
        foreach ($wordToCategories[$id] as $categoryId) {
            $sort[$categoryId] += $freqWords[$id] / ($freqTotalWords[$id] ? $freqTotalWords[$id] : 0.01);
        }
    }

    foreach ($sort as $categoryId => $v) {
        $sort[$categoryId] = min(1, $sort[$categoryId]) * $freqCategories[$categoryId] / $freqTotalCategories[$categoryId];
    }

    arsort($sort);

    $coeff = array_slice($sort, 0, 100, true);

    $values = array();
    foreach ($coeff as $categoryId => $v) {
        $values[] = sprintf(
            '(%d, %d, %f)',
            $new->id,
            $categoryId,
            $v
        );
    }

    if ($values) {
        $db->exec(
            sprintf(
                'insert into news_news_coeff(id, categoryId, coeff) values %s on duplicate key update coeff=VALUES(coeff)',
                implode(',', $values)
            )
        );
    }

    $values = array();
    $parentIds = array();
    foreach ($mWords as $mWordGroup) {
        foreach ($mWordGroup as $mWord) {
            $values[] = sprintf(
                '(%d, %f, %f)',
                $mWord->id,
                0.1,
                0.1
            );
            $parentIds[] = $mWord->parentId;
        }
    }

    if ($values) {
        $db->exec(
            sprintf(
                'insert into news_words (id, freq, freqS) values %s on duplicate key update freq=freq+VALUES(freq), freqS=freqS+VALUES(freqS)',
                implode(',', $values)
            )
        );
        $res = $db->query(
            sprintf(
                'select parentId, max(freqS) as freq from news_words where parentId in (%s) group by parentId',
                implode(',', array_unique($parentIds))
            )
        );

        $values = array();
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $values[] = sprintf(
                '(%d, %f)',
                $row['parentId'],
                $row['freq']
            );
        }

        $db->exec(
            sprintf(
                'insert into news_words (id, freqS) values %s on duplicate key update freqS=VALUES(freqS)',
                implode(',', $values)
            )
        );
    }

    $new->isProcessed = true;
    $new->save();
}