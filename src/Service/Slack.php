<?php
namespace App\Service;
use App\Entity\User;

/**
 * Created by PhpStorm.
 * User: lambeletjp
 * Date: 19.10.18
 * Time: 17:13
 */

class Slack
{

    protected $slackHook;

    public function __construct($slackHook)
    {
        $this->slackHook = $slackHook;
    }

    public function sendVictoryMessage(User $winner, User $looser)
    {
        $text = "*".$winner->getDisplayName()."* won against " . $looser->getDisplayName();
        $this->sendPostCurlRequest($this->slackHook , $text);
    }

    protected function sendPostCurlRequest($url, $text)
    {
        $postFields = ['text' => $text];
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->slackHook);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFields));
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);
    }
}