<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

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

    public function getOrderDetails(int $orderId, bool $excludeCancelled = true): array
    {
        $where = $excludeCancelled ? 'AND o.cancelled IS FALSE' : '';

        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT
                  o.invoice_number,
                  o.purchase_date,
                  o.state,

                  -- Datos del usuario que hizo el pedido (from_user)
                  from_user.id AS from_id,
                  from_user.username AS from_username,
                  from_client.name AS from_name,
                  from_client.surname AS from_surname,
                  from_client.dni AS from_dni,
                  from_brawler.image AS from_user_image,

                  -- Datos del usuario que recibe el pedido (to_user)
                  to_user.id AS to_id,
                  to_user.username AS to_username,
                  to_client.name AS to_name,
                  to_client.surname AS to_surname,
                  to_client.dni AS to_dni,

                  -- Datos de las cajas compradas
                  b.id AS box_id,
                  b.name AS box_name,
                  b.type AS box_type,
                  COUNT(i.id) AS quantity,  -- Cantidad de cajas compradas
                  i.price AS unit_price,
                  COUNT(i.id) * i.price AS total,  -- Precio total por tipo de caja

                  -- Descuento aplicado
                  COALESCE(od.discount, 0) AS discount,

                  -- Transacciones de gemas
                  COALESCE(ABS(gt.gems), 0) AS gems

              FROM \"order\" o
                       -- Descuentos y transacciones de gemas
                       LEFT JOIN order_discount od ON o.id = od.order_id
                       LEFT JOIN gem_transaction gt ON od.transaction_id = gt.id

                  -- Usuario que hizo el pedido (from_user)
                       LEFT JOIN \"user\" from_user ON o.user_id = from_user.id
                       LEFT JOIN client from_client ON from_user.client_id = from_client.id
                       LEFT JOIN brawler from_brawler ON from_user.brawler_avatar = from_brawler.id

                  -- Inventario (cajas compradas)
                       LEFT JOIN inventory i ON o.id = i.order_id
                       LEFT JOIN box b ON i.box_id = b.id

                  -- Usuario que recibe el pedido (to_user) -> Se asume que el usuario que recibe es quien aparece en inventory
                       LEFT JOIN \"user\" to_user ON i.user_id = to_user.id
                       LEFT JOIN client to_client ON to_user.client_id = to_client.id

              WHERE o.cancelled IS FALSE
                AND o.id = :orderId

              GROUP BY
                  o.invoice_number, o.purchase_date, o.state,
                  from_user.id, from_user.username, from_client.name, from_client.surname, from_client.dni, from_brawler.image,
                  to_user.id, to_user.username, to_client.name, to_client.surname, to_client.dni,
                  b.id, b.name, b.type, i.price, od.discount, gt.gems

              ORDER BY b.id";

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
                     WHERE o2.id = o.id
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

    public function generateInvoiceNumber(): string {
        return strtoupper(substr(Uuid::v7()->toBase32(), 0, 10));
    }
}
