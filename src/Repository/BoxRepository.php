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
        $sql = 'SELECT b.id, b.name, b.price, b.type, b.quantity as boxes_left, count(ufb.brawler_id) as favorite_brawlers_in_box, b.pinned, (COUNT(i.id) > 100) AS popular
                FROM box b
                LEFT JOIN box_brawler bb on b.id = bb.box_id
                LEFT JOIN user_favorite_brawlers ufb on bb.brawler_id = ufb.brawler_id and ufb.user_id = :userId
                LEFT JOIN inventory i on b.id = i.box_id
                LEFT JOIN box_daily bd on b.id = bd.box_id
                WHERE b.deleted = FALSE and bd.box_id IS NULL
                GROUP BY b.id';

        $result = $conn->executeQuery($sql, ['userId' => $user->getId()]);
        return $result->fetchAllAssociative();
    }

    /**
     * It returns all the free daily boxes that are going to be shown in the shop
     *
     * @param User $user
     * @return array
     * @throws Exception
     */
    public function getAllFreeDailyBoxesShop(User $user): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT 
                    b.id,
                    b.name,
                    b.type,
                    COUNT(ufb.brawler_id) AS favorite_brawlers_in_box,
                    bd.repeat_every_hours,
                    (COUNT(i.id) > 0) AS claimed,
                    MAX(i.collect_date) AS last_claimed
                FROM box b
                JOIN box_daily bd ON b.id = bd.box_id
                LEFT JOIN box_brawler bb ON b.id = bb.box_id
                LEFT JOIN user_favorite_brawlers ufb ON bb.brawler_id = ufb.brawler_id AND ufb.user_id = :userId
                LEFT JOIN inventory i ON b.id = i.box_id AND i.user_id = :userId 
                    AND i.collect_date > NOW() - INTERVAL '1 hour' * bd.repeat_every_hours
                WHERE b.deleted = FALSE
                GROUP BY b.id, bd.repeat_every_hours;";

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
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT b.id, b.name, b.price, b.quantity, b.type, b.pinned, NOT bd.box_id IS NULL as is_daily
                FROM box b
                LEFT JOIN box_daily bd on b.id = bd.box_id
                WHERE b.deleted = FALSE
                GROUP BY b.id, bd.box_id';

        $result = $conn->executeQuery($sql);
        return $result->fetchAllAssociative();
    }

    /**
     * It returns the details of a box
     *
     * @param int $boxId
     * @return array
     */
    public function getBoxDetails(int $boxId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT b.id, b.name, b.price, b.type, b.quantity as boxes_left, b.brawler_quantity, NOT bd.box_id IS NULL as is_daily, (COUNT(i.id) > 0) AS claimed 
                FROM box b
                LEFT JOIN box_daily bd on b.id = bd.box_id
                LEFT JOIN inventory i on b.id = i.box_id and i.collect_date > NOW() - INTERVAL \'1 hour\' * bd.repeat_every_hours
                WHERE b.deleted = FALSE and b.id = :boxId
                GROUP BY b.id, bd.box_id';
        return $conn->executeQuery($sql, ['boxId' => $boxId])->fetchAssociative();
    }

}
