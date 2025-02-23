<?php

namespace App\Repository;

use App\DTO\user\UserDetailsResponse;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByBrawltag(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Returns the user details for the given id.
     * @param User $user
     * @return false|mixed[]
     */
    public function getUserDetails(User $user)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql ="
        select 
            u.id,
            u.username,
            u.brawl_tag as brawltag,
            c.name,
            c.surname,
            to_char(c.birthdate, 'yyyy-mm-dd') as birthdate,
            c.dni,
            u.email,
            u.gems,
            (select coalesce(count(distinct i.id), 0) 
             from inventory i 
             where i.user_id = u.id) as openboxes,
            (select coalesce(count(distinct ufb.brawler_id), 0) 
             from user_favorite_brawlers ufb 
             where ufb.user_id = u.id) as favouritebrawlers,
            (select coalesce(sum(ub.quantity - 1) filter (where ub.quantity > 1), 0) 
             from user_brawler ub 
             where ub.user_id = u.id) as trophies,
            (select coalesce(count(distinct ub.brawler_id), 0) 
             from user_brawler ub 
             where ub.user_id = u.id) as brawlers,
            (select count(*) 
             from \"order\" o 
             join inventory i on o.id = i.order_id 
             where o.user_id = u.id 
               and o.user_id <> i.user_id) as gifts
        from \"user\" u
            left join client c on u.client_id = c.id
        where u.id = :userId";

        $result = $conn->executeQuery($sql, ['userId' => $user->getId()]);

        return $result->fetchAssociative();
    }
}
