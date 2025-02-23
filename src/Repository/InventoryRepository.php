<?php

namespace App\Repository;

use App\Entity\Inventory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inventory>
 */
class InventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventory::class);
    }

    /**
     * Returns the number of boxes collected per day in the last 30 days
     * @return Inventory[]
     */
    public function inventoryStats():array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql="select
                date(i.collect_date) as day,
                count(i.id) as boxes,
                sum(i.price) as total_price
            from inventory i
            where i.collect_date >= current_date - interval '29 days'
            group by day
            
            union
            
            select
                day::date,
                0,
                0
            from generate_series(
                         date_trunc('day', current_date) - interval '29 days',
                         date_trunc('day', current_date),
                         interval '1 day'
                 ) day
            where day::date not in (
                select date_trunc('day', i.collect_date)::date
                from inventory i
                where i.collect_date >= current_date - interval '29 days'
            )
            order by day";

        $result = $conn->executeQuery($sql);

        return $result->fetchAllAssociative();

    }


    /**
     * Returns the number of gems collected per day in the last 30 days
     * @return Inventory[]
     */
    public function gemStat(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "select
                    date(gt.date) as day,
                    sum(abs(gt.gems)) as gems
                from  gem_transaction gt
                where gt.date >= current_date - interval '29 days' and gt.gems < 0
                group by day
                
                union
                
                select
                    day::date,
                    0
                from generate_series(
                             date_trunc('day', current_date) - interval '29 days',
                             date_trunc('day', current_date),
                             interval '1 day'
                     ) day
                where day::date not in (
                    select date_trunc('day', gt.date)::date
                    from gem_transaction gt
                    where gt.date >= current_date - interval '29 days'
                )
                order by day";

        $result = $conn->executeQuery($sql);

        return $result->fetchAllAssociative();
    }

    /**
     * Returns the details of the inventory for the given order
     * @param int $order_id
     * @return \mixed[][]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getInventoryForOrderDetails(int $order_id)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "select
                    b.id,
                    i.price,
                    i.collect_date as collect_date,
                    b.name as box_name,
                    b.type as box_type
                from inventory i
                join box b on i.box_id = b.id
                where i.order_id = :order_id";

        $result = $conn->executeQuery($sql, ['order_id' => $order_id]);

        return $result->fetchAllAssociative();
    }


}