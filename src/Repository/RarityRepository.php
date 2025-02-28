<?php

namespace App\Repository;

use App\Entity\Rarity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rarity>
 */
class RarityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rarity::class);
    }

    /**
     * Returns all rarities details
     *
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getAllDetailRarities(int $user_id): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT r.id, r.name, r.color, COUNT(distinct ub.brawler_id) AS brawlers_of_rarity_unlocked, COUNT(distinct b.id) AS total_brawlers_of_rarity
                FROM rarity AS r
                LEFT JOIN user_brawler AS ub ON r.id = ub.brawler_id and ub.user_id = :user_id
                LEFT JOIN brawler AS b ON r.id = b.rarity_id
                GROUP BY r.id
                ORDER BY r.id';

        $result = $conn->executeQuery($sql, ['user_id' => $user_id]);

        return $result->fetchAllAssociative();
    }
}
