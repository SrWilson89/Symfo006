<?php

namespace App\Repository;

use App\Entity\Tareas;
use App\Entity\User;
use App\Entity\Clientes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tareas>
 */
class TareasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tareas::class);
    }

    //    /**
    //     * @return Tareas[] Returns an array of Tareas objects
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

    //    public function findOneBySomeField($value): ?Tareas
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function countByMonthLastYear(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 
                DATE_FORMAT(fecha_creacion, '%Y-%m') AS month, 
                COUNT(id) AS count
            FROM tareas
            WHERE fecha_creacion >= :date
            GROUP BY month
            ORDER BY month ASC
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['date' => (new \DateTime('-1 year'))->format('Y-m-01')]);

        $results = $result->fetchAllAssociative();

        $data = [];
        foreach ($results as $row) {
            $data[$row['month']] = (int) $row['count'];
        }

        return $data;

        
    }

    public function findTareasNoFinalizadas(?User $user = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.fechafin IS NULL');

        if ($user !== null && $user->getCliente() !== null) {
            $qb->andWhere('t.cliente = :cliente')
            ->setParameter('cliente', $user->getCliente()->getId());
        }
        return $qb->getQuery()->getResult();
    }

    


}
