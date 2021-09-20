<?php

namespace App\Repository;

use App\Entity\Question;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    /**
     * 
     *
     * @param DateTimeImmutable $date Find questions earlier than... (default to "now")
     * @param integer $limit Max of query results
     * @return array
     */
    public function findAllQuestionsWithAnswers($date = null, $limit = null): array
    {
        if ($date === null) {
            $date = new DateTimeImmutable();
        }

        return $this->createQueryBuilder('q')
            ->join('q.answers', 'a')
            ->groupBy('a.question')
            ->andHaving('COUNT(a.answer) > 0')
            ->andWhere('q.createdAt < :date')
            ->setParameter('date', $date)
            ->orderBy('q.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * 
     *
     * @param array $spaceIds list of user's subscribed spaces's ids
     * @param DateTimeImmutable|null $date Find questions earlier than... (default to "now")
     * @param integer $limit Max of query results
     * @return array
     */
    public function findAllQuestionsBySpaceNames(array $spaceIds, DateTimeImmutable $date = null, int $limit = null): array
    {
        if ($date === null) {
            $date = new DateTimeImmutable();
        }
        return $this->createQueryBuilder('q')
            ->join('q.space', 's')
            ->andWhere('s.id IN (' . implode(',', $spaceIds) . ')')
            ->andWhere('q.createdAt < :date')
            ->setParameter('date', $date)
            ->orderBy('q.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Search a piece of word on an answer
     * @param string $query The piece of word to search
     * @return array
     */
    public function findQuestionsByQuery(string $query): array
    {
        return $this
            ->createQueryBuilder('q')
            ->andWhere('q.question LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('q.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
