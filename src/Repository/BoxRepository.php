<?php

namespace App\Repository;

use App\DTO\box\BoxShopResponse;
use App\DTO\box\CreateBoxRequest;
use App\DTO\box\CreateDailyBoxRequest;
use App\Entity\Box;
use App\Entity\BoxBrawler;
use App\Entity\BoxDaily;
use App\Entity\Brawler;
use App\Entity\User;
use App\Enum\BoxType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
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
     * It returns all the boxes that are going to be shown in the shop
     *
     * @param User $user
     * @return array<BoxShopResponse>
     * @throws Exception
     */
    public function getAllBoxesShop(User $user): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT b.id, b.name, b.price, b.type, b.quantity as boxes_left, count(distinct ufb.brawler_id) as favorite_brawlers_in_box, b.pinned, (COUNT(i.id) > 100) AS popular
                FROM box b
                LEFT JOIN box_brawler bb on b.id = bb.box_id
                LEFT JOIN user_favorite_brawlers ufb on bb.brawler_id = ufb.brawler_id and ufb.user_id = :userId
                LEFT JOIN inventory i on b.id = i.box_id
                LEFT JOIN box_daily bd on b.id = bd.box_id
                WHERE b.deleted = FALSE and bd.box_id IS NULL
                GROUP BY b.id';

        $result = $conn->executeQuery($sql, ['userId' => $user->getId()]);
        return $result->fetchAllAssociative();
    }

    /**
     * It returns all the free daily boxes that are going to be shown in the shop
     *
     * @param User $user
     * @return array
     * @throws Exception
     */
    public function getAllFreeDailyBoxesShop(User $user): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT 
                    b.id,
                    b.name,
                    b.type,
                    COUNT(ufb.brawler_id) AS favorite_brawlers_in_box,
                    bd.repeat_every_hours,
                    (COUNT(i.id) > 0) AS claimed,
                    MAX(i.collect_date) AS last_claimed
                FROM box b
                JOIN box_daily bd ON b.id = bd.box_id
                LEFT JOIN box_brawler bb ON b.id = bb.box_id
                LEFT JOIN user_favorite_brawlers ufb ON bb.brawler_id = ufb.brawler_id AND ufb.user_id = :userId
                LEFT JOIN inventory i ON b.id = i.box_id AND i.user_id = :userId 
                    AND i.collect_date > NOW() - INTERVAL '1 hour' * bd.repeat_every_hours
                WHERE b.deleted = FALSE
                GROUP BY b.id, bd.repeat_every_hours;";

        $result = $conn->executeQuery($sql, ['userId' => $user->getId()]);
        return $result->fetchAllAssociative();
    }

    /**
     * It returns all the boxes that are not deleted
     *
     * @return array<Box>
     */
    public function getAllBoxes(): array
    {
        return $this->createQueryBuilder('b')
            ->select('b.id', 'b.name', 'b.price', 'b.quantity', 'b.type', 'b.pinned')
            ->where('b.deleted = FALSE')
            ->getQuery()
            ->getResult();
    }

    /**
     * It returns the details of a box
     *
     * @param int $boxId
     * @return array
     */
    public function getBoxDetails(int $boxId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT b.id, b.name, b.price, b.type, b.quantity as boxes_left, b.brawler_quantity, NOT bd.box_id IS NULL as is_daily, (COUNT(i.id) > 0) AS claimed 
                FROM box b
                LEFT JOIN box_daily bd on b.id = bd.box_id
                LEFT JOIN inventory i on b.id = i.box_id and i.collect_date > NOW() - INTERVAL \'1 hour\' * bd.repeat_every_hours
                WHERE b.deleted = FALSE and b.id = :boxId
                GROUP BY b.id, bd.box_id';
        return $conn->executeQuery($sql, ['boxId' => $boxId])->fetchAssociative();
    }

    /**
     * It creates a new box
     *
     * @param CreateBoxRequest $createBoxRequest
     * @return void
     */
    public function createBox(CreateBoxRequest $createBoxRequest): void
    {
        $this->getEntityManager()->beginTransaction();

        try {
            $box = new Box();
            $box->setName($createBoxRequest->name);
            $box->setPrice($createBoxRequest->price);
            $box->setType(BoxType::tryFrom($createBoxRequest->type));
            $box->setQuantity($createBoxRequest->quantity);
            $box->setBrawlerQuantity($createBoxRequest->brawler_quantity);
            $this->getEntityManager()->persist($box);

            foreach ($createBoxRequest->brawlers_in_box as $brawler) {
                $boxBrawler = new BoxBrawler();
                $boxBrawler->setBrawler($this->getEntityManager()->getReference(Brawler::class, $brawler['id']));
                $boxBrawler->setProbability($brawler['probability']);
                $boxBrawler->setBox($box);
                $this->getEntityManager()->persist($boxBrawler);
            }

            $this->getEntityManager()->flush();
            $this->getEntityManager()->commit();

        }catch (\Exception $e) {
            $this->getEntityManager()->rollback();
            throw $e;
        }
    }

    /**
     * It creates a new daily box
     *
     * @param CreateDailyBoxRequest $createDailyBoxRequest
     * @return void
     */
    public function createDailyBox(CreateDailyBoxRequest $createDailyBoxRequest) : void
    {
        $this->getEntityManager()->beginTransaction();

        try {
            $box = new Box();
            $box->setName($createDailyBoxRequest->name);
            $box->setQuantity(-1);
            $box->setBrawlerQuantity($createDailyBoxRequest->brawler_quantity);
            $box->setPrice(0); // Daily boxes are free
            $box->setType(BoxType::tryFrom($createDailyBoxRequest->type));
            $this->getEntityManager()->persist($box);

            $boxDaily = new BoxDaily();
            $boxDaily->setBox($box);
            $boxDaily->setRepeatEveryHours($createDailyBoxRequest->repeat_every_hours);
            $this->getEntityManager()->persist($boxDaily);

            foreach ($createDailyBoxRequest->brawlers_in_box as $brawler) {
                $boxBrawler = new BoxBrawler();
                $boxBrawler->setBrawler($this->getEntityManager()->getReference(Brawler::class, $brawler['id']));
                $boxBrawler->setProbability($brawler['probability']);
                $boxBrawler->setBox($box);
                $this->getEntityManager()->persist($boxBrawler);
            }

            $this->getEntityManager()->flush();
            $this->getEntityManager()->commit();
        }catch (\Exception $e) {
            $this->getEntityManager()->rollback();
            throw $e;
        }
    }
}
