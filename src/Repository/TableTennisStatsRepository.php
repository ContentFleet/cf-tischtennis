<?php

namespace App\Repository;

use App\Entity\TableTennisStats;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TableTennisStats|null find($id, $lockMode = null, $lockVersion = null)
 * @method TableTennisStats|null findOneBy(array $criteria, array $orderBy = null)
 * @method TableTennisStats[]    findAll()
 * @method TableTennisStats[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TableTennisStatsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TableTennisStats::class);
    }

    public function getUserRanking(int $limit = null)
    {
        $params = ['eloRating' => 'DESC'];
        $ranking = $this->findBy(array(), $params, $limit);
        return $ranking;
    }

    public function userHasWon(?User $user, int $eloRating)
    {
        $tableTennisStats = $this->findOneBy(['user' => $user]);
        if(!$tableTennisStats) {
            $tableTennisStats = new TableTennisStats();
            $tableTennisStats->setUser($user);
        }
        $tableTennisStats->setEloRating($eloRating);
        $tableTennisStats->setNbWon($tableTennisStats->getNbWon() + 1);
        $this->_em->persist($tableTennisStats);
        $this->_em->flush();
    }

    public function userHasLost(?User $user, int $eloRating)
    {
        $tableTennisStats = $this->findOneBy(['user' => $user]);
        if(!$tableTennisStats) {
            $tableTennisStats = new TableTennisStats();
            $tableTennisStats->setUser($user);
        }
        $tableTennisStats->setEloRating($eloRating);
        $tableTennisStats->setNbLost($tableTennisStats->getNbLost() + 1);
        $this->_em->persist($tableTennisStats);
        $this->_em->flush();
    }
}
