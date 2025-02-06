<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 *
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Returns all orders but if brawlTag is provided, it returns only orders for that user.
     * @param string|null $brawlTag
     * @return array
     */
    public function getAllOrders(?string $brawlTag = null): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT 
                o.invoice_number, 
                o.purchase_date, 
                o.state, 
                u.username, 
                b.image AS user_image,
                COALESCE(od.discount, 0) AS discount, 
                SUM(i.price) AS total_price, 
                SUM(i.price) - SUM(COALESCE(od.discount, 0)) AS total_with_discount
            FROM \"order\" o
            LEFT JOIN order_discount od ON o.id = od.order_id
            LEFT JOIN \"user\" u ON o.user_id = u.id
            LEFT JOIN inventory i ON o.id = i.order_id
            LEFT JOIN brawler b ON u.brawler_avatar = b.id
            WHERE o.cancelled IS FALSE";

        $params = [];
        if ($brawlTag !== null) {
            $sql .= " AND u.brawl_tag = :brawlTag";
            $params['brawlTag'] = $brawlTag;
        }

        $sql .= " GROUP BY o.id, o.invoice_number, o.purchase_date, o.state, u.username, b.image, od.discount";

        $result = $conn->executeQuery($sql, $params);

        return $result->fetchAllAssociative();
    }
}
