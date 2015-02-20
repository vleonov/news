<?php

class U_Text
{
    private static $instances = array();

    private $text;
    private $sentences;

    private $stopSymbols = "(\.|\?|\!)+([ \t\n\r\s]|$)";

    private function __construct($text)
    {
        $text = html_entity_decode($text);
        $text = preg_replace('~(\<\s*br\s*/?\s*\>|\<[^>]/\>)~iu', "$1\n", $text);
        $this->text = (string) strip_tags($text);
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
     * @return U_Sentence[]
     */
    public function getSentences()
    {
        if (!is_null($this->sentences)) {
            return $this->sentences;
        }

        $this->sentences = array();

        $sentences = preg_split('/' . $this->stopSymbols . '/iu', $this->text);
        foreach ($sentences as $sentence) {
            $this->sentences[] = U_Sentence::i($sentence);
        }

        return $this->sentences;
    }

    /**
     * @return U_Word[]
     */
    public function getWords()
    {
        $result = array();
        foreach ($this->getSentences() as $sentence) {
            $result = array_merge($result, $sentence->getWords());
        }

        return $result;
    }

    public function __toString()
    {
        return $this->text;
    }
}