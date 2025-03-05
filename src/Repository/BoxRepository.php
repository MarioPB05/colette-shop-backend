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
use Doctrine\DBAL\ArrayParameterType;
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
                    (COUNT(o.id) > 0) AS claimed,
                    CASE WHEN COUNT(o.id) = 0 THEN NULL ELSE MAX(i.collect_date) END AS last_claimed
                FROM box b
                JOIN box_daily bd ON b.id = bd.box_id
                LEFT JOIN box_brawler bb ON b.id = bb.box_id
                LEFT JOIN user_favorite_brawlers ufb ON bb.brawler_id = ufb.brawler_id AND ufb.user_id = :userId
                LEFT JOIN inventory i ON b.id = i.box_id AND i.user_id = :userId 
                    AND i.collect_date > NOW() - INTERVAL '1 hour' * bd.repeat_every_hours
                LEFT JOIN public.order o ON i.order_id = o.id AND o.cancelled = FALSE
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
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT b.id, b.name, b.price, b.quantity, b.type, b.pinned, NOT bd.box_id IS NULL as is_daily
                FROM box b
                LEFT JOIN box_daily bd on b.id = bd.box_id
                WHERE b.deleted = FALSE
                GROUP BY b.id, bd.box_id';

        $result = $conn->executeQuery($sql);
        return $result->fetchAllAssociative();
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
        $sql = 'SELECT b.id, b.name, b.price, b.type, b.quantity as boxes_left, b.brawler_quantity, NOT bd.box_id IS NULL as is_daily, (COUNT(o.id) > 0) AS claimed 
                FROM box b
                LEFT JOIN box_daily bd on b.id = bd.box_id
                LEFT JOIN inventory i on b.id = i.box_id and i.collect_date > NOW() - INTERVAL \'1 hour\' * bd.repeat_every_hours
                LEFT JOIN public.order o on i.order_id = o.id and o.cancelled = FALSE
                WHERE b.deleted = FALSE and b.id = :boxId
                GROUP BY b.id, bd.box_id';
        return $conn->executeQuery($sql, ['boxId' => $boxId])->fetchAssociative();
    }

    /**
     * Handles the base logic for creating a box.
     *
     * @param CreateBoxRequest|CreateDailyBoxRequest $request
     * @param bool $isDailyBox
     * @return void
     * @throws \Exception
     */
    public function createBoxBase(object $request, bool $isDailyBox): void
    {
        $this->getEntityManager()->beginTransaction();

        try {
            $box = new Box();
            $box->setName($request->name);
            $box->setType(BoxType::tryFrom($request->type));
            $box->setBrawlerQuantity($request->brawler_quantity);

            if ($isDailyBox) {
                $box->setQuantity(-1);
                $box->setPrice(0); // Daily boxes are free
            } else {
                $box->setQuantity($request->quantity);
                $box->setPrice($request->price);
            }

            $this->getEntityManager()->persist($box);

            if ($isDailyBox) {
                $boxDaily = new BoxDaily();
                $boxDaily->setBox($box);
                $boxDaily->setRepeatEveryHours($request->repeat_every_hours);
                $this->getEntityManager()->persist($boxDaily);
            }

            $this->addBrawlersToBox($box, $request->brawlers_in_box);

            $this->getEntityManager()->flush();
            $this->getEntityManager()->commit();

        } catch (\Exception $e) {
            $this->getEntityManager()->rollback();
            throw $e;
        }
    }

    /**
     * Adds brawlers to a box with their probabilities.
     *
     * @param Box $box
     * @param array $brawlers
     * @return void
     */
    private function addBrawlersToBox(Box $box, array $brawlers): void
    {
        foreach ($brawlers as $brawlerData) {
            $brawler = $this->getEntityManager()->getReference(Brawler::class, $brawlerData['id']);
            $boxBrawler = new BoxBrawler();
            $boxBrawler->setBrawler($brawler);
            $boxBrawler->setProbability($brawlerData['probability']);
            $boxBrawler->setBox($box);
            $this->getEntityManager()->persist($boxBrawler);
        }
    }

    /**
     * Checks if a box is a daily box
     *
     * @param int $id
     * @return bool
     */
    public function isDailyBox(int $id): bool
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT bd.box_id
                FROM box_daily bd
                WHERE bd.box_id = :id';
        return $conn->executeQuery($sql, ['id' => $id])->fetchOne() !== false;
    }

    /**
     * Returns the details of a box
     *
     * @param int $id
     * @return array
¡     */
    public function getCreateBoxRequest(int $id): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT b.id, b.name, b.price, b.type, b.quantity, b.brawler_quantity
                FROM box b
                WHERE b.deleted = FALSE and b.id = :id
                GROUP BY b.id';

        return $conn->executeQuery($sql, ['id' => $id])->fetchAssociative();
    }

    /**
     * It returns the details of a daily box
     *
     * @param int $id
     * @return array
     */
    public function getCreateDailyBoxRequest(int $id): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT b.id, b.name, b.type, bd.repeat_every_hours, b.brawler_quantity
                FROM box b
                JOIN box_daily bd on b.id = bd.box_id
                WHERE b.deleted = FALSE and b.id = :id
                GROUP BY b.id, bd.repeat_every_hours';

        return $conn->executeQuery($sql, ['id' => $id])->fetchAssociative();
    }


    /**
     * It returns the brawlers that are in a box
     *
     * @param int $boxId
     * @return array
     */
    public function getBrawlersInBox(int $boxId) : array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT bb.brawler_id as id, bb.probability
                FROM box_brawler bb
                WHERE bb.box_id = :boxId';
        return $conn->executeQuery($sql, ['boxId' => $boxId])->fetchAllAssociative();
    }

    /**
     * Edits a normal box or a daily box
     *
     * @param object $request
     * @param bool $isDailyBox
     * @return void
     */
    public function editBoxBase(int $boxId, object $request, bool $isDailyBox): void
    {
        $this->getEntityManager()->beginTransaction();

        try {
            $box = $this->find($boxId);
            $box->setName($request->name);
            $box->setType(BoxType::tryFrom($request->type));
            $box->setBrawlerQuantity($request->brawler_quantity);

            if ($isDailyBox) {
                $box->setQuantity(-1);
                $box->setPrice(0); // Daily boxes are free
            } else {
                $box->setQuantity($request->quantity);
                $box->setPrice($request->price);
            }

            $this->editBrawlersInBox($box, $request->brawlers_in_box);

            if ($isDailyBox) {
                $boxDaily = $this->getDailyBox($box);
                $boxDaily->setRepeatEveryHours($request->repeat_every_hours);
                $this->getEntityManager()->persist($boxDaily);
            }

            $this->getEntityManager()->flush();
            $this->getEntityManager()->commit();
        } catch (\Exception $e) {
            $this->getEntityManager()->rollback();
            throw $e;
        }
    }

    private function getDailyBox(Box $box): BoxDaily|null
    {
        return $this->getEntityManager()->getRepository(BoxDaily::class)->findOneBy(['box' => $box]);
    }

    /**
     * Edits the brawlers in a box
     *
     * @param Box $box
     * @param array $brawlers
     * @return void
     */
    public function editBrawlersInBox(Box $box, array $brawlers): void
    {
        $this->getEntityManager()->beginTransaction();

        try {
            $this->removeBrawlersFromBox($box);
            $this->addBrawlersToBox($box, $brawlers);

            $this->getEntityManager()->flush();
            $this->getEntityManager()->commit();

        } catch (\Exception $e) {
            $this->getEntityManager()->rollback();
            throw $e;
        }
    }

    /**
     * Removes all the brawlers from a box
     *
     * @param Box $box
     * @return void
     */
    private function removeBrawlersFromBox(Box $box): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'DELETE FROM box_brawler bb
                WHERE bb.box_id = :boxId';
        $conn->executeQuery($sql, ['boxId' => $box->getId()]);
    }

    public function getAllBoxDetails(array $boxesIds): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT b.id, b.name, b.price, b.type, b.quantity as boxes_left, b.brawler_quantity, NOT bd.box_id IS NULL as is_daily, (COUNT(i.id) > 0) AS claimed, b.deleted
                FROM box b
                LEFT JOIN box_daily bd on b.id = bd.box_id
                LEFT JOIN inventory i on b.id = i.box_id and i.collect_date > NOW() - INTERVAL \'1 hour\' * bd.repeat_every_hours
                WHERE b.deleted = FALSE and b.id IN (:boxesIds)
                GROUP BY b.id, bd.box_id';
        return $conn->executeQuery($sql, ['boxesIds' => $boxesIds], ['boxesIds' => ArrayParameterType::INTEGER])->fetchAllAssociative();
    }

}
