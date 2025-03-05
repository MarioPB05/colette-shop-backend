<?php

namespace App\Repository;

use App\Entity\GemTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GemTransaction>
 */
class GemTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GemTransaction::class);
    }

    public function addGemTransaction(int $userId, int $amount): int
    {
        $conn = $this->getEntityManager()->getConnection();
        $gemSQL = "INSERT INTO gem_transaction (gems, date, user_id)
                VALUES (:amount, NOW(), :userId)";

        $userSQL = "UPDATE public.user
                    SET gems = gems + :amount
                    WHERE id = :userId";

        $conn->executeQuery($gemSQL, ['userId' => $userId, 'amount' => $amount]);
        $conn->executeQuery($userSQL, ['userId' => $userId, 'amount' => $amount]);

        return $conn->lastInsertId();
    }
}
