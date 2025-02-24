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
     * @return Brawler[]
     */
    public function getBrawlers(User $user): Brawler
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            select ub.brawler_id, b.name, b.image, b.model_image
                from user_brawler ub
                left join public.brawler b on b.id = ub.brawler_id
                where ub.user_id = :userId;";

        $stmt = $conn->prepare($sql);

        $stmt->execute(['userId' => $user->getId()]);

        return $stmt->fetchAllAssociative();
    }
}
