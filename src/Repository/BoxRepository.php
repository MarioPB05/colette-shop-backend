<?php

namespace App\Repository;

use App\DTO\box\BoxShopResponse;
use App\Entity\Box;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Box>
 */
class BoxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Box::class);
    }

    /**
     * It returns all the boxes that are going to be shown in the shop
     *
     * @param User $user
     * @return array<BoxShopResponse>
     * @throws Exception
     */
    public function getAllBoxesShop(User $user): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT b.id, b.name, b.price, b.type, b.quantity as boxesLeft, count(ufb.brawler_id) as favoriteBrawlersInBox, b.pinned, (COUNT(i.id) > 100) AS popular
                FROM box b
                LEFT JOIN box_brawler bb on b.id = bb.box_id
                LEFT JOIN user_favorite_brawlers ufb on bb.brawler_id = ufb.brawler_id and ufb.user_id = :userId
                LEFT JOIN inventory i on b.id = i.box_id
                WHERE b.deleted = FALSE
                GROUP BY b.id';

        $result = $conn->executeQuery($sql, ['userId' => $user->getId()]);
        return $result->fetchAllAssociative();
    }

    /**
     * It returns all the boxes that are not deleted
     *
     * @return array<Box>
     */
    public function getAllBoxes(): array
    {
        return $this->createQueryBuilder('b')
            ->select('b.id', 'b.name', 'b.price', 'b.quantity', 'b.type', 'b.pinned')
            ->where('b.deleted = FALSE')
            ->getQuery()
            ->getResult();
    }

}
