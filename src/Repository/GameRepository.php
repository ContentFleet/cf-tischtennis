<?php

namespace App\Repository;

use App\Entity\Game;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Game|null find($id, $lockMode = null, $lockVersion = null)
 * @method Game|null findOneBy(array $criteria, array $orderBy = null)
 * @method Game[]    findAll()
 * @method Game[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function getStatsAgainstPlayers($userId)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT 
            gu1.user_id,
            gu2.user_id,
            SUM(CASE WHEN winner_user_id = gu1.user_id THEN 1 ELSE  0 END ) as \'nb_win\',
            SUM(CASE WHEN winner_user_id != gu1.user_id THEN 1 ELSE  0 END ) as \'nb_loose\',
            SUM(CASE WHEN winner_user_id = gu1.user_id THEN 1 ELSE  -1 END ) as \'avg_win_loose\',
            user.id,
            user.firstname,
            user.lastname
        FROM
            game_user AS gu1
                LEFT JOIN
            game_user AS gu2 ON gu1.game_id = gu2.game_id
                AND gu1.user_id != gu2.user_id
                LEFT JOIN
            game ON gu1.game_id = game.id
                LEFT JOIN
            user ON gu2.user_id = user.id
        WHERE
            gu1.user_id = :user_id
        GROUP BY gu1.user_id , gu2.user_id
        ORDER BY avg_win_loose DESC';

        $stmt = $conn->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();

    }

    public function getPossibleScore($eloRating1, $eloRating2)
    {
        $possibleScore  = [
            '3-0' => '3-0',
            '3-1' => '3-1',
            '3-2' => '3-2',
            '2-3' => '2-3',
            '1-3' => '1-3',
            '0-3' => '0-3'
        ];

        if($eloRating1 - $eloRating2 >= 200) {
          $possibleScore  = [
              '3-0' => '3-0',
              '2-1' => '2-1',
              '1-1' => '1-1',
              '0-1' => '0-1'
          ];
        }
        elseif($eloRating1 - $eloRating2 >= 100) {
            $possibleScore  = [
                '3-0' => '3-0',
                '3-1' => '3-1',
                '2-2' => '2-2',
                '1-2' => '1-2',
                '0-2' => '0-2'
            ];
        }
        elseif($eloRating2 - $eloRating1 >= 200) {
            $possibleScore  = [
                '1-0' => '1-0',
                '1-1' => '1-1',
                '1-2' => '1-2',
                '0-3' => '0-3'
            ];
        }
        elseif($eloRating2 - $eloRating1 >= 100) {
            $possibleScore  = [
                '2-0' => '2-0',
                '2-1' => '2-1',
                '2-2' => '2-2',
                '1-2' => '1-2',
                '0-2' => '0-2'
            ];
        }
        return $possibleScore;
    }

    /**
     * @param $gameScore
     * @param $players
     * @param User $winnerUser
     * @return bool
     */
    public function isWinnerWithScoreCorrect($gameScore, $players, User $winnerUser) : bool
    {
        $realWinner = null;
        $aScore = preg_split('/-/',$gameScore);
        $score1 = $aScore[0];
        $score2 = $aScore[1];

        /** @var User $player1 */
        $player1 = $players[0];
        $eloRating1 = $player1->getEloRating();
        /** @var User $player2 */
        $player2 = $players[1];
        $eloRating2 = $player2->getEloRating();


        if($eloRating1 - $eloRating2 >= 200){
            if($score2 >= 1){
                $realWinner = $player2;
            }
            elseif($score1 == 3){
                $realWinner = $player1;
            }
        }
        elseif($eloRating1 - $eloRating2 >= 100){
            if($score2 >= 2){
                $realWinner = $player2;
            }
            elseif($score1 == 3){
                $realWinner = $player1;
            }
        }
        elseif($eloRating2 - $eloRating1 >= 200){
            if($score1 >= 1){
                $realWinner = $player1;
            }
            elseif($score2 == 3){
                $realWinner = $player2;
            }
        }
        elseif($eloRating2 - $eloRating1 >= 100){
            if($score1 >= 2){
                $realWinner = $player1;
            }
            elseif($score2 == 3){
                $realWinner = $player2;
            }
        }
        elseif($eloRating1 - $eloRating2 < 100 && $eloRating1 - $eloRating2 > 0){
            if($score1 > $score2){
                $realWinner = $player1;
            }
            elseif($score2 > $score1){
                $realWinner = $player2;
            }
        }
        elseif($eloRating2 - $eloRating1 < 100 && $eloRating2 - $eloRating1 > 0){
            if($score1 > $score2){
                $realWinner = $player1;
            }
            elseif($score2 > $score1){
                $realWinner = $player2;
            }
        }

        if(!$realWinner){
            return false;
        }

        return !!($realWinner->getId() == $winnerUser->getId());
    }
}
