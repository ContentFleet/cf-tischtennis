<?php

namespace App\Repository;

use App\Entity\Game;
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
            gu1.user_id = 1
        GROUP BY gu1.user_id , gu2.user_id
        ORDER BY avg_win_loose DESC';

        $stmt = $conn->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();

    }
}
