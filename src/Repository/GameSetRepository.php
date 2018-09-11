<?php

namespace App\Repository;

use App\Entity\GameSet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GameSet|null find($id, $lockMode = null, $lockVersion = null)
 * @method GameSet|null findOneBy(array $criteria, array $orderBy = null)
 * @method GameSet[]    findAll()
 * @method GameSet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameSetRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GameSet::class);
    }

//    /**
//     * @return Set[] Returns an array of Set objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Set
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
