<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    private PaginatorInterface $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Task::class);
        $this->paginator = $paginator;
    }

    public function findAllPaginated(string $status, int $page, int $limit)
    {
        $query = $this->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC');
            

        if (!empty($status)){
            $query
            ->andWhere('t.status = :status')
            ->setParameter('status', $status);
        }
        
        return $this->paginator->paginate(
            $query->getQuery(),
            $page,
            $limit
        );
    }

    //    /**
    //     * @return Task[] Returns an array of Task objects
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

    //    public function findOneBySomeField($value): ?Task
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
