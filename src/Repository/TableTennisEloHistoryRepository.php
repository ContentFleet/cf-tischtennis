<?php

namespace App\Repository;

use App\Entity\EloHistory;
use App\Entity\TableTennisEloHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EloHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method EloHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method EloHistory[]    findAll()
 * @method EloHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TableTennisEloHistoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TableTennisEloHistory::class);
    }

    public function saveCurrentEloRating(?\App\Entity\User $user, $userStats)
    {
        if (!$userStats) {
            $eloRating = 1500;
        } else {
            $eloRating = $userStats->getEloRating();
        }

        $eloHistory = new TableTennisEloHistory();
        $eloHistory->setUser($user);
        $eloHistory->setEloRating($eloRating);
        $this->_em->persist($eloHistory);
        $this->_em->flush();
    }

    /**
     * @param int|null $userId
     * @return mixed
     * @throws \Exception
     */
    public function getEloHistory(?int $userId)
    {
        $db = $this->createQueryBuilder('eloRep')
            ->select('avg(eloRep.eloRating) as eloRatingAvg, max(eloRep.eloRating) as eloRatingMax, min(eloRep.eloRating) as eloRatingMin, DATE_FORMAT(eloRep.createdAt, \'%Y-%m\') as dateAsMonth')
            ->leftJoin('eloRep.user', 'user')
            ->andWhere('user.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('dateAsMonth')
            ->groupBy('dateAsMonth')
            ->setMaxResults(12);
        $result = $db->getQuery()->getResult();

        $lastValue = null;
        $preparedData = [];
        foreach ($result as $value) {
            if (!$lastValue) {
                $preparedData[] = $value;
                $lastValue = $value;
                continue;
            }

            $lastDate = new \DateTime($lastValue['dateAsMonth']);
            $crtDate = new \DateTime($value['dateAsMonth']);
            $monthDiff = $lastDate->diff($crtDate)->m;
            for($addMonth = 1; $addMonth < $monthDiff; $addMonth++) {
                $lastDate->add(new \DateInterval('P1M'));
                $lastValue['dateAsMonth'] = $lastDate->format('Y-m');
                $preparedData[] = $lastValue;
            }

            $preparedData[] = $value;
            $lastValue = $value;
        }

        return $preparedData;
    }

//    /**
//     * @return EloHistory[] Returns an array of EloHistory objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EloHistory
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
