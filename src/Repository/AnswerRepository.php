<?php

namespace App\Repository;

use App\Entity\Answer;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Answer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Answer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Answer[]    findAll()
 * @method Answer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Answer::class);
    }

    /**
     * 
     *
     * @param integer $id Id of the question 
     * @param DateTimeImmutable|null $date Find answer earlier than... (default to "now")
     * @param integer|null $limit Max of query results
     * @return array
     */
    public function findAnswersByQuestionId(int $id,DateTimeImmutable $date = null, int $limit = null):array
    {
        if($date === null){
            $date = new DateTimeImmutable();
        }

        return $this
            ->createQueryBuilder('a')
            ->join('a.question', 'q')
            ->andWhere('q.id = :id')
            ->andWhere('a.createdAt < :date')
            ->setParameters([':id' => $id, ':date' => $date])
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
