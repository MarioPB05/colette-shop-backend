<?php

namespace App\Repository;

use App\DTO\box\AdminGetBoxesResponse;
use App\Entity\Box;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * It returns all the boxes that are not deleted
     *
     * @return array<Box>
     */
    public function getAllBoxes(): array
    {
        $results = $this->createQueryBuilder('b')
            ->select('b.id', 'b.name', 'b.price', 'b.quantity', 'b.type', 'b.pinned')
            ->where('b.deleted = FALSE')
            ->getQuery()
            ->getResult();

        return array_map(fn($result) => new AdminGetBoxesResponse(
            $result['id'],
            $result['name'],
            $result['price'],
            $result['quantity'],
            $result['type'],
            $result['pinned']
        ), $results);
    }

}
