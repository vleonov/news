<?php

class U_Sentence
{
    private static $instances = array();

    private $text;
    private $words;

    private $whiteSymbols = "(\.|,|;|:|\?|\!|\-|\/|\&|)+([ \t\n\r\s]|$)";
    private $decorSymbols = "[\'\"«»“”\(\)\[\]{}<>\—]";

    private function __construct($text)
    {
        $this->text = (string) $text;
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

    /**
     * @return U_Word[]
     */
    public function getWords()
    {
        if (!is_null($this->words)) {
            return $this->words;
        }

        $clean = preg_replace('/' . $this->decorSymbols . '/iu', '', $this->text);
        $words = preg_split('/' . $this->whiteSymbols . '/iu', $clean);
        $words = array_filter($words);

        $this->words = array();
        foreach ($words as $word) {
            $this->words[] = U_Word::i($word);
        }

        return $this->words;
    }

    public function __toString()
    {
        return $this->text;
    }
}