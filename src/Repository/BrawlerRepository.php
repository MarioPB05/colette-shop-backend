<?php

namespace App\Repository;

use App\Entity\BoxBrawler;
use App\Entity\Brawler;
use App\Entity\User;
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

    public function getAllBrawlersForBoxEditor(): array
    {
        return $this->createQueryBuilder('b')
            ->select('b.id', 'b.name', 'b.image', 'r.name AS rarity')
            ->join('b.rarity', 'r')
            ->getQuery()
            ->getResult();
    }

    /**
     * It returns the brawlers that are in a box and the probability of getting them
     *
     * @param int $boxId
     * @param User $user
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getBrawlersProbabilityFromBox(int $boxId, User $user): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT b.id, b.name, b.image, b.rarity_id, r.name as rarity, bb.probability, NOT ufb.brawler_id IS NULL as user_favorite
                FROM box_brawler bb
                JOIN brawler b on bb.brawler_id = b.id
                JOIN rarity r on b.rarity_id = r.id
                LEFT JOIN user_favorite_brawlers ufb on bb.brawler_id = ufb.brawler_id and ufb.user_id = :userId
                WHERE bb.box_id = :boxId';

        $result = $conn->executeQuery($sql, ['boxId' => $boxId, 'userId' => $user->getId()]);

        return $result->fetchAllAssociative();
    }

    /**
     * It returns the brawlers that are in a box and the probability of getting them and the quantity that the user has
     *
     * @param int $boxId
     * @param int $userId
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getUserProbabilityBrawlersFromBox(int $box_id, int $user_id): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT b.id, b.name, b.image, b.model_image, bb.probability, COALESCE(SUM(ub.quantity), 0) as user_quantity, r.id as rarity_id
                FROM box_brawler bb
                JOIN brawler b ON bb.brawler_id = b.id
                JOIN rarity r ON b.rarity_id = r.id
                LEFT JOIN user_brawler ub ON ub.brawler_id = b.id and ub.user_id = :user_id
                WHERE bb.box_id = :box_id
                GROUP BY b.id, bb.probability, r.id';

        $result = $conn->executeQuery($sql, ['box_id' => $box_id, 'user_id' => $user_id]);
        return $result->fetchAllAssociative();
    }

    /**
     * It returns the brawlers that the user has obtained in a box and the quantity that the user had in the past
     *
     * @param int $user_id
     * @param int $item_id
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getInventoryBrawlers(int $user_id, int $item_id)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT 
            b.id, 
            b.name, 
            b.image, 
            COALESCE(SUM(CASE WHEN i.id = :item_id THEN ub.quantity END), 0) AS user_quantity_actual,
            COALESCE(SUM(CASE WHEN i.open_date < (SELECT i.open_date FROM inventory WHERE id = :item_id) THEN ub.quantity END), 0) AS user_quantity_past
        FROM inventory i
        LEFT JOIN user_brawler ub ON i.id = ub.inventory_id
        JOIN brawler b ON ub.brawler_id = b.id
        WHERE ub.user_id = :user_id
        GROUP BY b.id, ub.id
        ORDER BY ub.id';

        $result = $conn->executeQuery($sql, ['user_id' => $user_id, 'item_id' => $item_id]);
        return $result->fetchAllAssociative();
    }
}
