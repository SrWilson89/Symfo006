<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Usuarios>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    //    /**
    //     * @return Usuarios[] Returns an array of Usuarios objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Usuarios
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
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
            FROM user
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
    public function countUsersConectados(?User $user = null): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.isOnline = :isOnline')
            ->setParameter('isOnline', true);

        if ($user !== null && $user->getCliente() !== null){
            $qb->andWhere('u.cliente = :cliente')
            ->setParameter('cliente', $user->getCliente());
        }

        return (int) $qb->getQuery()->getSingleScalarResult();

    }

     public function getPorcentajeUsersConectados(?User $user = null): float
    {
       $qbTotal = $this->createQueryBuilder('u')
        ->select('COUNT(u.id)');

        $qbOnline = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.isOnline = :isOnline')
            ->setParameter('isOnline', true);

        if ($user !== null && $user->getCliente() !== null) {
            $qbTotal->andWhere('u.cliente = :cliente')
                ->setParameter('cliente', $user->getCliente());
            $qbOnline->andWhere('u.cliente = :cliente')
                ->setParameter('cliente', $user->getCliente());
        }

        $total = (int) $qbTotal->getQuery()->getSingleScalarResult();
        $online = (int) $qbOnline->getQuery()->getSingleScalarResult();

         return round(($online / $total) * 100, 2);
        
    }

    
}
