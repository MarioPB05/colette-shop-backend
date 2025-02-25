<?php

namespace App\Repository;

use App\Entity\Inventory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inventory>
 */
class InventoryRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Inventory::class);
        $this->entityManager = $entityManager;
    }

    /**
     * Returns the inventory box with the given id
     *
     * @param int $id
     * @return array
     */
    public function getInventoryBox(int $id): array
    {
        return $this->createQueryBuilder('i')
            ->select('i.id', 'b.id as box_id', 'b.type', 'b.brawler_quantity', 'i.open')
            ->join('i.box', 'b')
            ->where('i.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();
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

    /**
     * Validate if the inventory is available and the quantity is correct
     *
     * @param int $id
     * @param int $user_id
     * @param int $quantity
     * @return bool
     */
    public function validateInventory(int $id, int $user_id, int $quantity = 1): bool
    {
        return $this->createQueryBuilder('i')
            ->select('count(i.id)')
            ->join('i.box', 'b')
            ->where('i.id = :id and i.user = :user_id and i.open = false and b.brawler_quantity = :quantity')
            ->setParameter('id', $id)
            ->setParameter('user_id', $user_id)
            ->setParameter('quantity', $quantity)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    /**
     * Save the results of opening a box
     *
     * @param int $id_item
     * @param array $data
     * @param int $user_id
     * @return array|string[]
     */
    public function saveBoxOpenResults(int $id_item, array $data, int $user_id): array
    {
        if (empty($data)) {
            return [
                'status' => 'error',
                'message' => 'No brawler data',
                'code' => 400
            ];
        }

        if (!$this->validateInventory($id_item, $user_id, count($data))) {
            return [
                'status' => 'error',
                'message' => 'Invalid inventory',
                'code' => 400
            ];
        }

        $this->entityManager->beginTransaction();
        $data = array_count_values($data);
        $conn = $this->getEntityManager()->getConnection();

        $brawler_query = "INSERT INTO user_brawler (quantity, brawler_id, user_id, inventory_id)
                VALUES (:quantity, :brawler_id, :user_id, :inventory_id)";

        $inventory_query = "UPDATE inventory SET open = true WHERE id = :id_item";

        $brawler_stmt = $conn->prepare($brawler_query);
        $inventory_stmt = $conn->prepare($inventory_query);

        try {
            foreach ($data as $brawler_id => $quantity) {
                $brawler_stmt->executeQuery([
                    'quantity' => $quantity,
                    'brawler_id' => $brawler_id,
                    'user_id' => $user_id,
                    'inventory_id' => $id_item
                ]);
            }

            $inventory_stmt->executeQuery(['id_item' => $id_item]);
            $this->entityManager->commit();

        }catch (\Exception $e){
            $this->entityManager->rollback();
            return [
                'status' => 'error',
                'message' => 'Error saving data',
                'code' => 500
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Data saved',
            'code' => 200
        ];
    }
}