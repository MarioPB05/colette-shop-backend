<?php

namespace App\Repository;

use App\Entity\Brawler;
use App\Entity\UserBrawler;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Brawler>
 */
class BrawlerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Brawler::class);
    }

    public function getAllBrawlers(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT b.id, b.name, b.pin_image as pin_image, r.name AS rarity,
                    (
                        SELECT COUNT(u.user_id)
                        FROM user_brawler u
                        WHERE u.brawler_id = b.id
                    ) AS num_people,
                    (
                        SELECT COUNT(u.user_id)
                        FROM user_favorite_brawlers u
                        WHERE u.brawler_id = b.id
                    ) AS num_favourite
                FROM brawler AS b
                JOIN rarity AS r ON b.rarity_id = r.id';

        $result = $conn->executeQuery($sql);

        return $result->fetchAllAssociative();
    }

    /**
     * It counts the number of people who have the favorite brawler
     * @return Brawler[]
     */
    public function findCountPersonByFavouriteBrawler(int $brawlerId): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT COUNT(u.user_id) FROM user_favorite_brawlers u WHERE u.brawler_id = :brawlerId';

        $result = $conn->executeQuery($sql, ['brawlerId' => $brawlerId]);

        return $result->fetchOne();
    }

    /**
     * It counts the number of brawlers that a person has
     * @return UserBrawler[]
     */
    public function findCountBrawlersByPerson(int $brawlerId): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT COUNT(u.user_id) FROM user_brawler u WHERE u.brawler_id = :brawlerId';

        $result = $conn->executeQuery($sql, ['brawlerId' => $brawlerId]);

        return $result->fetchOne();
    }
}
