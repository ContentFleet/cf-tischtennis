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

    public function sendVictoryMessage(User $winner, User $looser,array $rankingUsers )
    {
        $text = "*".$winner->getDisplayName()."* won against " . $looser->getDisplayName() . "\n";

        $text .="\n*Ranking*\n";
        $text .= "#         Won             Lost                Rating               Name\n";
        foreach($rankingUsers as $key => $user){

            $text .= $key+1 . "         ";

            /** @var User $user */
            $nbWon = $user->getNbWon();
            $text .= $nbWon;
            $nbTab = 10 - strlen($nbWon) ? 10 - strlen($nbWon) : 1;
            for ($i = 1; $i <= $nbTab; $i++) {
                $text .= "  ";
            }

            $nbLost = $user->getNbLost();
            $text .= $nbLost;
            $nbTab = 10 - strlen($nbLost) ? 10 - strlen($nbLost) : 1;
            for ($i = 1; $i <= $nbTab; $i++) {
                $text .= "  ";
            }
            $text .= "\t";


            $eloRanking = $user->getEloRating();
            $text .= $eloRanking;
            $nbTab = 10 - mb_strlen($eloRanking) ? 10 - mb_strlen($eloRanking) : 1;
            for ($i = 1; $i <= $nbTab; $i++) {
                $text .= "  ";
            }
            $text .= "\t";

            $text .= $user->getDisplayName();
            $text .= "\n";
        }

        $postFields = ['text' => $text];
        $this->sendPostCurlRequest($this->slackHook , $postFields);
    }

    protected function sendPostCurlRequest($url, $postFields)
    {
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