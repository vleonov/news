<?php

class C_Test extends Controller
{
    public function main()
    {
        $feedUrl = Request()->get('feed', 'http://news/lenta.ru.xhtml');

        $curl = curl_init($feedUrl);
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_AUTOREFERER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 10,
            )
        );

        $db = Database::get();
        $sql = 'select
                o.word,
                w.freqS as freq,
                g.grammaId
            from
                news_words w
                join (select word, parentId from news_words where word in (%s) order by freqS desc) o
                    on (o.parentId=w.id)
                join news_words_grammas g
                    on (g.wordId = o.parentId)
            group by
                word';

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);

        if (!$response || $info['http_code'] != 200) {
            throw new Exception('Eroro getting feed ', $feedUrl);
        }

        $xml = new DOMDocument();
        $xml->loadXML($response);

        /**
         * @var $channel DOMElement
         */
        $channel = $xml->getElementsByTagName('channel')->item(0);
        $items = $channel->getElementsByTagName('item');
        $feed = array(
            'url' => $feedUrl,
            'title' => $channel->getElementsByTagName('title')->item(0)->nodeValue,
            'description' => $channel->getElementsByTagName('description')->item(0)->nodeValue,
        );

        $result = array();
        $freqs = array();
        $colors = array();
        $grammas = array();

        /**
         * @var $item DOMElement
         */
        foreach ($items as $item) {
            $title = $item->getElementsByTagName('title')->item(0);
            $descr = $item->getElementsByTagName('description')->item(0);
            $category = $item->getElementsByTagName('category')->item(0);
            $new = array(
                'title' => $title ? $title->nodeValue : 'n/a',
                'description' => $descr ? $descr->nodeValue : 'n/a',
                'tags' => array($category ? $category->nodeValue : ''),
            );

            $sentence = U_Sentence::i($new['title']);

            $res = $db->query(
                sprintf(
                    $sql,
                    implode(',', array_map(array($db, 'escape'), $sentence->getWords()))
                )
            );

            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                $freqs[$row['word']] = $row['freq'];
                $grammas[$row['word']] = $row['grammaId'];
            }

            foreach ($sentence->getWords() as $word) {
                $word = mb_strtolower($word->__toString());
                $freq = U_Misc::is($freqs[$word]);
                $color = round((min($freq, 80) / 80) * 240);

                $result[$sentence->__toString()][] = $word;
                $colors[$word] = $color;
            }
        }

        $r = array(
            'feed' => $feed,
            'result' => $result,
            'freqs' => $freqs,
            'colors' => $colors,
            'grammas' => $grammas,
        );

        return Response()->assign($r)->fetch('test/index.tpl');
    }

    public function categories()
    {
        $text = U_Text::i(Request()->post('text'));

        $sentences = $text->getSentences();
        $words = array();
        foreach ($sentences as $sentence) {
            $words = array_merge($words, $sentence->getWords());
        }

        $categories = array();
        $wordToCategories = array();
        $categoryToWords = array();

        $freqCategories = array();
        $freqWords = array();
        $freqTotalCategories = array();
        $freqTotalWords = array();

        $data = array();
        $sort = array();

        if ($words) {
            $db = Database::get();
            $sql
                = 'select
                    lower(o.word) as word,
                    w.freqS as freq,
                    c.id as categoryId,
                    c.title as categoryTitle,
                    c.freqS as categoryFreq
                from
                    (select f.word, f.parentId
                        from (select word, parentId from news_words where word in (%s) order by freqS desc) f
                        group by f.word
                    ) o
                    join news_words w
                        on (w.id = o.parentId)
                    join news_words_categories wc
                        on (wc.id = o.parentId)
                    join news_categories_links cl
                        on (cl.id = wc.categoryId and cl.level <= 3)
                    join news_categories c
                        on (c.id = cl.parentId)
                group by
                    word,
                    c.id
                order by
                    word,
                    c.id';

            $res = $db->query(
                sprintf(
                    $sql,
                    implode(',', array_map(array($db, 'escape'), $words))
                )
            );

            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                if (!$row['categoryId']) {
                    continue;
                }
                $categories[$row['categoryId']] = array(
                    'title' => $row['categoryTitle'],
                    'freq' => $row['categoryFreq'],
                );
                $wordToCategories[$row['word']][] = $row['categoryId'];
                $categoryToWords[$row['categoryId']][] = $row['word'];

                $freqTotalCategories[$row['categoryId']] = $row['categoryFreq'];
                $freqTotalWords[$row['word']] = $row['freq'];
            }

            foreach ($words as $word) {
                $w = mb_strtolower($word->__toString(), 'UTF-8');
                if (!isset($wordToCategories[$w])) {
                    continue;
                }
                foreach ($wordToCategories[$w] as $categoryId) {
                    $freqCategories[$categoryId] = U_Misc::is($freqCategories[$categoryId], 0) + 1;
                }
                $freqWords[$w] = U_Misc::is($freqWords[$w], 0) + 1;
            }

            foreach ($freqTotalWords as $w => $v) {
                foreach ($wordToCategories[$w] as $categoryId) {
                    $data[$categoryId] = sprintf(
                        '%d / %.1f',
                        $freqCategories[$categoryId],
                        $freqTotalCategories[$categoryId]
                    );
//                    echo $w." ".$freqWords[$w]." ".$freqTotalWords[$w]."<br>";
                    $sort[$categoryId] = U_Misc::is($sort[$categoryId], 0) + $freqWords[$w]/$freqTotalWords[$w];
                }

                $data[$w] = sprintf(
                    '%d / %.1f',
                    $freqWords[$w],
                    $freqTotalWords[$w]
                );
            }

            foreach ($sort as $categoryId => $v) {
                $sort[$categoryId] = $freqCategories[$categoryId] / $freqTotalCategories[$categoryId];
            }

            arsort($sort);
        }

        $r = array(
            'text' => $text,
            'categories' => $categories,
            'categoryToWords' => $categoryToWords,
            'sort' => $sort,
            'data' => $data,
        );

        return Response()->assign($r)->fetch('test/categories.tpl');
    }

    public function news()
    {
        $news = new L_News(
            array('isProcessed' => true),
            array('publicatedAt desc'),
            1000
        );

        $distr = array();
        /**
         * @var M_News $new
         */
        foreach ($news as $new) {
            foreach ($new->coeff as $categoryId => $coeff) {
                $distr[$categoryId] = U_Misc::is($distr[$categoryId], 0) + $coeff;
            }
        }

        $categoriesList = new L_Categories(
            array('id' => array_keys($distr)),
            array(),
            1e5
        );
        $categories = array();
        foreach ($categoriesList as $category) {
            $categories[$category->id] = $category;
        }


        arsort($distr);

        $r = array(
            'distr' => $distr,
            'categories' => $categories,
        );

        return Response()->assign($r)->fetch('test/news.tpl');
    }

    public function newsCategory()
    {
        $category = new M_Category(Request()->args('categoryId'));

        $news = new L_News(
            array('coeff like \'%"' . $category->id . '"%\''),
            array('publicatedAt desc'),
            100
        );

        $r  = array(
            'category' => $category,
            'news' => $news,
        );

        return Response()->assign($r)->fetch('test/newsCategory.tpl');
    }
}