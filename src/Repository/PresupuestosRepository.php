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

        return (int) $result->fetchOne();
    }

    /**
     * Calcula el total de ventas (presupuestos tipo 1) en las últimas 24 horas.
     * CORRECCIÓN: Usa JOIN con la tabla 'detalles' y calcula el monto total.
     */
    public function getTotalVentasUltimas24Horas(): float
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT SUM(d.precio * d.cantidad * (1 + d.iva / 100))
            FROM presupuestos p
            LEFT JOIN detalles d ON p.detalle_id = d.id
            WHERE p.fechacreacion >= :date AND p.tipo = 1
        ";

        $stmt = $conn->prepare($sql);

        // Calcular fecha/hora 24 horas atrás
        $date = (new \DateTime())->modify('-24 hours')->format('Y-m-d H:i:s');

        $result = $stmt->executeQuery(['date' => $date]);

        // Retorna 0.0 si el resultado es nulo
        return (float) $result->fetchOne() ?? 0.0;
    }

    /**
     * Calcula la suma de ventas (presupuestos tipo 1) agrupadas por mes del último año.
     * CORRECCIÓN: Usa JOIN con la tabla 'detalles' y calcula el monto total.
     */
    public function sumByMonthLastYear(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 
                DATE_FORMAT(p.fechacreacion, '%Y-%m') AS month, 
                SUM(d.precio * d.cantidad * (1 + d.iva / 100)) AS total_sales
            FROM presupuestos p
            LEFT JOIN detalles d ON p.detalle_id = d.id
            WHERE p.fechacreacion >= :date AND p.tipo = 1
            GROUP BY month
            ORDER BY month ASC
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['date' => (new \DateTime('-1 year'))->format('Y-m-01')]);

        $results = $result->fetchAllAssociative();

        $data = [];
        foreach ($results as $row) {
            $data[$row['month']] = (float) $row['total_sales'];
        }

        return $data;
    }
}