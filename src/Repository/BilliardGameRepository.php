<?php

namespace App\Repository;

use App\Entity\BilliardGame;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BilliardGame|null find($id, $lockMode = null, $lockVersion = null)
 * @method BilliardGame|null findOneBy(array $criteria, array $orderBy = null)
 * @method BilliardGame[]    findAll()
 * @method BilliardGame[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BilliardGameRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BilliardGame::class);
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
            billiard_game_user AS gu1
                LEFT JOIN
            billiard_game_user AS gu2 ON gu1.billiard_game_id = gu2.billiard_game_id
                AND gu1.user_id != gu2.user_id
                LEFT JOIN
            billiard_game ON gu1.billiard_game_id = billiard_game.id
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
}
