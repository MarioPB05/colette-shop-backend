<?php

namespace App\Repository;

use App\Entity\BoxReview;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BoxReview>
 */
class BoxReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoxReview::class);
    }

    /**
     * It returns all the reviews of a box
     *
     * @param int $boxId
     * @return array
     */
    public function getBoxReviews(int $boxId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT br.id, br.rating, br.comment, br.post_date, u.username, u.id as user_Id
                FROM box_review br
                JOIN \"user\" u on br.user_id = u.id
                WHERE br.box_id = :boxId
                ORDER BY br.post_date DESC";

        $result = $conn->executeQuery($sql, ['boxId' => $boxId]);
        return $result->fetchAllAssociative();
    }
}
