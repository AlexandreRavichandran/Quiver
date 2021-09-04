<?php

namespace App\Repository;


use App\Entity\Space;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Space|null find($id, $lockMode = null, $lockVersion = null)
 * @method Space|null findOneBy(array $criteria, array $orderBy = null)
 * @method Space[]    findAll()
 * @method Space[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Space::class);
    }

    public function findSpaces($id, $limit)
    {
        return $this
            ->createQueryBuilder('s')
            ->andWhere('s.id > :id')
            ->setParameter(':id', $id)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function orderUserSpace($userId, $order)
    {
        $queryBuilder = $this
            ->createQueryBuilder('s')
            ->innerJoin('s.subscribers', 'u')
            ->andWhere('u.id = :id')
            ->setParameter(':id', $userId);
            if($order === 'name'){
            $queryBuilder->orderBy('s.name', 'ASC');
            }
            if($order === 'lastVisited'){
            $queryBuilder->orderBy('s.lastVisited', 'DESC');
            }
        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
    // /**
    //  * @return Space[] Returns an array of Space objects
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
    public function findOneBySomeField($value): ?Space
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
