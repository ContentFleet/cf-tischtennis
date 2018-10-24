<?php
/**
 * Created by PhpStorm.
 * User: lambeletjp
 * Date: 24.10.18
 * Time: 13:44
 */

namespace App\Service;


class Giphy
{
    protected $giphyApiKey;

    const HTTP_API_GIPHY_GIFS_RANDOM = "http://api.giphy.com/v1/gifs/random";

    public function __construct(string $giphyApiKey)
    {
        $this->giphyApiKey = $giphyApiKey;
    }

    /**
     * @return null|string
     */
    public function getWinningGifUrl() : ?string
    {
        $gifJson = $this->sendGetCurlRequest('happy');
        $gif = json_decode($gifJson, true);
        if($gif && isset($gif['data']) && isset($gif['data']['images']) && isset($gif['data']['images']['fixed_height_small'])){
            return $gif['data']['images']['fixed_height_small']['url'];
        }
        return null;
    }

    protected function sendGetCurlRequest($tag)
    {
        $ch = curl_init();

        $url = "" . self::HTTP_API_GIPHY_GIFS_RANDOM . "?api_key=" . $this->giphyApiKey . "&tag=" . $tag;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");


        $headers = array();
        $headers[] = "Cache-Control: no-cache";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            $result = null;
        }
        curl_close ($ch);

        return $result;
    }

}