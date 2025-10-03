<?php

namespace App\Repository;

use App\Entity\Presupuestos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Presupuestos>
 */
class PresupuestosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Presupuestos::class);
    }

    //    /**
    //     * @return Presupuestos[] Returns an array of Presupuestos objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Presupuestos
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
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
                DATE_FORMAT(fechacreacion, '%Y-%m') AS month, 
                COUNT(id) AS count
            FROM presupuestos
            WHERE fechacreacion >= :date AND tipo = 1
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

    public function countFacturasUltimas24Horas(): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT COUNT(id) AS count
            FROM presupuestos
            WHERE fechacreacion >= :date AND tipo = 1
        ";

        $stmt = $conn->prepare($sql);

        // Calcular fecha/hora 24 horas atrás
        $date = (new \DateTime())->modify('-24 hours')->format('Y-m-d H:i:s');

        $result = $stmt->executeQuery(['date' => $date]);

        return (int) $result->fetchOne(); // Devuelve el número directamente
    }
}
