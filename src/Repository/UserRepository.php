<?php

namespace App\Repository;

use App\Entity\User;
use Chovanec\Rating\Rating;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    protected $rating;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getUpdatedEloScore(User $winnerUser, User $looserUser)
    {
        $rating = new Rating($winnerUser->getEloRating() , $looserUser->getEloRating(), Rating::WIN, Rating::LOST);
        $newRating = $rating->getNewRatings();
        return $newRating;
    }

    public function getAllEnabledUsers(array $criteria = [], array $orderBy, $limit = 150)
    {
        $onlyEnabled = [ 'enabled' => 1];
        $criteria = array_merge($onlyEnabled,$criteria);
        return $this->findBy($criteria, $orderBy, $limit);
    }

}
