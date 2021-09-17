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
    
    /**
     *
     * @param integer $id Id of the previous space (default to 1)
     * @param integer|null $limit Max of query results 
     * @return array
     */
    public function findSpaces(int $id = 1, int $limit = null):array
    {
        return $this
            ->createQueryBuilder('s')
            ->andWhere('s.id > :id')
            ->setParameter(':id', $id)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
