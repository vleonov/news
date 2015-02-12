<?php

class U_Rss
{
    private static $instances = array();

    private $url;

    private function __construct($url)
    {
        $curl = curl_init($url);
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_AUTOREFERER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 10,
            )
        );

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);

        if (!$response || $info['http_code'] != 200) {
            throw new Exception('Error getting feed ', $url);
        }

        $xml = new DOMDocument();
        $xml->loadXML($response);

        $this->url = (string) $url;
        $this->xml = $xml;
    }

    /**
     * @param string $url
     * @return self
     */
    public static function i($url)
    {
        if (!isset(self::$instances[$url])) {
            self::$instances[$url] = new self($url);
        }

        return self::$instances[$url];
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        /**
         * @var $channel DOMElement
         */
        $channel = $this->xml->getElementsByTagName('channel')->item(0);
        return $channel->getElementsByTagName('title')->item(0)->nodeValue;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        /**
         * @var $channel DOMElement
         */
        $channel = $this->xml->getElementsByTagName('channel')->item(0);
        return $channel->getElementsByTagName('description')->item(0)->nodeValue;
    }

    public function getItems()
    {
        $result = [];

        /**
         * @var $channel DOMElement
         */
        $channel = $this->xml->getElementsByTagName('channel')->item(0);
        $items = $channel->getElementsByTagName('item');

        /**
         * @var $item DOMElement
         */
        foreach ($items as $item) {
            $title = $item->getElementsByTagName('title')->item(0);
            $descr = $item->getElementsByTagName('description')->item(0);
            $url = $item->getElementsByTagName('link')->item(0);
            $pubDate = $item->getElementsByTagName('pubDate')->item(0);
            $categories = $item->getElementsByTagName('category');
//            $content = $item->getElementsByTagNameNS("http://purl.org/rss/1.0/modules/content/", 'content');
//
//            var_dump($title);
//            var_dump($content);
//
//            exit();

            $item = array(
                'title' => $title ? $title->nodeValue : 'n/a',
                'descr'  => $descr ? $descr->nodeValue : 'n/a',
                'url' => $url ? $url->nodeValue : null,
                'pubDate' => $pubDate ? strtotime($pubDate->nodeValue) : time(),
                'tags' => array(),
            );

            foreach ($categories as $category) {
                $item['tags'][] = $category->nodeValue;
            }

            $result[] = $item;
        }

        return $result;
    }
}