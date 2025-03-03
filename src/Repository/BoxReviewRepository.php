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

    /**
     * It returns if a user has reviewed a box
     *
     * @param int $boxId
     * @param int $userId
     * @return bool
     */
    public function userHasReviewedBox(int $boxId, int $userId): bool
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT COUNT(*) as count
                FROM box_review
                WHERE box_id = :boxId AND user_id = :userId";

        $result = $conn->executeQuery($sql, ['boxId' => $boxId, 'userId' => $userId]);
        return $result->fetchOne() > 0;
    }

    /**
     * It adds a review to a box
     *
     * @param int $boxId
     * @param int $userId
     * @param int $rating
     * @param string $comment
     */
    public function addBoxReview(int $boxId, int $userId, int $rating, string $comment): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "INSERT INTO box_review (box_id, user_id, rating, comment, post_date)
                VALUES (:boxId, :userId, :rating, :comment, NOW())";

        $conn->executeQuery($sql, ['boxId' => $boxId, 'userId' => $userId, 'rating' => $rating, 'comment' => $comment]);
    }
}
