<?php

namespace App\Repository;

use App\Entity\Brawler;
use App\Entity\User;
use App\Entity\UserBrawler;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserBrawler>
 */
class UserBrawlerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBrawler::class);
    }

    /**
     * Returns the brawlers that the user has.
     * @param User $user
     * @return array
     */
    public function getBrawlers(User $user): array
    {
        $conn = $this->getEntityManager()->getConnection();


        $sql = "select 
                b.id,
                b.image,
                b.pin_image as pin_image,
                b.model_image as model_image,
                b.portrait_image as portrait_image,
                b.name
            from user_brawler ub
            join brawler b on ub.brawler_id = b.id
            where ub.user_id = :userId;";

        $result = $conn->executeQuery($sql, ['userId' => $user->getId()]);

        return $result->fetchAllAssociative();
    }
}
