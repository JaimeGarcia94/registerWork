<?php

namespace App\Repository;

use App\Entity\WorkEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkEntry>
 *
 * @method WorkEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkEntry[]    findAll()
 * @method WorkEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkEntry::class);
    }

    public function save(WorkEntry $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WorkEntry $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

   /**
    * @return WorkEntry[] Returns an array of WorkEntry objects
    */
   public function findByUserId($userId): array
   {
       return $this->createQueryBuilder('w')
            ->andWhere('w.userId = :userId')
            ->orderBy('w.id','DESC')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult()
       ;
   }

   public function findOneByRow($userId): ?WorkEntry
   {
       return $this->createQueryBuilder('w')
           ->andWhere('w.userId = :userId')
           ->orderBy('w.id','DESC')
           ->setMaxResults(1)
           ->setParameter('userId', $userId)
           ->getQuery()
           ->getOneOrNullResult()
       ;
   }
   
}
