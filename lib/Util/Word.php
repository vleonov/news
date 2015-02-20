<?php

class U_Word
{
    private static $instances = array();

    private $text;

    private $grammaIds = array(
        'A' => 3, // прилагательное
        'ADV' => 12, // наречие
        'ADVPRO' => 12, // местоименное наречие
        'ANUM' => 3, // числительное-прилагательное
        'APRO' => 3, // местоимение-прилагательное
        'CONJ' => 16, // союз
        'INTJ' => 18, // междометие
        'NUM' => 11, // числительное
        'PART' => 17, // частица
        'PR' => 15, // предлог
        'S' => 2, // существительное
        'SPRO' => 13, // местоимение-существительное
        'V' => 6, // глагол

        'наст' => 78, // настоящее
        'непрош' => 80, // непрошедшее
        'прош' => 79, // прошедшее

        'им' => 34, // именительный
        'род' => 35, // родительный
        'дат' => 36, // дательный
        'вин' => 37, // винительный
        'твор' => 38, // творительный
        'пр' => 39, // предложный
        'парт' => 42, // партитив (второй родительный)
        'местн' => 45, // местный (второй предложный)
        'зват' => 40, // звательный

        'ед' => 28, // единственное число
        'мн' => 29, // множественное число

        'деепр' => 10, // деепричастие
        'инф' => 7, // инфинитив
        'прич' => 8, // причастие
        'изъяв' => 82, // изьявительное наклонение
        'пов' => 83, // повелительное наклонение

        'притяж' => 58, // притяжательные прилагательные

        'прев' => 54, // превосходная

        '1-л' => 74, // 1-е лицо
        '2-л' => 75, // 2-е лицо
        '3-л' => 76, // 3-е лицо

//        'муж' => 23, // мужской род
//        'жен' => 24, // женский род
//        'сред' => 25, // средний род

        'несов' => 65, // несовершенный
        'сов' => 64, // совершенный

        'действ' => 88, // действительный залог
        'страд' => 89, // страдательный залог

        'од' => 20, // одушевленное
        'неод' => 21, // неодушевленное

//        'имя' => 47, //имя собственное
//        'фам' => 48, //фамилия
//        'отч' => 49, //отчество
//        'гео' => 50, //географическое название
    );

    private function __construct($text)
    {
        $this->text = strtr((string) $text, array('ё' => 'е', 'Ё' => 'е'));
    }

    /**
     * @param string $text
     * @return self
     */
    public static function i($text)
    {
        if (!isset(self::$instances[$text])) {
            self::$instances[$text] = new self($text);
        }

        return self::$instances[$text];
    }

    public function lower()
    {
        return mb_strtolower($this->text, 'UTF-8');
    }

    public function isLetters()
    {
        return preg_match('/^[a-zа-яё0-9\-\.]+$/iu', $this->text);
    }

    public function isNumeric()
    {
        return preg_match('/^([\$\%\€]?[\d\.\,\/\-]+(\$|\%|\€|K|К|М|M|\-[а-я]+)?)$/iu', $this->text);
    }

    public function isRussian()
    {
        return preg_match('/^[а-яё\-\.]+$/iu', $this->text);
    }

    public function getMWords()
    {
        $db = Database::get();
        $sqlGramma = 'insert into news_words_grammas (wordId, grammaId) values %s';
        $sqlSearch = 'select w.word, w.id, w.parentId
            from news_words w
            join news_words_grammas wg on (wg.wordId=w.id)
            where w.word=%s and wg.grammaId in (%s) %s
            group by w.id
            having count(wg.id) = %d';
        $defaultGramma = array(
            2, // существительное
        );

        $mWords = array();

        if (!$this->isRussian()) {
            $mWord = new M_Word();
            $mWord->word = $this->text;
            $mWord->isProcessed = false;
            $mWord->save();
            $mWord->parentId = $mWord->id;
            $mWord->save();

            $values = array();
            foreach ($defaultGramma as $grammaId) {
                $values[] = sprintf(
                    '(%d, %d)',
                    $mWord->id,
                    $grammaId
                );
            }
            $db->exec(
                sprintf(
                    $sqlGramma,
                    implode(',', $values)
                )
            );

            $mWords[] = $mWord;
            return $mWords;
        }

        $command = "echo '%s' | %s/mystem -i -n";
        $cmd = sprintf(
            $command,
            $this->text,
            ROOT_DIR . '/bin'
        );
        $output = array_filter(explode("\n", `$cmd`));
//echo "W " . $cmd."\n";
        foreach ($output as $item) {
            $parentWord = null;
            list($word, $item) = explode('{', $item);
            $word = trim($word, "\t\n\r?");
            $forms = explode('|', $item);
            foreach ($forms as $form) {
                if (strpos($form, '=')) {
                    list($parent, $gramma) = explode('=', $form, 2);
                    $parent = trim($parent, "\t\n\r?");
                    $parentWord = $parent ? $parent : $parentWord;

                    $grammaIds = array();
                    $grammas = preg_split('/[,=\}]/', $gramma);
                    foreach ($grammas as $gramma) {
                        if (isset($this->grammaIds[$gramma])) {
                            $grammaIds[] = $this->grammaIds[$gramma];
                        }
                    }
                } else {
                    $parentWord = $word;
                    $grammaIds = $defaultGramma;
                }

                if (empty($grammaIds)) {
                    $grammaIds = $defaultGramma;
                }

                $sql = sprintf(
                    $sqlSearch,
                    $db->escape($word),
                    implode(',', $grammaIds),
                    '',
                    sizeof($grammaIds)
                );
//echo "W " . $sql."\n";
                $res = $db->query($sql);
                if ($res->rowCount()) {
                    $row = $res->fetch(PDO::FETCH_ASSOC);
                    $mWords[$word] = new M_Word($row['id']);
                } else {
                    $cmd = sprintf(
                        $command . ' -l',
                        $parentWord,
                        ROOT_DIR . '/bin'
                    );
//echo "P " . $cmd."\n";
                    $parentOutput = trim(`$cmd`);

                    $parentGrammas = explode('|', $parentOutput, 2);
                    $parentGramma = preg_split('/[,=]/', reset($parentGrammas));
                    unset($parentGramma[0]);
                    $parentGrammaIds = array();
                    foreach ($parentGramma as $gramma) {
                        if (isset($this->grammaIds[$gramma])) {
                            $parentGrammaIds[] = $this->grammaIds[$gramma];
                        }
                    }

                    $parentGrammaIds = $parentGrammaIds ? $parentGrammaIds : array(2);

                    $sql = sprintf(
                        $sqlSearch,
                        $db->escape($parentWord),
                        implode(',', $parentGrammaIds),
                        ' and w.id=w.parentId',
                        sizeof($parentGrammaIds)
                    );
//echo "P " . $sql."\n";
                    $res = $db->query($sql);

                    if ($res->rowCount()) {
                        $row = $res->fetch(PDO::FETCH_ASSOC);
                        $parentMWord = new M_Word($row['id']);
                    } else {
                        $parentMWord = new M_Word();
                        $parentMWord->word = $parentWord;
                        $parentMWord->isProcessed = !in_array(2, $parentGrammaIds);
                        $parentMWord->save();
                        $parentMWord->parentId = $parentMWord->id;
                        $parentMWord->save();

                        $values = array();
                        foreach ($parentGrammaIds as $grammaId) {
                            $values[] = sprintf(
                                '(%d, %d)',
                                $parentMWord->id,
                                $grammaId
                            );
                        }
                        $db->exec(
                            sprintf(
                                $sqlGramma,
                                implode(',', $values)
                            )
                        );
                    }

                    if (mb_strtolower($word, 'UTF-8') != mb_strtolower($parentWord, 'UTF-8') || $grammaIds != $parentGrammaIds) {
                        $mWord = new M_Word();
                        $mWord->word = $word;
                        $mWord->parentId = $parentMWord->id;
                        $mWord->isProcessed = $parentMWord->isProcessed;
                        $mWord->save();

                        $values = array();
                        foreach ($grammaIds as $grammaId) {
                            $values[] = sprintf(
                                '(%d, %d)',
                                $mWord->id,
                                $grammaId
                            );
                        }
                        $db->exec(
                            sprintf(
                                $sqlGramma,
                                implode(',', $values)
                            )
                        );

                        $mWords[$word] = $mWord;
                    } else {
                        $mWords[$word] = $parentMWord;
                    }
                }
            }
        }

        if (empty($mWords)) {
            $mWord = new M_Word();
            $mWord->word = $this->text;
            $mWord->isProcessed = false;
            $mWord->save();
            $mWord->parentId = $mWord->id;
            $mWord->save();

            $values = array();
            foreach ($defaultGramma as $grammaId) {
                $values[] = sprintf(
                    '(%d, %d)',
                    $mWord->id,
                    $grammaId
                );
            }
            $db->exec(
                sprintf(
                    $sqlGramma,
                    implode(',', $values)
                )
            );

            $mWords[] = $mWord;
        }

        return $mWords;
    }

    public function __toString()
    {
        return $this->text;
    }
}