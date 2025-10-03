<?php

namespace App\Repository;

use App\Entity\Hilos;
use App\Entity\Tareas;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hilos>
 */
class HilosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hilos::class);
    }

    //    /**
    //     * @return Hilos[] Returns an array of Hilos objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('h.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Hilos
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

   public function findByTareas(array $tareas): array
    {
        if (empty($tareas)) {
            return [];
        }

        $qb = $this->createQueryBuilder('h')
            ->innerJoin('h.tarea', 't')
            ->andWhere('t IN (:tareas)')
            ->setParameter('tareas', $tareas)
            ->orderBy('t.fechafin', 'ASC');

        return $qb->getQuery()->getResult();
    }
}

