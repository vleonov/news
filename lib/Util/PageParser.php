<?php

class U_PageParser
{
    private static $instances = array();
    private $apiUrl = 'https://readability.com/api/content/v1/parser?token=604cb41a3532ed69e596165441712e9f5cf8d979&url=%s';

    private $url;
    private $data;

    private function __construct($url)
    {
        $curl = curl_init(
            sprintf(
                $this->apiUrl,
                urlencode($url)
            )
        );
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_AUTOREFERER    => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 10,
            )
        );

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);

        if (!$response || $info['http_code'] != 200) {
            throw new Exception('Error getting page ' . $url);
        }

        $this->url = (string) $url;
        $this->data = json_decode($response, true);
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

    public function getContent()
    {
        return $this->data['content'];
    }
}