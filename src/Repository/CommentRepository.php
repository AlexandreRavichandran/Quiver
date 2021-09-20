<?php

namespace App\Repository;

use App\Entity\Comment;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * 
     *
     * @param integer $answerId Id of the answer
     * @param DateTimeImmutable|null $date Find comments earlier than... (default to "now")
     * @param integer|null $limit Max of query results
     * @return array
     */
    public function findCommentByAnswer(int $answerId, DateTimeImmutable $date = null, int $limit = null): array
    {
        if ($date === null) {
            $date = new DateTimeImmutable();
        }
        return $this->createQueryBuilder('c')
            ->join('c.answer', 'a')
            ->andWhere('a.id = :id')
            ->andWhere('c.createdAt < :date')
            ->setParameters(['id' => $answerId, 'date' => $date])
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

}
