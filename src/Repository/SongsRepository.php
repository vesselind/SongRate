<?php

namespace App\Repository;

use App\Entity\Songs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Songs|null find($id, $lockMode = null, $lockVersion = null)
 * @method Songs|null findOneBy(array $criteria, array $orderBy = null)
 * @method Songs[]    findAll()
 * @method Songs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SongsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Songs::class);
    }

    // /**
    //  * @return Songs[] Returns an array of Songs objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Songs
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
