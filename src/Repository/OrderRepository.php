<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function getAllOrders(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT 
                    o.invoice_number, 
                    o.purchase_date, 
                    o.state, 
                    u.username, 
                    b.image as user_image,
                    coalesce(od.discount, 0) as discount, 
                    sum(i.price) as total_price, 
                    sum(i.price) - coalesce(od.discount, 0) as total_with_discount
                from "order" o
                left join order_discount od on o.id = od.order_id
                left join "user" u on o.user_id = u.id
                left join inventory i on o.id = i.order_id
                left join brawler b on u.brawler_avatar = b.id 
                where o.cancelled = false
                group by o.id, u.username, b.image, od.discount, o.invoice_number, o.purchase_date, o.state';

        $result = $conn->executeQuery($sql);

        return $result->fetchAllAssociative();
    }
}
