<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
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
                o.id,
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

    public function getOrderDetails(int $orderId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "select
                    o.invoice_number,
                    o.purchase_date,
                    o.state,
                    u.id as from_id,
                    u.username as from_username,
                    c.name as from_name,
                    c.surname as from_surname,
                    c.dni as from_dni,
                    case
                        when i.user_id != o.user_id then u2.id
                        end as to_id,
                    case
                        when i.user_id != o.user_id then u2.username
                        end as to_username,
                    case
                        when i.user_id != o.user_id then c2.name
                        end as to_name,
                    case
                        when i.user_id != o.user_id then c2.surname
                        end as to_surname,
                    case
                        when i.user_id != o.user_id then c2.dni
                        end as to_dni,
                    coalesce(od.discount, 0) as discount,
                    sum(i.price) as total,
                    u.gems,
                    b.image as user_image
                from \"order\" o
                         left join order_discount od on o.id = od.order_id
                         left join \"user\" u on o.user_id = u.id
                         left join client c on u.client_id = c.id
                         left join inventory i on o.id = i.order_id
                         left join \"user\" u2 on i.user_id = u2.id
                         left join client c2 on u2.client_id = c2.id
                         left join brawler b on u.brawler_avatar = b.id
                where o.cancelled is false and o.id = :orderId
                group by o.id, o.invoice_number, i.user_id, o.purchase_date, o.state, u.id, u.username, c.name, c.surname, c.dni, u2.id, u2.username, c2.name, c2.surname, c2.dni, od.discount, u.gems, b.image";

        $result = $conn->executeQuery($sql, ['orderId' => $orderId]);

        return $result->fetchAssociative();

    }


    /**
     * Returns all orders for a user.
     * @param User $user
     * @return array
     */
    public function getOrdersByUser(User $user): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT
                    o.id,
                    o.invoice_number,
                    o.purchase_date,
                    u.username,
                    count(i.id) as total_items,
                    COALESCE(od.discount, 0) AS discount,
                    SUM(i.price) AS total_price,
                    SUM(i.price) - SUM(COALESCE(od.discount, 0)) AS total_with_discount,
                    (SELECT u2.username
                     FROM \"order\" o2
                     JOIN inventory i2 ON o2.id = i2.order_id
                     JOIN \"user\" u2 ON i2.user_id = u2.id
                     WHERE o2.user_id = u.id
                       AND o2.user_id <> i2.user_id
                     LIMIT 1) AS gift_username
                FROM \"order\" o
                    LEFT JOIN order_discount od ON o.id = od.order_id
                    LEFT JOIN \"user\" u ON o.user_id = u.id
                    LEFT JOIN inventory i ON o.id = i.order_id
                    LEFT JOIN brawler b ON u.brawler_avatar = b.id
                WHERE o.cancelled IS FALSE AND u.id = :userId
                GROUP BY o.id, o.invoice_number, o.purchase_date, o.state, u.username, b.image, od.discount, u.id";

        $result = $conn->executeQuery($sql, ['userId' => $user->getId()]);

        return $result->fetchAllAssociative();

    }
}
