<?php

namespace App\Repository;

use App\Entity\Tips;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tips>
 */
class TipsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tips::class);
    }

    /**
     * Get all tips by specific month.
     *
     * @return Tips[]
     */
    public function findByMonth(int $month): array
    {
        return $this->createQueryBuilder('t')
             ->where('JSON_CONTAINS(t.month_list, :month, \'$\') = 1')
             ->setParameter('month', json_encode($month))
             ->getQuery()
             ->getResult();
    }

    //    /**
    //     * @return Tips[] Returns an array of Tips objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Tips
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
