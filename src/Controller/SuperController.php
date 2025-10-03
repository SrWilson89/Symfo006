<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Utils\ExportService;

class SuperController extends AbstractController
{
    // Variables accesibles en vistas (como título, breadcrumbs, etc.)
    protected array $globals;

    // EntityManager para acceso a la base de datos
    protected EntityManagerInterface $em;

    // Servicio de exportación a PDF/Excel
    protected ExportService $exportService;

    public function __construct(
        EntityManagerInterface $em,
        ExportService $exportService
    ) {
        $this->globals = [
            'nombre' => '',
            'breadcrumbs' => []
        ];

        $this->em = $em;
        $this->exportService = $exportService;
    }
}