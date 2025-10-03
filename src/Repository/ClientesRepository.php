<?php

namespace App\Repository;

use App\Entity\Clientes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Clientes>
 */
class ClientesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Clientes::class);
    }

    //    /**
    //     * @return Clientes[] Returns an array of Clientes objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Clientes
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

        /**
     * Devuelve un array con conteo de clientes registrados por mes en el último año
     * [
     *   '2024-09' => 10,
     *   '2024-10' => 15,
     *   ...
     * ]
     */
    public function countByMonthLastYear(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 
                DATE_FORMAT(fech_creacion, '%Y-%m') AS month, 
                COUNT(id) AS count
            FROM clientes
            WHERE fech_creacion >= :date
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
    
    public function countClientesUltimas24Horas(): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT COUNT(id) AS count
            FROM clientes
            WHERE fech_creacion >= :date
        ";

        $stmt = $conn->prepare($sql);

        // Calcular fecha/hora 24 horas atrás
        $date = (new \DateTime())->modify('-24 hours')->format('Y-m-d H:i:s');

        $result = $stmt->executeQuery(['date' => $date]);

        return (int) $result->fetchOne(); // Devuelve el número directamente
    }
    

    
}
