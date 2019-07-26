<?php

namespace App\Repository;

use App\Entity\BilliardEloHistory;
use App\Entity\EloHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EloHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method EloHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method EloHistory[]    findAll()
 * @method EloHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BilliardEloHistoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BilliardEloHistory::class);
    }

    public function saveCurrentEloRating(?\App\Entity\User $user, $userStats)
    {
        if (!$userStats) {
            $eloRating = 1500;
        } else {
            $eloRating = $userStats->getEloRating();
        }

        $eloHistory = new BilliardEloHistory();
        $eloHistory->setUser($user);
        $eloHistory->setEloRating($eloRating);
        $this->_em->persist($eloHistory);
        $this->_em->flush();
    }

    /**
     * @param int|null $userId
     * @param $eloHistory
     * @return mixed
     * @throws \Exception
     */
    public function getEloHistory(?int $userId, $monthsEloHistory)
    {
        $firstMonth = reset($monthsEloHistory);
        $minDate = new \DateTime($firstMonth);

        $db = $this->createQueryBuilder('eloRep')
            ->select('avg(eloRep.eloRating) as eloRatingAvg, max(eloRep.eloRating) as eloRatingMax, min(eloRep.eloRating) as eloRatingMin, DATE_FORMAT(eloRep.createdAt, \'%Y-%m\') as dateAsMonth')
            ->leftJoin('eloRep.user', 'user')
            ->andWhere('user.id = :userId')
            ->andWhere('eloRep.createdAt >= :minDate')
            ->orderBy('dateAsMonth', 'ASC')
            ->groupBy('dateAsMonth')
            ->setParameter('userId', $userId)
            ->setParameter('minDate', $minDate->format('Y-m-d'));
        $result = $db->getQuery()->getResult();

        $lastValue = null;
        $preparedData = [];
        foreach ($result as $value) {
            $crtDate = new \DateTime($value['dateAsMonth']);

            if (!$lastValue) {
                $preparedData[] = $value;
                $lastValue = $value;
                continue;
            }

            $lastDate = new \DateTime($lastValue['dateAsMonth']);
            $monthDiff = $lastDate->diff($crtDate)->m;
            for ($addMonth = 1; $addMonth < $monthDiff; $addMonth++) {
                $lastDate->add(new \DateInterval('P1M'));
                $lastValue['dateAsMonth'] = $lastDate->format('Y-m');
                $preparedData[] = $lastValue;
            }

            $preparedData[] = $value;
            $lastValue = $value;
        }

        $firstPreparedValue = reset($preparedData);
        $firstDate = new \DateTime($firstPreparedValue['dateAsMonth']);
        $lastPreparedValue = end($preparedData);
        $lastDate = new \DateTime($lastPreparedValue['dateAsMonth']);
        $missingMonth = [];

        if($monthsEloHistory) {
            foreach($monthsEloHistory as $monthEloHistory) {
                if($monthEloHistory){
                    $monthEloDate = new \DateTime($monthEloHistory);
                    if($monthEloDate < $firstDate) {
                        $blankData['eloRatingAvg'] = 0;
                        $blankData['eloRatingMax'] = 0;
                        $blankData['eloRatingMin'] = 0;
                        $blankData['dateAsMonth'] = $monthEloHistory;
                        $missingMonth[] = $blankData;
                    }
                    else{
                        $preparedData = array_merge($missingMonth,$preparedData);
                        $missingMonth = [];
                    }
                    if($monthEloDate > $lastDate){
                        $preparedData[] = $lastPreparedValue;
                    }
                }
            }
        }

        return $preparedData;
    }
}
